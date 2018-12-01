<?php
/**
 * Config\Lib\Router
 * PHP version 7
 *
 * @category  Router
 * @package   Config
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2018 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.1
 * @link      Site <https://phatto.ga>
 */

namespace Config\Lib;

(new Router)->respond('get', '/', function(){include _HTMLPATH.'/index.html'; exit();})

    // Download de atualizações
    ->respond('get', '/download', function(){include _HTMLPATH.'/version.html'; exit();})

    // Messages
    ->respond('get',  '/message',        'App/Msg::formHtml')
    ->respond('post', '/message/create', 'App/Msg::create')
    ->respond('post', '/message/lido',   'App/Msg::lido')

    // LOGIN
    ->respond('get',  '/user/rsakey', 'App/User::getkey')
    ->respond('post', '/user/login',  'App/User::connect')
    ->respond('post', '/user/logout', 'App/User::logout')

    // SYSTEM
    ->respond('post', '/ping', 'App/System::ping')



    // Files ------------------------------------------------------------------------- [BEGIN]
    ->respond('post', '/file/save',                 'Lib/File::save')
    ->respond('post', '/file/list',                 'Lib/File::list')
    ->respond('post', '/file/download',             'Lib/File::download') 
    ->respond('get',  '/file/downloadZiped(.*)',    'Lib/File::downloadZiped') 
    ->respond('post', '/file/delete',               'Lib/File::delete')

    // File Manager 2 (simple - not ziped/cripto)
    ->respond('get',  '/file2/download/(?<id>.*?)', 'Lib/File2::download')
    ->respond('post', '/file2/save',                'Lib/File2::save')
    ->respond('post', '/file2/list',                'Lib/File2::list')
    ->respond('post', '/file2/delete',              'Lib/File2::delete');

    // Files ------------------------------------------------------------------------- [END]


//Load others routes
//require_once __DIR__.'/router.others.php';

// See configuration examples at the end of this file.
// ------------------- END ROUTES ----------------------


/**
 * Config\Lib\Router Class
 *
 * @category Router
 * @package  Config
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     Site <https://phatto.ga>
 */

class Router
{
    //WEB
    public static $defaultController    = 'Phatto/Controller';
    public static $defaultAction        = 'notFound';
    public static $namespacePrefix      = '';

    //CLI
    public static $defaultCliController = 'Main';
    public static $defaultCliAction     = 'cliHelp';
    public static $namespaceCliPrefix   = 'Devbr\Cli';

    //Routes
    public static $all                  = [];
    public static $routes               = [];

    public static $http                 = '';
    public static $base                 = '';
    public static $params               = [];
    public static $args                 = [];
    public static $separator            = '::';
    public static $controller           = '';
    public static $action               = '';

    /** 
     *  Constructor
     */
    public function __construct()
    {
        //Defaults routes
        $this->respond('options|head', '.*',
            function () {
                header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT, PATCH, HEAD');
                exit();
            }
        );
    }

    /** 
     *  Adding routes
     */
    public function respond(
        $method = 'get',
        $request = '',
        $controller = null,
        $action = null,
        $args = null
    ) {
        $method = strtoupper(trim($method));
        //Para sintaxe: CONTROLLER::ACTION
        if (!is_object($controller) && strpos($controller, static::$separator) !== false) {
            $a = explode(static::$separator, $controller);
            $controller = isset($a[0]) ? $a[0] : null;
            $action = isset($a[1]) ? $a[1] : null;
        }
        if ($method == 'ALL') {
            static::$all[] = ['request' => trim($request, '/'),
                              'controller' => $controller,
                              'action' => $action,
                              'args' => $args
                            ];
        } else {
            foreach (explode('|', $method) as $mtd) {
                static::$routes[$mtd][] = ['request' => trim($request, '/'),
                                           'controller' => $controller,
                                           'action' => $action,
                                           'args' => $args
                                        ];
            }
        }
        return $this;
    }
}


