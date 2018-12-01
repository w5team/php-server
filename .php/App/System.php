<?php 

namespace App;
use Lib\Database as DB;
use Lib\Aes as AES;
use App\User as User;
use App\Msg as Msg;

class System
{

	private static $id = 0; 		// id do usuário atual (logado)
	private static $key = '';		// token do usuário logado no momento
	private static $random = false;	// ativa a troca aleatória do token
	private static $data = false;	// dados enviados pelo client

	/**
	 * Entrega o ID do usuário atual
	 * @return integer ID do usuário atual
	 */
	static public function getUserId()
	{
		return static::$id;
	}

	/**
	 * Pega o token by user id
	 * @param  integer $id user id
	 * @return string user token (key)
	 */
	static public function getUserKeyById($id)
	{
		$res = (new DB)->query('SELECT '.User::$table['token'].' 
									FROM '.User::$table['table'].' 
									WHERE id=:id', 

									[':id'=>(0 + $id)]);

		if($res){
			return $res[0]->get(User::$table['token']);
		}
		return false;
	}

	/**
	 * Entrega a chave atual
	 * @return string chave atual
	 */
	static public function getUserKey()
	{
		return static::$key;
	}

	/**
	 * Pega os dados já decodificados (decriptados) de entrada
	 * 
	 * -- ATT: Esses dados só estarão disponíveis se uma chamda a System::input()
	 * 
	 * @return mixed  dados solicitados pelo client
	 */
	static public function getData()
	{
		return static::$data;
	}

	/**
	 * Realiza as operações de sincronização (temporizador no cliente)
	 * @return mixed Vários dados de sincronização (config, atualização, mensagens, etc)
	 */
	function ping()
	{
		// decodificando os dados recebidos
		$data = self::input();

		// Pegando as mensagens (se existirem)
		$data['msg'] = Msg::getMessage();
	
		// Enviando os dados
		self::send($data);
	}

	/**
	 * Decriptografa dados de entrada via POST enviado pelo cliente
	 * @return void|json decodifica ou retorna erro (via json) ao client
	 */
	static public function input()
	{
		if(isset($_POST['data']) 
			&& isset($_POST['id'])){ 

			// Gravando o ID
			static::$id = (0 + $_POST['id']);

			// pegando a chave no DB
			$res = (new DB)->query('SELECT '.User::$table['token'].' 
								FROM '.User::$table['table'].' 
								WHERE id=:id', 

								[':id' => static::$id]);

			// On error: aborta e responde ao cliente com erro
			if(!$res) return self::send([]);

			// Gravando a KEY
			static::$key = $res[0]->get(User::$table['token']); 
			
			// Decodificando
			try {

				static::$data = json_decode(AES::dec($_POST['data'], static::$key));
				return static::$data;

			} catch(Exception $e) {

				return self::send([]);
			}
		}
	}

	/**
	 * Envia os dados encriptografados ao cliente
	 * @param  array|object $data  	Dados a serem enviados
	 * @param  string 		$extra  String (base64) de conteúdo não criptografado a ser enviado
	 * @return HTTP         Criptografa e envia os dados ao client (formato Json)
	 */
	static public function send($data = null, $extra = null)
    {
    	// Se não indicar $data é retornado "erro" simnples (sem "msg")
    	if($data == null) $data = ['error'=>true];

    	// Ajeitando as coisas (data tem que ser um array)
    	if(!is_array($data)) $data = [$data];

        // Tira "par ou impar"
		if(static::$random == true 
			&& rand(0,1) == 1) {

			// Se der "par" troca a key
			$nkey = md5(uniqid(rand(), true));

			// Atualizando no BD
			(new DB)->query('UPDATE '.User::$table['table'].' 
								SET '.User::$table['token'].'=:tk 
								WHERE id=:id',

							[
								':id' => static::$id, 
								':tk' => $nkey
							]
						);
			
			// embalando para o client
			$data['key'] = $nkey; 

		} else {

			$data['key'] = static::$key;
		}

		$json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_NUMERIC_CHECK);

        // Criptografando com AES
        try{

        	$data = AES::enc($json, static::$key);

        } catch(Exception $e) {

        	$data = [];
        }
     	
     	// Enviando e saindo 
     	self::sendJson($data, $extra);
    }

    /**
     * Codifica em JSON e envia
     * @param  mixed $data Dados a serem enviados
     * @return HTTP output to client
     */
    static public function sendJson($data, $extra = null)
    {
    	ob_start("ob_gzhandler");
        header('Access-Control-Allow-Origin:*');
        header('Content-Type: application/json; charset=utf-8');

        // Adicionando dados seguros e extra.
        $send = [
        			'data' => $data, 
        			'extra' => $extra
        		];

        // Entrega ao client.
        exit(json_encode($send));
    }
}