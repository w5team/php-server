<?php 

// include functions helper
include __DIR__.'/functions.php';

// Allow to AJAX access for ALL
header('Access-Control-Allow-Origin:*');

// Defaults
error_reporting(E_ALL ^ E_STRICT ^ E_WARNING);
setlocale(LC_ALL, 'pt_BR');
date_default_timezone_set('America/Sao_Paulo');


// Development only...
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('track_errors', '1');


// Constants
define('_ROOTPATH', __DIR__);               // Root of all (not web root)
define('_PHPPATH', _ROOTPATH.'/.php');     // Path to PHP application files
define('_HTMLPATH', _ROOTPATH.'/.html');    // Path to HTML files (templates)
define('_CACHEPATH', _HTMLPATH.'/.cache');   // all caches here
define('_WWWPATH', _ROOTPATH.'/public');   // Path to public folder
define('_APPMOD', 'dev');                 // Application modes: dev|pro
//define('_URL',        'http://localhost');    // force, but the router creates this


// Composer ( & all others ) autoload --
$autoload = _PHPPATH.'/Composer/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(
        function ($class) {
            $file = str_replace('\\', '/', _PHPPATH.'/'.$class.'.php');
            if (file_exists($file)) {
                require $file;
            }
        }
    );
}

// Proxy para criptografia
App\System::input();

// Running Router
Lib\Router::this()->run();



/*
    --------------------------
    That's all for now, folks!
    --------------------------

*/