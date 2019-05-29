<?php

define( "TEXTDB_CR", "##CR##" );
define( "TEXTDB_LF", "##LF##" );
define( "TEXTDB_SEP", "##SEP##" );

class CTextDB
{
	var $fpath = "";
	var $columns;
	var $column;
	var $columncnt;
	var $i;
	var	$datas;
	var $condition;
	var $key;
	var	$data;
	var $cmpkey;
	var $cmpdesc;


	function __construct( $fpath = "" )
	{
		$this->fpath = $fpath;
	}


	function SetFileName( $fpath )
	{
		$this->fpath = $fpath;
	}


	function insert( $datas, $fpath = "" )
	{
		if( !$fpath )	$fpath = $this->fpath;
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$pieces	= array();
			foreach( $this->columns as $i => $column )
			{
				if( $i )
				{
					$pieces[$column] = $datas[$column];
				}
				else
				{
					$pieces[$column] = $maxid;
				}
			}
			$recode	= implode( TEXTDB_SEP, $pieces );
			$recode	= $this->_initstr( $recode );
			
			$file	= file( $fpath );
			$count	= count( $file );
			if( $fp = fopen( $fpath, "w" ) )
			{
				flock( $fp, LOCK_EX );
				$maxid++;
				fwrite( $fp, $file[0] );
				fwrite( $fp, $maxid . "\n" );
				for( $i = 2; $i < $count; $i++ )
				{
					fwrite( $fp, $file[$i] );
				}
				fwrite( $fp, $recode . "\n" );
				fclose( $fp );
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function update( $datas, $condition, $fpath = "" )
	{
		if( !$fpath )	$fpath = $this->fpath;
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$file	= file( $fpath );
			$count	= count( $file );
			if( $fp = fopen( $fpath, "w" ) )
			{
				flock( $fp, LOCK_EX );
				fwrite( $fp, $file[0] );
				fwrite( $fp, $file[1] );
				$this->condition	= $condition;
				for( $i = 2; $i < $count; $i++ )
				{
					$this->datas	= explode( TEXTDB_SEP, trim( $file[$i] ) );
					if( $this->_check_condition() )
					{
						$pieces	= array();
						foreach( $this->columns as $j => $column )
						{
							if( $j )
							{
								if( array_key_exists( $column, $datas ) )
								{
									$pieces[$column] = $datas[$column];
								}
								else
								{
									$pieces[$column] = $this->datas[$j];
								}
							}
							else
							{
								$pieces[$column] = $this->datas[$j];
							}
						}
						$recode	= implode( TEXTDB_SEP, $pieces );
						$recode	= $this->_initstr( $recode );
						fwrite( $fp, $recode . "\n" );
					}
					else
					{
						fwrite( $fp, $file[$i] );
					}
				}
				fclose( $fp );
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function delete( $condition, $fpath = "" )
	{
		if( !$fpath )	$fpath = $this->fpath;
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$file	= file( $fpath );
			$count	= count( $file );
			if( $fp = fopen( $fpath, "w" ) )
			{
				flock( $fp, LOCK_EX );
				fwrite( $fp, $file[0] );
				fwrite( $fp, $file[1] );
				if( !strlen( $condition ) )	$condition = "1";
				$this->condition	= $condition;
				for( $i = 2; $i < $count; $i++ )
				{
					$this->datas	= explode( TEXTDB_SEP, trim( $file[$i] ) );
					if( !$this->_check_condition() )
					{
						fwrite( $fp, $file[$i] );
					}
				}
				fclose( $fp );
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function select( $condition, $order = "", $fpath = "", $limit = '' )
	{
		if( !$fpath )	$fpath = $this->fpath;
		$ret	= array();
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$file				= file( $fpath );
			$count				= count( $file );
			if( !strlen( $condition ) )	$condition = "1";
			$this->condition	= $condition;
			$limit				= $this->_parse_limit( $limit );
			if( $limit )
			{
				$lstart	= $limit[0];
				$lnum	= $limit[1];
			}
			for( $i = 2; $i < $count; $i++ )
			{
				$this->datas	= explode( TEXTDB_SEP, trim( $file[$i] ) );
				if( $this->_check_condition() )
				{
					$data	= array();
					for( $j = 0; $j < $this->columncnt; $j++ )
					{
						$data[$this->columns[$j]]	= $this->_reversestr( $this->datas[$j] );
					}
					array_push( $ret, $data );
				}
			}
			if( count( $ret ) )
			{
				$this->_sort_records( $ret, $order );
			}
		}
		
		if( $limit )	return array_slice( $ret, $lstart, $lnum );
		
		return $ret;
	}
	
	function count( $condition, $fpath = "" )
	{
		if( !$fpath )	$fpath = $this->fpath;
		$ret	= 0;
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$file				= file( $fpath );
			$count				= count( $file );
			if( !strlen( $condition ) )	$condition = "1";
			$this->condition	= $condition;
			for( $i = 2; $i < $count; $i++ )
			{
				$this->datas	= explode( TEXTDB_SEP, trim( $file[$i] ) );
				if( $this->_check_condition() )
				{
					$ret++;
				}
			}
		}
		
		return $ret;
	}
	
	function _read_header( $fpath, &$maxid )
	{
		$ret	= FALSE;
	
	
		if( $fp = fopen( $fpath, "r" ) )
		{
			$line	= trim( fgets( $fp ) );
			if( $line )
			{
				$this->columns		= explode( ",", $line );
				$this->columncnt	= count( $this->columns );
				$line				= trim( fgets( $fp ) );
				if( $line )
				{
					$maxid	= $line;
					$ret	= TRUE;
				}
			}
			fclose( $fp );
		}
		
		return $ret;
	}
	
	function _check_condition()
	{
		for( $this->i = 0; $this->i < $this->columncnt; $this->i++ )
		{
			$this->column	= $this->columns[$this->i];
			$this->data		= $this->datas[$this->i];
			$str = '$' . $this->column . " = '" . $this->data . "';";
			eval( $str );
		}
		
		$str	= '
		if( ' . $this->condition . ' )
		{
			$ret = TRUE;	
		}
		else
		{
			$ret = FALSE;
		}
		';
		eval( $str );
		
		return $ret;
	}
	
	function _initstr( $str )
	{
		$str	= str_replace( "\r", TEXTDB_CR, $str );
		$str	= str_replace( "\n", TEXTDB_LF, $str );
		return $str;
	}
	
	function _reversestr( $str )
	{
		$str	= str_replace( TEXTDB_CR, "\r", $str );
		$str	= str_replace( TEXTDB_LF, "\n", $str );
		return $str;
	}
	
	function _cmpfunc( $a, $b )
	{
	    if( $a[$this->cmpkey] == $b[$this->cmpkey] )
		{
	        return 0;
	    }
		$ret	= ( $a[$this->cmpkey] < $b[$this->cmpkey] ) ? -1 : 1;
		if( $this->cmpdesc )	$ret *= -1;
	    return $ret;
	}
	
	function _sort_records( &$records, $order )
	{
		switch( $order )
		{
		case 'RAND()':
			shuffle( $records );
			break;
		default:
			$order			= trim( $order );
			$pos			= strpos( $order, " " );
			$this->cmpdesc	= FALSE;
			if( $pos === FALSE )
			{
				$this->cmpkey	= $order;
			}
			else
			{
				$this->cmpkey	= substr( $order, 0, $pos );
				if( trim( strtolower( substr( $order, $pos ) ) ) == "desc" )
				{
					$this->cmpdesc	= TRUE;
				}
			}
			if( $this->cmpkey && array_key_exists( $this->cmpkey, $records[0] ) )
			{
				usort( $records, array( &$this, '_cmpfunc' ) );
			}
			break;
		}
	}
	
	
	function _parse_limit( $limit )
	{
		$limit	= trim( $limit );
		if( !$limit )	return null;
		
		if( strpos( $limit, ',' ) )
		{
			list( $start, $num )	= explode( ',', $limit );
			return array( intval( $start ), intval( $num ) );
		}
		
		return array( 0, intval( $limit ) );
	}
}