/*
    SYNTAX
    ======

    $route->respond( <method>, <request>, <controller>, [<action>, <extra>] );

    <method>:       A string with the following methods: "all", "get", "post", "delete", "put", "patch".
                    Or specify a specific group: "get|post|delete".

    <request>:      String of the requested URI - ex.: "about/me" in http://site.com/about/me
                    Or a regular expression (valid PHP),
                        like this: '/path/(.*)' - takes everything after "path" in
                        http://site.com/path/xxx/yyy/zzz (return: "xxx/yyy/zzz")

    <controller>:   Class (object) to manage the request.
                    The value must be a complete string, with NAMESPACE + CLASSNAME. ("Devbr\User", for example).

                    Alternatively you can use the following format: "controller::action", how in "Devbr\User::login".

                    The Controller can also be an anonymous function that receives (or not)
                        parameters of the regular expression in <request>.

                        ->respond('get', '/(*)', function($path){
                                exit( $path );
                            }
                        )
                        -- if request "http://site.com/test/me", print in screen "test/me".

    <action>:       Another way to indicate action (optional)
                        ->respond('get', '/', 'Controller', 'login')
                        -- 'login' being an Action of the Controller.

    <extra>:        Extra data (optional parameters) passed to the controller
                        ->respond('get', '/', 'Controller', 'login', 'guest')
                        -- the parameter can be of any valid type, including, also, dynamic parameters.


    SAMPLES
    =======


    $route->respond('get', '/', 'Resource\Main::index')

    ->respond('get', 'login', 'Blog\Page::login')
    ->respond('get', 'blog', 'Devbr\Install\Page::index')

    ->respond('get', 'blog/e/(?<id>(.*)?)', 'Blog\Page::edit')
    ->respond('get', 'blog/(?<id>.*?)', 'Blog\Page::view')

    //AJAX ----------
    ->respond('post', 'blog/save', 'Blog\Ajax::save')
    ->respond('post', 'blog/checklink', 'Blog\Ajax::checkLink')

    ->respond('post', 'blog/delete/(?<id>(\d+)?)', 'Blog\Ajax::delete')
    ->respond('post', 'blog/upload/(?<id>(\d+)?)', 'Blog\Ajax::upload')

    //REST
    ->respond('post',   'api/data', 'Rest/Ful::create)
    ->respond('get',    'api/data', 'Rest/Ful::read)
    ->respond('put',    'api/data', 'Rest/Ful::update)
    ->respond('patch',  'api/data', 'Rest/Ful::modify)
    ->respond('delete', 'api/data', 'Rest/Ful::delete)


    Considere a url:
    http://localhost/loja/903/Camisa Polo Marca XXX/ldfnld-n0p/+=dlknferçlkm/dsfdfdsjd//

    Captura:
    ['id'] => 903
    ['produto'] => Camisa Polo Marca XXX

    E ignora tudo depois de 'produto'.

    Veja a função "test" da classe em "/.php/Site/Front.php"
    Segue a configuração da rota, abaixo:

        ->respond('get', '/loja/(?<id>.*?)/(?<produto>[^/]*).*', 'Site/Front::test')


    A mesma configuração, porém com parametros (índice) NUMÉRICOS:
    http://localhost/loja2/903/Camisa Polo Marca XXX/ldfnld-n0p/+=dlknferçlkm/dsfdfdsjd//


        ->respond('get', '/loja2/(.*?)/([^/]*).*', 'Site/Front::test')


    Usando uma função anônima diretamente na configuração do Router
    http://localhost/fac/categoria/pergunta

        ->respond('get', '/fac/(.*?)/([^/]*).*',

            function ($type, $user) {
                echo '<h1>Função anônima</h1>
                <p><b>Request URI:</b> '.$type.'<br>
                <b>Parametros:</b><pre>'.print_r($user, true).'</pre></p>';
            }
        )


    Usando uma função anônima para mostrar uma página HTML ESTÁTICA
    http://localhost/about

        ->respond('get', '/about',

                    function () {
                        include _HTMLPATH.'Static/about.html';
                    })
    Mostrando uma página diretamente pela classe Html [ equal: Html->show('about') ]
    http://localhost/about

        ->respond('get', '/about', 'Html', 'show', 'about');

*/
