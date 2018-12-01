<?php


// Envia dados em JSON
function sendJson($data)
{
   	ob_start("ob_gzhandler");
	header('Access-Control-Allow-Origin:*');
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode($data));
}


// Print (formated) and Exit
function e($v, $log = '')
{
    exit(p($v, $log, false));
}

// Print (formated)
function p($v, $log = '', $show = true)
{
    $p = '<div style="text-family:sans-serif;width:fit-content"><pre style="background:#EFE;color:#090;border-radius:5px;padding:15px;margin:20px;box-shadow:0 5px 30px rgba(0,0,0,.3);width:fit-content"><h3 style="text-align:center;margin:-16px -16px 10px -16px;padding:10px;background:#039;color:#FFF">'.$log.'</h3>'.print_r($v, true).'</pre></div>';

    if (!$show) {
        return $p;
    }
    echo $p;
}

// Print without HTML/CSS, and exit
function ex($v)
{
    exit(px($v, false));
}

// Print without HTML/CSS
function px($v, $show = true)
{
    if(!$show) return print_r($v, true);
    echo print_r($v, true);
}

//Download de arquivo em modo PHAR (interno)
function download($reqst = '') {

    //checando a existencia do arquivo solicitado
    $reqst = _file_exists($reqst);
    if($reqst == false) return false;

    //gerando header apropriado
    include WEB_PATH . '.php/config/mimetypes.php';
    $ext = end((explode('.', $reqst)));
    if (!isset($_mimes[$ext])) $mime = 'text/plain';
    else $mime = (is_array($_mimes[$ext])) ? $_mimes[$ext][0] : $_mimes[$ext];

    //get file
    $dt = file_get_contents($reqst);

    //download
    ob_end_clean();
    ob_start('ob_gzhandler');

    header('Vary: Accept-Language, Accept-Encoding');
    header('Content-Type: ' . $mime);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($reqst)) . ' GMT');
    header('Cache-Control: must_revalidate, public, max-age=31536000');
    header('Content-Length: ' . strlen($dt));
    header('x-Server: Qzumba.com');
    header('ETAG: '.md5($reqst));
    exit($dt);
}