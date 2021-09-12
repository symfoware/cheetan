<?php
/**----------------------------------------------------------------------------
 * cheetan Web Framework.
 * 
 * The Lightweight PHP Web Framework to Accelerate Development.
 *
 * @version 0.9.0-dev
 * @copyright Copyright 2006 cheetan all right reserved.
 * @license https://opensource.org/licenses/MIT
 * @link http://php.cheetan.net/
-----------------------------------------------------------------------------*/
define( 'LIBDIR', dirname(__FILE__));
// controller内で使用
define( 'SCRIPTFILE', basename( $_SERVER['SCRIPT_FILENAME'] ) );

require_once(LIBDIR . DIRECTORY_SEPARATOR . 'database.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'controller.php');

class Cheetan {

    private $config = [];

    public function loadConfig($config=null) {
        $files = [
            LIBDIR . DIRECTORY_SEPARATOR . 'config.php',
            'config.php',
        ];
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }
            $this->config = array_merge($this->config, require($file));
        }
    }

    public function dispatch() {

        $controller = new CController();
        $controller->setDatabase(new CDatabase());
        $controller->setConfig($this->config);
        $controller->requestHandle();

        if (function_exists('action')) {
            action( $controller );
        }

        $func = 'action_'.$controller->method;
        if (function_exists($func)) {
            $func( $controller );
        }

        $controller->display();

        return $controller;
    }
}


$cheetan = new Cheetan();
isset($config) ? $cheetan->loadConfig($config) : $cheetan->loadConfig();
$c = $cheetan->dispatch();
extract($c->getVariable());

