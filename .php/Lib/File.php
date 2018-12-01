<?php

namespace Lib;

use App\System as System;
use Lib\Database as DB;

class File
{
	function save()
	{
		$in = System::getData(); 
		if(!isset($_POST['param'])) return System::send(); // Se não tiver o arquivo, sai.

		$id = System::getUserId(); // user id

		// Pegando a extensão do nome do arquivo original
		$tm = explode('.', $in->name);
		$ext = '.'.end($tm);

		// nome temporário para os arquivos
		$tmpname = $in->tmpname;
		$filePath = _ROOTPATH.'/files/'; // repositório dos arquivos recebidos
		$tmppath = _ROOTPATH.'/tmp/'; // diretório temporário
		$passw = System::getUserKey(); // senha
		
		// Salvando o arquivo recebido na pasta "./tmp/nxnxnxnx.7z"
		file_put_contents($tmppath.$tmpname.'.7z', base64_decode($_POST['param']));		

		// Extract file to tmp path
		$echo = exec('7z e '.$tmppath.$tmpname.'.7z -o"'.$tmppath.'" -p"'.$passw.'" -y');

		// Checando se o arquivo e legível
		if(!is_file($tmppath.$tmpname.$ext)){
			// removendo os arquivos temporários
			$echo =  @unlink($tmppath.$tmpname.$ext);
			$echo .= @unlink($tmppath.$tmpname.'.7z');
			System::send(['error' => true,
					   'echo' => $echo,
					   'ext' => $ext,
					   'original' => $tmppath.$tmpname.$ext,
					   'zip' => $tmppath.$tmpname.'.7z',
					   'msg' => 'Não consegui gravar o arquivo <br>"'.$in->name.'"!!<br>Pode ser algum caractere acentuado ou o tamanho do título que tenha excedido o limite.<br>Tente renomea-lo e envia-lo novamente.']);
		}

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

		// Movendo o arquivo para a pasta de arquivos
		rename($tmppath.$tmpname.$ext, $filePath.$nid.$ext);

		// Limpando ...
		$echo =  unlink($tmppath.$tmpname.$ext);
		$echo .= unlink($tmppath.$tmpname.'.7z');

		// Saída
		System::send(['msg' => 'Arquivo "'.$in->name.'" salvo no servidor!',
			       'echo' => $echo,
			       'name' => $in->name]);
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
	public function download()
	{
		return System::send(['error' => true, 'msg' => 'TESTE DOWNLOAD']);

		$in = System::getData(); return System::send(['error' => true, 'msg' => print_r($in, true)]);
		
		if(!isset($in->id)) return System::send(['error' => true]);

		// pegando o nome do arquivo
		$nome = $this->getFileNameById($in->id);
		$passw = System::getUserKey(); // senha

		$filePath = _ROOTPATH.'/files/'; // repositório dos arquivos
		$tmpPath = _ROOTPATH.'/tmp/'; // diretório temporário
		$nomeAleatorio = md5(time());
		
		//$contents = base64_encode(file_get_contents($filePath.$in->id.'.'.$nome['ext']));
		
		$echo = mkdir($tmpPath.$nomeAleatorio, 0777);

		return System::send(['error' => true, 'msg' => $echo.' || '.print_r($in, true)]);
		
		// Copiando o arquivo para o diretório temporário (com o nome original)...
		@mkdir($tmpPath.$nomeAleatorio, 0777); //criando um diretório de trabalho
		@copy($filePath.$in->id.'.'.$nome['ext'], $tmpPath.$nomeAleatorio.'/'.$nome['completo']); //copiando o arquivo 

		if(is_file($tmpPath.$nomeAleatorio.'/'.$nome['completo'])){
			// Zipando...
			$echo = exec('7z a -p"'.$passw.'" '.$tmpPath.$nomeAleatorio.'.zip "'.$tmpPath.$nomeAleatorio.'/'.$nome['completo'].'" -y -sdel');

			// checando se o arquivo existe
			if(is_file($tmpPath.$nomeAleatorio.'.zip')){

				// Pegando o conteúdo zipado
				//$contents = base64_encode(file_get_contents($tmpPath.$nomeAleatorio.'.zip'));

				// apagando o diretório temporário
				@unlink($tmpPath.$nomeAleatorio.'/'.$nome['completo']);
				@rmdir($tmpPath.$nomeAleatorio);

				// retornando OK
				return System::send(['error' => false, 'file' => $nomeAleatorio.'.zip', 'nome' => $nome['completo'], 'echo' => $echo]);
			}
		}

		// apagando o diretório temporário
		@unlink($tmpPath.$nomeAleatorio.'.zip');
		@unlink($tmpPath.$nomeAleatorio.'/'.$nome['completo']);
		@rmdir($tmpPath.$nomeAleatorio);

		return System::send(['error' => true]);
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

		return $nome;
	}

	public function downloadZiped()
	{
		if(!isset($_GET['file'])) exit('');
		if(isset($_GET['delete'])) {
			// removendo o arquivo e saíndo
			unlink(_ROOTPATH.'/tmp/'.$_GET['file']);
			exit('');
		}

		// checando a existência do arquivo
		$file = _ROOTPATH.'/tmp/'.$_GET['file'];
		if(!is_file($file)) exit('');

	    // carregando o arquivo
	    $fileContent = file_get_contents($file);

	    // apagando o arquivo da pasta tmp (1 download permitido, por segurança)
	    unlink($file);

		// limpando alguma saída de erro (ou coisa do tipo)
	    ob_end_clean();
	    ob_start('ob_gzhandler');

	    // criando os cabeçalhos
	    header('Vary: Accept-Language, Accept-Encoding');
	    header('Content-Type: application/zip');
	    header('Content-Length: ' . strlen($fileContent));

	    // enviando o arquivo
	    exit($fileContent);
	}
}