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

require_once(LIBDIR . DIRECTORY_SEPARATOR . 'database.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'controller.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'view.php');

require_once(LIBDIR . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'common.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'mysql.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'pgsql.php');

// controller内で使用
define( 'SCRIPTFILE', basename( $_SERVER['SCRIPT_FILENAME'] ) );


class Cheetan {

    public function dispatch() {

        $db = new CDatabase();
        if( function_exists( 'config_database' ) ) {
            config_database( $db );
        }
    
        $controller = new CController();

        $controller->requestHandle();
        $controller->setDatabase( $db );

        if( !function_exists( 'is_session' ) || is_session() ) {
            session_start();
        }

        if( function_exists( 'action' ) ) {
            action( $controller );
        }
        
        $template = $controller->getTemplateFile();
        $viewfile = $controller->getViewFile();
        $variable = $controller->getVariable();
        $sqllog = $controller->getSqlLog();
        $is_debug = $controller->getDebug();
        
        $view = new CView();
        $view
            ->setFile( $template, $viewfile )
            ->setVariable( $variable )
            ->setController( $controller )
            ->setSqlLog( $sqllog );
        $view->display();

        return $controller;
    }
}


$cheetan = new Cheetan();
$controller = $cheetan->dispatch();
$c = &$controller;

$data = $controller->getVariable();
