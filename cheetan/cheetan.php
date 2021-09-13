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








// ----------------------------------------------------------------------------
class Cheetan {

    private $config = [];

    public function loadConfig() {
        // cheetan.phpと同じ階層のconfig.php
        // 実行スクリプトと同じ階層のconfig.phpを読み取り設定
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
        $controller->db = new CDatabase();
        $controller->setConfig($this->config);
        $controller->requestHandle();

        // 呼び出し元のaction関数実行
        if (function_exists('action')) {
            action( $controller );
        }

        // 呼び出し元にaction_[method]が存在すれば実行
        $func = 'action_'.strtolower($controller->method);
        if (function_exists($func)) {
            $func( $controller );
        }

        $controller->display();

        return $controller;
    }
}


$cheetan = new Cheetan();
$cheetan->loadConfig();
$c = $cheetan->dispatch();
extract($c->getVariable());

