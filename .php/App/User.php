<?php

namespace App;
use Lib\Database as DB; 	// Acesso ao Banco de dados
use Lib\Aes as AES;     	// Criptografia
use App\System as System;	// Classe do "sistema" (in/out padronizado)
use App\Audit as Audit; 	// Grava dados para auditoria

class User 
{
	/**
	 * Caminho para os arquivos de chaves
	 * @var string
	 */
	private $keyPath = _PHPPATH.'/Config/Keys';

	/**
	 * Parâmetros do Banco de dados
	 * Tabela de usuários
	 */
	public static $table = [
		'table'		=> 'usuario', 	// nome da tabela
		'login'		=> 'login', 	
		'password'	=> 'senha', 	
		'name' 		=> 'nome',		// string user name
		'token'		=> 'token', 	// token (secret)		
		'life'		=> 'life', 		// datetime || null
		'level'		=> 'nivel',  	// string fixed 'admin/normal' ... /others
		'deleted'	=> 'deleted' 	// datetime || null - para softdelet
	];


	/**
	 * Envia PUBLIC KEY
	 * @return string|html public key
	 */
	public function getkey()
	{
		// Enviando a PUBLIC KEY ao client...
		$this->sendJson(
			[
				'key' => str_replace(array("\r", "\n"), '', file_get_contents($this->keyPath.'/public.key'))
			]
		);
	}

	/**
	 * Conecta o usuário no sistema
	 * @return JSON retorna uma string JSON com os dados do usuário encriptados pela chave enviada pelo usuário.
	 */
	public function connect()
	{
		// Nenhum dado enviado
		$out =  [
					'error'=>true, 
					'msg'=>''
				];

		// Os dados são recebidos no campo "data", encriptado por RSS.
		if(isset($_POST['data']) 
			&& $_POST['data'] != ''){

		    $private = file_get_contents($this->keyPath.'/private.key');    

		    $key = base64_decode($_POST['data']);

		    // Checando a decriptação com a chave PRIVATE
		    if(!openssl_private_decrypt($key, $key, openssl_pkey_get_private($private))) {
		        $out = [
		        		'error'=>true, 
		        		'msg'=>'Falha no envio da chave!<br>'.$key
		        	];

		    } else {

		    	$user = json_decode($key); // decodifica os dados em JSON

		    	// Checa o login/senha
		    	$db = new DB;
		    	$result = $db->query('SELECT * 
		    							FROM '.static::$table['table'].' 
		    							WHERE '.static::$table['login'].'=:login 
		    							AND '.static::$table['password'].'=:senha
		    							AND !ISNULL('.static::$table['deleted'].')',
		    				
		    							[
		    								':login' => $user->login, 
		    								':senha'  => md5($user->passw)
		    							]
		    						);

		    	if($result){

		    		// Tempo de vida fixado em 24 HORAS
		    		$life = date('Y-m-d H:i:s', time()+(24*60*60));

		    		// Atualizando a tabela de usuários
		    		$db->query('UPDATE '.static::$table['table'].' 
		    						SET '.static::$table['token'].'=:token, 
		    							'.static::$table['life'].'=:life 
		    						WHERE id=:id', 

		    						[
		    							':token' => $user->token, 
		    							':life'  => $life, 
		    							':id'    => $result[0]->get('id')
		    						]
		    					);

		    		// Gravando auditoria
		    		Audit::log(true, [
		    								'id' => $result[0]->get('id'),
		    								'name' => $result[0]->get(static::$table['name'])
		    							]
		    						);

		    		// Montando os dados a serem criptografados e enviados
		    		$data = json_encode([
		    				'name'	=> $result[0]->get(static::$table['name']),
		    		 		'id'	=> $result[0]->get('id'),
		    		 		'token'	=> $user->token,
		    		 		'life'  => $life
		    		 	]);

		    		// Criptografando AES 256
		    		$data = AES::enc($data, $user->token);

		    		// Formatando os dados de saída
		    		$out = [
		    				'error' => false, 
		    				'data' => $data
		    			];

		    	} else {

		    		// Caso não consiga fazer logIn ...
		    		$out = [
		    				'error'=>true, 
		    				'msg'=>'Login ou senha inválidos!'
		    			];
		    	}
		    }
		} // Caso o campo "data" não exista, retorna vazio (erro: acesso indevido) 

	    $this->sendJson($out);
	}

	/**
	 * Faz logout no sistema
	 * @return json|html retorna em json, criptografado.
	 */
	public function logout()
	{
		$data = System::getData();

		if(isset($data->log) 
			&& $data->log == 'logout' 
			&& isset($data->id)){

			// Verificando se o ID corresponde...
			if($data->id != System::getUserId()){
				System::send(); // Retorna com erro.
			}

			// Atualizando a tabela de usuários
		    (new DB)->query('UPDATE '.static::$table['table'].' 
		    					SET '.static::$table['token'].'=:token, 
		    						'.static::$table['life'].'=:life 
		    					WHERE id=:id', 

		    					[
		    						':token' => '', 
		    						':life'=>NULL, 
		    						':id' => System::getUserId()
		    					]
		    				);

		    // Gravando auditoria
		    Audit::log(System::getUserId(), false, $data);

		    // Enviando notificação ao client.
		    System::send($data);
		    
		}

		// Retorna com erro, caso não tenha "logout" sinalizado
		System::send();
	}

	/**
	 * Envia os dados no formato JSON.
	 * @param  mixed $data os dados a serem enviados.
	 * @return json|html string no formato JSON para o client.       
	 */
	private function sendJson($data)
    {
    	ob_start("ob_gzhandler");
        header('Access-Control-Allow-Origin:*');
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($data));
    }
}