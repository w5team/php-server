<?php 

namespace App;
use Lib\Database as DB;
use Lib\NTag as Html;
use App\System as System;
use App\User as User;

class Msg
{
	/**
	 * Parâmetros do Banco de dados
	 * Tabela de mensagens
	 */
	private static $table = [
		'table'		=> 'msg', 		// nome da tabela
		'id'		=> 'id', 		// integer
		'source'	=> 'origem', 	// user id
		'target'	=> 'destino', 	// user id
		'created'	=> 'criado', 	// datetime || null
		'sent' 		=> 'enviado', 	// datetime || null
		'seen'		=> 'visto', 	// datetime || null
		'read'		=> 'lido',  	// datetime || null
		'title'		=> 'titulo', 	// string
		'link'		=> 'link', 		// string
		'msg'		=> 'mensagem' 	// string
	];


	/**
	 * Retorna as mensagens do usuário logado
	 * @return array mensagens 
	 */
	static function getMessage()
	{
		// init
		$msg = [];
		$db = new DB;

		// Pegando mensagens (se existirem)
		$result = $db->query('SELECT * 
								FROM '.static::$table['table'].' 
								WHERE '.static::$table['target'].' = :id 
								AND ISNULL('.static::$table['sent'].')', 

								[
									':id' => System::getUserId()
								]
							);
		if($result){
			
			// Marcando como enviado ...
			$db->query('UPDATE '.static::$table['table'].' 
						SET '.static::$table['sent'].' = :sent 
						WHERE '.static::$table['target'].' = :id 
						AND ISNULL('.static::$table['sent'].')', 
				[':id' => System::getUserId(),
				 ':sent' => date('Y-m-d H:i:s')]);

			foreach($result as $res){
				$msg[] = [
					'id' => $res->get(static::$table['id']),
					'created' => $res->get(static::$table['created']),
					'title' => $res->get(static::$table['title']),
					'link' => $res->get(static::$table['link']),
					'msg' => $res->get(static::$table['msg'])];
			}
		}

		// devolvendo as mensagens
		return $msg;
	}

	/**
	 * Formulário web de envio de mensagens (para teste)
	 * @return http
	 */
	function formHtml(){
		$result = (new DB)->query('SELECT id, 
										  '.USer::$table['name'].' 
									FROM '.User::$table['table']);
		$user = [];

		if($result){
			$user['all'] = 'Todos os Usuários';
			foreach($result as $res){
				$user[$res->get('id')] = $res->get(User::$table['name']);
			}			
		}

		// View		
		(new HTML)->render('message', ['usuario' => $user])->send();
	}

	/**
	 * [registrar description]
	 * @return [type] [description]
	 */
	function create()
	{
		if(isset($_POST['titulo']) 
			&& isset($_POST['mensagem']) 
			&& isset($_POST['destino'])
			&& isset($_POST['link'])) {

			$db = new DB;

			// SE for para TODOS as usuários
			if($_POST['destino'] == 'all'){

				$result = $db->query('SELECT id 
										FROM '.User::$table['table'].' 
										WHERE ISNULL('.User::$table['deleted'].')'
									);
				
				if($result){
					foreach($result as $res){
						$destino[] = $res->get('id');
					}
				
				} else {					
					System::sendJson(['status'=>'error']);
				}

			} else {				
				$destino = [$_POST['destino']];
			}

			foreach ($destino as $dest) {

				$db->query('INSERT INTO '.static::$table['table'].' 
								('.static::$table['source'].', 
								 '.static::$table['target'].',
								 '.static::$table['created'].', 
								 '.static::$table['title'].', 
								 '.static::$table['link'].', 
								 '.static::$table['msg'].') 
								VALUES (:origem, :destino, :envio, :titulo, :link, :mensagem)',
					[
						':origem' => 1,
					 	':destino' => $dest,
					 	':envio' => date('Y-m-d H:i:s'),
					 	':titulo' => $_POST['titulo'],
					 	':link' => $_POST['link'] ?: null,
					 	':mensagem' => $_POST['mensagem']
					 ]
				);
			}

			return System::sendJson(['status'=>'ok']);
		}
		System::sendJson(['status'=>'error']);
	}

	/**
	 * [recibo description]
	 * @return [type] [description]
	 */
	function lido()
	{
		$data = System::input();

		if(isset($data->id) 
			&& ($data->id + 0) > 0){

			(new DB)->query('UPDATE '.static::$table['table'].' 
								SET '.static::$table['seen'].' = :lido 
								WHERE id = :id', 

						[
							':id' => ($data->id + 0), 
						 	':lido' => date('Y-m-d H:i:s')
						]
					);
		}

		// Enviando os dados
		System::send($data);
	}
}