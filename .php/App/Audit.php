<?php

namespace App;
use Lib\Database as DB;
use APP\System as System;

class Audit
{
	/**
	 * Tabela de auditoria
	 * @var string
	 */
	private static $table = [
		'table'			=> 'auditoria', // nome da tabela
		'action'		=> 'acao', 		// string tipo de ação (create/delete ...)
		'target'		=> 'tabela', 	// nome da tabela de destino (da ação)
		'action_date'	=> 'data', 		// datetime data do registro
		'user' 			=> 'usuario', 	// id do usuário
		'data'			=> 'dados', 	// parametros específicos da ação
	];

	/**
	 * Nome da tabela de usuários do sistema
	 * @var string
	 */
	private static $tabelaUsuario = 'usuario';

	/**
	 * Registra "insert" em auditoria
	 * @param  mixed $dados Dados a serem gravados (será convertido em string JSON)
	 * @return void 
	 */
	static public function insert($tabela, $dados)
	{
		return self::_save('insert', $tabela, $dados);
	}

	/**
	 * Registra "update" em auditoria
	 * @param  mixed $dados Dados a serem gravados (será convertido em string JSON)
	 * @return void 
	 */
	static public function update($tabela, $dados)
	{
		return self::_save('update', $tabela, $dados);
	}

	/**
	 * Registra "delete" em auditoria
	 * @param  mixed $dados Dados a serem gravados (será convertido em string JSON)
	 * @return void 
	 */
	static public function delete($tabela, $dados)
	{
		return self::_save('delete', $tabela, $dados);
	}

	/**
	 * Registra "login/out" em auditoria
	 * @param  $login indica se é um login (true) ou logout (false)
	 * @param  mixed $dados Dados a serem gravados (será convertido em string JSON)
	 * @return void 
	 */
	static public function log($login = true, $dados = [])
	{
		return self::_save(($login === false ? 'logout' : 'login'), self::$tabelaUsuario, $dados);
	}

	/**
	 * Registra "delete" em auditoria
	 * @param  mixed $dados Dados a serem gravados (será convertido em string JSON)
	 * @return void 
	 */
	static public function softDelete($tabela, $dados)
	{
		return self::_save('softDelete', $tabela, $dados);
	}

	/**
	 * Registra "delete" em auditoria
	 * @param  mixed $dados Dados a serem gravados (será convertido em string JSON)
	 * @return void 
	 */
	static public function softUndelete($tabela, $dados)
	{
		return self::_save('softUndelete', $tabela, $dados);
	}

	/**
	 * Salva os dados na tabela de auditoria
	 * @param  string $acao   		Nome da ação programada
	 * @param  string $tabela 		Tabela alvo da ação
	 * @param  array|object $dados  Array ou Objeto com os dados da ação a serem gravados (convertido em JSON)
	 * @return void 
	 */
	static private function _save($acao, $tabela, $dados)
	{
		return (new DB)->query('INSERT INTO '.static::$table['table'].' 
					('.static::$table['target'].', 
					 '.static::$table['action'].', 
					 '.static::$table['data'].', 
					 '.static::$table['action_date'].', 
					 '.static::$table['user'].') 
					 VALUES (:tabela, :acao, :dados, :data, :usuario)', 
				[
					':acao' 	=> $acao,
				 	':tabela' 	=> $tabela,
				 	':dados' 	=> json_encode($dados),
				 	':data' 	=> date('Y-m-d H:i:s'),
				 	':usuario' 	=> System::getUserId()
				]
			);
	}
}