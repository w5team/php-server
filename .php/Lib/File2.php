<?php

/**
 *
 *	Página de Administração do sistema
 * 
 */

namespace Lib;

use App\System as System;
use Lib\Database as DB;
use Lib\NTag as HTML;
use Lib\Aes as AES;
use Config\Mimetype as Mimetype;
use ZipArchive as Zip;

class File2
{

	function save()
	{
		$in = System::getData(); 
		if(!isset($_POST['param'])) return System::send(); // Se não tiver o arquivo, sai.

		$id = System::getUserId(); // user id

		// Repositório dos arquivos recebidos
		$filePath = _ROOTPATH.'/files/'; 

		// Gravando no banco de dados
		$db = new DB;
		$db->query('INSERT INTO arquivo (tabela, tabela_id, nome, usuario, uploaddata) VALUES (:tabela, :tabela_id, :nome, :usuario, :uploaddata)',
			[':tabela' => $in->tabela,
		     ':tabela_id' => $in->tabela_id,
		 	 ':nome' => $in->name,
		 	 ':usuario' => $id,
		 	 ':uploaddata' => date('Y-m-d H:i:s')
		 	]);
		$nid = $db->query('SELECT MAX(id) as maxid FROM arquivo')[0]->get('maxid');
		
		// Salvando o arquivo recebido
		file_put_contents($filePath.$nid.'.'.$in->ext, base64_decode($_POST['param']));		
		
		// Saída
		System::send(['msg' => 'Arquivo "'.$in->name.'" salvo no servidor!']);
	}

	/**
	 * Listagem dos arquivos por TABELA/ID
	 * @return json data
	 */
	public function list()
	{
		$in = System::getData();

		if(!isset($in->tabela) || !isset($in->tabela_id)) return System::send(); // Preciso do nome da tabela e do ID do registro

		$db = new DB;
		$res = $db->query('SELECT id, nome FROM arquivo WHERE tabela=:tabela AND tabela_id=:tabela_id', 
					[':tabela' => $in->tabela,
					 ':tabela_id' => $in->tabela_id]);

		if($res){
			$data['title'] = ['id' => 'Id', 'nome' => 'Nome'];
			foreach ($res as $row) {
				$data['row'][] = ['id' => $row->get('id'), 'nome' => $row->get('nome')];
			}

			$data['error'] = false;

			return System::send($data);
		}

		return System::send();
	}

	/**
	 * Download de arquivo
	 * @return mixed O arquivo é enviado ao client
	 */
	function download($rqst, $param)
	{
		if(isset($param['id'])) {

			$file = $this->getFileNameById($param['id'] + 0);

			if($file !== false){

				$arquivo = _ROOTPATH.'/files/'.$file['id'].'.'.$file['ext'];

				if(!is_file($arquivo)) $this->fileNotFound();

			    // Carregando mimeTypes
			    $mime = Mimetype::getByExt($file['ext']);
			    if($mime == false) $this->fileNotFound();

			    // carregando o arquivo
			    $fileContent = file_get_contents($arquivo);

				// limpando alguma saída de erro (ou coisa do tipo)
			    ob_end_clean();
			    ob_start('ob_gzhandler');

			    // criando os cabeçalhos
			    header('Vary: Accept-Language, Accept-Encoding');
			    header('Content-Type: '.$mime);
			    header('Content-Length: ' . strlen($fileContent));

			    // enviando o arquivo
			    exit($fileContent);

			} else {
				$this->fileNotFound();
			}
		} else {
			$this->fileNotFound();
		}
	}

	/**
	 * Exclui o arquivo indicado pelo ID
	 * @return json success
	 */
	public function delete()
	{
		$in = System::getData();
		if(!isset($in->id)) return System::send(); 

		// Pegando o nome (extensão) do arquivo
		$nome = $this->getFileNameById($in->id);

		// Caso o arquivo não mais exista ...
		if($nome == false) return System::send(['error' => false, 'msg' => 'Arquivo "'.$nome['completo'].'" excluído!']);

		// Excluindo no diretório
		$file = _ROOTPATH.'/files/'.(0 + $in->id).'.'.$nome['ext'];
		@unlink($file);

		if(!is_file($file)){
			// Excluindo do banco de dados
			(new DB)->query('DELETE FROM arquivo WHERE id=:id', [':id' => $in->id]);

			return System::send(['error' => false, 'msg' => 'Arquivo "'.$nome['completo'].'" excluído!']);
		}
		return System::send(['error' => true, 'msg' => 'Não consegui excluir o arquivo!']);
	}


	/**
	 * Retorna os dados do arquivo no BD idexado pelo ID
	 * @return array success
	 */
	private function getFileNameById($id)
	{
		// Pegando o nome do arquivo...
		$res = (new DB)->query('SELECT id, nome FROM arquivo WHERE id=:id', [':id' => $id]);

		// Caso não exista, mais (alguém pode ter apagado nesse período entre a listagem e o click no botão de download)
		if(!$res) return false;

		$nome = [];
		$nome['completo'] = $res[0]->get('nome');
		$tmp = explode('.', $nome['completo']);
		$nome['ext'] = array_pop($tmp); // pegando a extensão do arquivo
		$nome['nome'] = implode('.', $tmp); // pegando a parte do nome (mesmo que tenha outros pontos)
		$nome['id'] = $id;

		return $nome;
	}


	/**
	 * Envia aviso de "file not found" e sai
	 * @return void
	 */
	private function fileNotFound()
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
}