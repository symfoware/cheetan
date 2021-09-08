<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CDBCommon {

    private $last_insert_id = null;
    private $affected_rows = null;
    private $last_query = null;
    private $last_error = null;
    private $query_time = null;
    private $sqllog = [];


    public function connect( $config ) {
        return false;
    }
    
    
    public function query( $query, $connect ) {
        return false;
    }
    
    
    public function find( $query, $connect ) {
        return [];
    }
    
    
    public function count( $query, $connect ) {
        return 0;
    }
    
    
    public function getFindQuery($connect, $query, $condition = null, $order = '', $limit = '', $group = '') {
        if($condition) {
            $query .= ' WHERE ' . $this->parseCondition($condition, $connect);
        }
        if($group) {
            $query .= " GROUP BY $group";
        }
        if($order) {
            $query .= " ORDER BY $order";
        }
        if($limit)  {
            $query .= " LIMIT $limit";
        }
        return $query;
    }
    
    
    public function parseCondition($data, $connect) {
        if (!is_array($data)) {
            return $data;
        }
        $queries = array();
        foreach ($data as $field => $value) {
            $queries[] = $this->createCondition($field, $value, $connect);
        }
        return join(' AND ', $queries);
    }
    
    
    public function findQuery( $connect, $query, $condition = '', $order = '', $limit = '', $group = '' ) {
        $query = $this->getFindQuery( $connect, $query, $condition, $order, $limit, $group );
        return $this->find( $query, $connect );
    }
    
    
    public function findAll($connect, $table, $condition = null, $order = '', $limit = '', $group = '') {
        $query = "SELECT * FROM $table ";
        $query = $this->getFindQuery( $connect, $query, $condition, $order, $limit, $group);
        return $this->find($query, $connect);
    }
    
    
    public function getCount($connect, $table, $condition = null, $limit = '') {
        $query = "SELECT COUNT(*) FROM $table ";
        $query = $this->getFindQuery($connect, $query, $condition, "", $limit);
        $results = $this->find($query, $connect);
        //var_export($query);
        return $results[0]['COUNT(*)'];
    }
    
    
    public function insert($table, $data, $connect) {
        $count = count($data);
        $query = "INSERT INTO $table(";
        $i = 0;
        foreach ($data as $key => $value) {
            $query .= $this->field($key);
            if ($i < $count - 1) {
                $query .= ",";
            }
            $i++;
        }
        $query    .= ") VALUES(";
        $i        = 0;
        foreach ($data as $key => $value) {
            $query .= $this->value($value, $connect);
            if ($i < $count - 1) {
                $query .= ",";
            }
            $i++;
        }
        $query    .= ")";
        return $this->query($query, $connect);
    }
    
    
    public function update($table, $data, $condition, $connect) {
        $count = count($data);
        $query = "UPDATE $table SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            $query .= $this->createCondition($key, $value, $connect);
            if ($i < $count - 1) {
                $query .= ",";
            }
            $i++;
        }
        $query .= " WHERE " . $this->parseCondition($condition, $connect);
        return $this->query($query, $connect);
    }
    
    
    public function del($table, $condition, $connect) {
        $query    = "DELETE FROM $table WHERE " . $this->parseCondition($condition, $connect);
        return $this->query( $query, $connect );
    }
    
    
    public function field($field) {
        return "`$field`";
    }
    
    public function value($value, $connect) {
        if ($value === null) return 'NULL';
        $value = $this->escape($value, $connect);
        //return "'$value'";
        return $value;
    }
    
    public function createCondition($field, $value, $connect) {
        return $this->field($field) . '=' . $this->value($value, $connect);
    }
    
    
    public function escape( $str, $connect ) {
        return $str;
    }
    
    
    public function getLastInsertId() {
        return $this->last_insert_id;
    }
    
    
    public function getAffectedRows() {
        return $this->affected_rows;
    }
    
    
    public function getLastError() {
        return $this->last_error;
    }
    
    
    private function _push_log() {
        $log['last_insert_id']    = $this->last_insert_id;
        $log['affected_rows']    = $this->affected_rows;
        $log['query']            = $this->last_query;
        $log['error']            = $this->last_error;
        $log['query_time']        = $this->query_time;
        array_push( $this->sqllog, $log );
    }
    
    
    public function getSqlLog() {
        return $this->sqllog;
    }
    
    
    public function describe($connect, $table) {
        return null;
    }
    
    
    public function length($real) {
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
