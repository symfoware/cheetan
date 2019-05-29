<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
if( !defined( "LIBDIR" ) )
{
	define( "LIBDIR", dirname(__FILE__) );
}

require_once LIBDIR . DIRECTORY_SEPARATOR . "boot.php";

$data		= array();
$sanitize	= new CSanitize();
$s			= $sanitize;

$dispatch	= new CDispatch();
$controller	= $dispatch->dispatch( $data );
$c			= &$controller;

