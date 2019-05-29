<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CDBPgsql extends CDBCommon
{
	function connect( $config )
	{
		if( empty( $config['port'] ) )	$config['port'] = 5432;
		$connect	= pg_connect( "host={$config['host']} port={$config['port']} dbname={$config['db']} user={$config['user']} password={$config['pswd']}" );
		return $connect;
	}
	
	
	function query( $query, $connect )
	{
		$this->last_query	= $query;
	    list($usec, $sec)	= explode( " ", microtime() ); 
		$time				= (float)$sec + (float)$usec;
		$res				= pg_query( $connect, $query );
	    list($usec, $sec)	= explode( " ", microtime() ); 
		$this->query_time	= ( (float)$sec + (float)$usec ) - $time;
		if( $res )
		{
			if( $affected = pg_affected_rows( $res ) )
			{
				$this->affected_rows	= $affected;
			}
		}
		else
		{
			$this->last_error	= pg_last_error( $connect );
		}
		$this->_push_log();
		return $res;
	}
	
	
	function find( $query, $connect )
	{
		$ret	= array();
		if( $res = $this->query( $query, $connect ) )
		{
			$rownum	= pg_num_rows( $res );
			for( $i = 0; $i < $rownum; $i++ )
			{
				$row	= pg_fetch_array( $res, $i, PGSQL_ASSOC );
				array_push( $ret, $row );
			}
			pg_free_result( $res );
		}
		
		return $ret;
	}
	
	
	function count( $query, $connect )
	{
		if( $res = $this->query( $query, $connect ) )
		{
			$count	= pg_num_rows( $res );
			pg_free_result( $res );
			return $count;
		}
		
		return 0;
	}
	
	function field($field) {
		return $field;
	}
	
	function escape( $str, $connect )
	{
		if( function_exists( 'pg_escape_string' ) )
		{
			return pg_escape_string( $str );
		}
		
		return addslashes( $str );
	}
	
	
	function describe($connect, $table) {
		$fields = $this->find("SELECT DISTINCT * FROM information_schema.columns WHERE table_name='$table' ORDER BY ordinal_position", $connect);
		$results = array();
		foreach ($fields as $field) {
			$results[$field['column_name']] = array(
				'type' => $this->column($field['data_type']),
				'length' => $this->length($field['data_type']),
				'null' => $field['is_nullable'] == 'YES' ? true : false,
				'default' => $field['column_default'],
				'values' => null,
			);
		}
		return $results;
	}
	
	
	function column($real) {
		if (is_array($real)) {
			$col = $real['name'];
			if (isset($real['limit'])) {
				$col .= '(' . $real['limit'] . ')';
			}
			return $col;
		}

		$col = str_replace(')', '', $real);
		$limit = null;
		@list($col, $limit) = explode('(', $col);

		if (in_array($col, array('date', 'time'))) {
			return $col;
		}
		if (strpos($col, 'timestamp') !== false) {
			return 'datetime';
		}
		if ($col == 'inet') {
			return('inet');
		}
		if ($col == 'boolean') {
			return 'boolean';
		}
		if (strpos($col, 'int') !== false && $col != 'interval') {
			return 'integer';
		}
		if (strpos($col, 'char') !== false) {
			return 'string';
		}
		if (strpos($col, 'text') !== false) {
			return 'text';
		}
		if (strpos($col, 'bytea') !== false) {
			return 'binary';
		}
		if (in_array($col, array('float', 'float4', 'float8', 'double', 'double precision', 'decimal', 'real', 'numeric'))) {
			return 'float';
		}
		return 'text';
	}

	
	function length($real) {
		$col = str_replace(array(')', 'unsigned'), '', $real);
		$limit = null;

		if (strpos($col, '(') !== false) {
			list($col, $limit) = explode('(', $col);
		}

		if ($limit != null) {
			return intval($limit);
		}
		return null;
	}
}
