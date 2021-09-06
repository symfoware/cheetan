<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
define( 'LIBDIR', dirname(__FILE__));

require_once(LIBDIR . DIRECTORY_SEPARATOR . 'database.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'sanitize.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'validate.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'controller.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'view.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'model.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'dispatch.php');

require_once(LIBDIR . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'common.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'mysql.php');
require_once(LIBDIR . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'pgsql.php');

// controller内で使用
define( 'SCRIPTFILE', basename( $_SERVER['SCRIPT_FILENAME'] ) );


//$data		= [];
//$sanitize	= new CSanitize();
//$s			= $sanitize;

$dispatch	= new CDispatch();
$controller	= $dispatch->dispatch();
$c			= &$controller;

$data = $controller->GetVariable();