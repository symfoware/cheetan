<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CDBMysql extends CDBCommon {

	function connect($config) {
		$connect_string = 'mysql:';
		$connect_string .= 'host='.$config['host'];
		$connect_string .= ';dbname='.$config['host'];
		$connect_string .= ';dbname='.$config['db'];
		$connect_string .= ';charset=utf8mb4';
		if (!empty($config['port'])) {
			$connect_string .= ';port='.$config['port'];
		}
		$pdo = new PDO($connect_string, $config['user'], $config['pswd']);
		return $pdo;
		
		/*
		$host = $config['host'];
		if (!empty($config['port'])) {
			$host .= ':' . $config['port'];
		}
		$connect = mysql_connect($host, $config['user'], $config['pswd']);
		if($connect) {
			mysql_select_db($config['db'], $connect);
		}
		return $connect;
		*/
	}
	
	
	function query( $query, $connect )
	{
		
		$this->last_query	= $query;
	    list($usec, $sec)	= explode( " ", microtime() ); 
		$time				= (float)$sec + (float)$usec;
		//$res				= mysql_query( $query, $connect );
		$res = $connect->query($query);

	    list($usec, $sec)	= explode( " ", microtime() ); 
		$this->query_time	= ( (float)$sec + (float)$usec ) - $time;
		if( $res )
		{
			//if( $last_insert_id = mysql_insert_id( $connect ) )
			if( $last_insert_id = $connect->lastInsertId() )
			{
				$this->last_insert_id	= $last_insert_id;
			}
			
			//if( $affected = mysql_affected_rows( $connect ) )
			if( $affected = $res->rowCount() )
			{
				$this->affected_rows	= $affected;
			}
			
		}
		else
		{
			//$this->last_error	= mysql_error( $connect );
			$this->last_error = $connect->errorInfo();
		}
		$this->_push_log();
		return $res;
	}
	
	
	function find( $query, $connect )
	{
		$ret	= array();
		if( $res = $this->query( $query, $connect ) )
		{
			$ret = $res->fetchAll(PDO::FETCH_ASSOC );
			/*
			foreach($res as $row) {
				$ret[] = $row;
			}
			*/
			/*
			while( $row = mysql_fetch_assoc( $res ) )
			{
				array_push( $ret, $row );
			}
			mysql_free_result( $res );
			*/
		}
		
		return $ret;
	}
	
	
	/*
	function count( $query, $connect )
	{
		if( $res = $this->query( $query, $connect ) )
		{
			$count	= mysql_num_rows( $res );
			mysql_free_result( $res );
			return $count;
		}
		
		return 0;
	}
	*/
	
	
	function escape( $str, $connect )
	{
		return $connect->quote($str);
		/*
		if( function_exists( 'mysql_real_escape_string' ) )
		{
			return mysql_real_escape_string( $str );
		}
		
		return mysql_escape_string( $str );
		*/
	}
	
	
	function describe($connect, $table) {
		$fields = $this->find("DESCRIBE $table", $connect);
		$results = array();
		foreach ($fields as $field) {
			$values = null;
			$length = null;
			if ($pos = strpos($field['Type'], '(')) {
				$type = substr($field['Type'], 0, $pos);
				$inner = substr($field['Type'], $pos + 1, strlen($field['Type']) - 2 - $pos);
				if (preg_match('/^enum/', $field['Type'])) {
					$values = str_replace("','", ",", $inner);
					$values = substr($values, 1, strlen($values) - 2);
					$values = explode(',', $values);
				} else {
					$length = $this->length($field['Type']);
				}
			} else {
				$type = $field['Type'];
			}
			$results[$field['Field']] = array(
				'type' => $this->column($type),
				'length' => $length,
				'null' => $field['Null'] == 'YES' ? true : false,
				'default' => $field['Default'] == 'NULL' ? null : $field['Default'],
				'values' => $values,
			);
		}
		return $results;
	}
	
	
	function column($real) {
		if (is_array($real)) {
			$col = $real['name'];
			if (isset($real['limit'])) {
				$col .= '('.$real['limit'].')';
			}
			return $col;
		}

		$col = str_replace(')', '', $real);
		$limit = $this->length($real);
		@list($col,$vals) = explode('(', $col);

		if (in_array($col, array('date', 'time', 'datetime', 'timestamp'))) {
			return $col;
		}
		if ($col == 'tinyint' && $limit == 1) {
			return 'boolean';
		}
		if (strpos($col, 'int') !== false) {
			return 'integer';
		}
		if (strpos($col, 'char') !== false || $col == 'tinytext') {
			return 'string';
		}
		if (strpos($col, 'text') !== false) {
			return 'text';
		}
		if (strpos($col, 'blob') !== false || $col == 'binary') {
			return 'binary';
		}
		if (in_array($col, array('float', 'double', 'decimal'))) {
			return 'float';
		}
		if (strpos($col, 'enum') !== false) {
			return "enum";
		}
		if ($col == 'boolean') {
			return $col;
		}
		return 'text';
	}
}
