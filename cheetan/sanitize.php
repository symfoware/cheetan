<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CSanitize
{
	function html( $data )
	{
		$data	= htmlspecialchars( $data );
		return $data;
	}
	

	function post( $key )
	{
		$data	= $this->html( $_POST[$key] );
		return $data;
	}


	function postt( $key )
	{
		$data	= $this->post( $key );
		$data	= trim( $data );
		return $data;
	}


	function posts( $key )
	{
		$data			= $this->post( $key );
		$_SESSION[$key]	= $data;
		return $data;
	}


	function postst( $key )
	{
		$data			= $this->postt( $key );
		$_SESSION[$key]	= $data;
		return $data;
	}
	

	function get( $key )
	{
		$data	= $this->html( $_GET[$key] );
		return $data;
	}


	function gett( $key )
	{
		$data	= $this->get( $key );
		$data	= trim( $data );
		return $data;
	}


	function gets( $key )
	{
		$data			= $this->get( $key );
		$_SESSION[$key]	= $data;
		return $data;
	}


	function getst( $key )
	{
		$data			= $this->gett( $key );
		$_SESSION[$key]	= $data;
		return $data;
	}
}
