<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CDBCommon
{
	var $last_insert_id	= null;
	var $affected_rows	= null;
	var $last_query		= null;
	var $last_error		= null;
	var $query_time		= null;
	var $sqllog			= array();


	function connect( $config )
	{
		return false;
	}
	
	
	function query( $query, $connect )
	{
		return false;
	}
	
	
	function find( $query, $connect )
	{
		return array();
	}
	
	
	function count( $query, $connect )
	{
		return 0;
	}
	
	
	function GetFindQuery($connect, $query, $condition = null, $order = "", $limit = "", $group = "") {
		if($condition)	$query .= " WHERE " . $this->parseCondition($condition, $connect);
		if($group)		$query .= " GROUP BY $group";
		if($order)		$query .= " ORDER BY $order";
		if($limit)		$query .= " LIMIT $limit";
		return $query;
	}
	
	
	function parseCondition($data, $connect) {
		if (!is_array($data)) return $data;
		$queries = array();
		foreach ($data as $field => $value) {
			$queries[] = $this->CreateCondition($field, $value, $connect);
		}
		return join(' AND ', $queries);
	}
	
	
	function findquery( $connect, $query, $condition = "", $order = "", $limit = "", $group = "" )
	{
		$query	= $this->GetFindQuery( $connect, $query, $condition, $order, $limit, $group );
		return $this->find( $query, $connect );
	}
	
	
	function findall($connect, $table, $condition = null, $order = "", $limit = "", $group = "") {
		$query	= "SELECT * FROM $table ";
		$query	= $this->GetFindQuery( $connect, $query, $condition, $order, $limit, $group);
		return $this->find($query, $connect);
	}
	
	
	function getcount($connect, $table, $condition = null, $limit = "") {
		$query	= "SELECT COUNT(*) FROM $table ";
		$query	= $this->GetFindQuery($connect, $query, $condition, "", $limit);
		$results = $this->find($query, $connect);
		var_export($query);
		return $results[0]['COUNT(*)'];
	}
	
	
	function insert($table, $data, $connect) {
		$count	= count($data);
		$query	= "INSERT INTO $table(";
		$i		= 0;
		foreach ($data as $key => $value) {
			$query .= $this->field($key);
			if ($i < $count - 1) {
				$query .= ",";
			}
			$i++;
		}
		$query	.= ") VALUES(";
		$i		= 0;
		foreach ($data as $key => $value) {
			$query .= $this->value($value, $connect);
			if ($i < $count - 1) {
				$query .= ",";
			}
			$i++;
		}
		$query	.= ")";
		return $this->query($query, $connect);
	}
	
	
	function update($table, $data, $condition, $connect) {
		$count	= count($data);
		$query	= "UPDATE $table SET ";
		$i		= 0;
		foreach ($data as $key => $value) {
			$query .= $this->CreateCondition($key, $value, $connect);
			if ($i < $count - 1) {
				$query .= ",";
			}
			$i++;
		}
		$query	.= " WHERE " . $this->parseCondition($condition, $connect);
		return $this->query($query, $connect);
	}
	
	
	function del($table, $condition, $connect) {
		$query	= "DELETE FROM $table WHERE " . $this->parseCondition($condition, $connect);
		return $this->query( $query, $connect );
	}
	
	
	function field($field) {
		return "`$field`";
	}
	
	function value($value, $connect) {
		if ($value === null) return 'NULL';
		$value = $this->escape($value, $connect);
		//return "'$value'";
		return $value;
	}
	
	function CreateCondition($field, $value, $connect) {
		return $this->field($field) . '=' . $this->value($value, $connect);
	}
	
	
	function escape( $str, $connect )
	{
		return $str;
	}
	
	
	function GetLastInsertId()
	{
		return $this->last_insert_id;
	}
	
	
	function GetAffectedRows()
	{
		return $this->affected_rows;
	}
	
	
	function GetLastError()
	{
		return $this->last_error;
	}
	
	
	function _push_log()
	{
		$log['last_insert_id']	= $this->last_insert_id;
		$log['affected_rows']	= $this->affected_rows;
		$log['query']			= $this->last_query;
		$log['error']			= $this->last_error;
		$log['query_time']		= $this->query_time;
		array_push( $this->sqllog, $log );
	}
	
	
	function GetSqlLog()
	{
		return $this->sqllog;
	}
	
	
	function describe($connect, $table) {
		return null;
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
