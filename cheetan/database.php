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

class CDatabase {

    private $config = [];
    private $query = '';
    private $condition = [];
    private $connections = [];
    private $logs = [];

    public function setConfig($config) {
        if (!array_key_exists('database', $config)) {
            return;
        }
        $this->config = array_merge($this->config, $config['database']);
    }

    public function connect($config) {
        $type = $config['type'];
        $user = $config['user'] ?? null;
        $password = $config['password'] ?? null;
        unset($config['type'], $config['user'], $config['password']);

        $constr = $type.':';
        switch($type) {
            case 'sqlite':
                $constr .= $config['dbname'];
                unset($config['dbname']);
            break;
        }

        foreach($config as $key => $val) {
            $constr .= sprintf('%s=%s;', $key, $val);
        }
        return new PDO($constr, $user, $password);
    }

    public function query($query) {
        $this->query = $query;
        return $this;
    }

    public function execute($target='default') {

        if (!array_key_exists($target, $this->connections)) {
            $this->connections[$target] = $this->connect($this->config[$target]);
        }
        $con = $this->connections[$target];

        if (empty($this->query)) {
            $this->query = $this->buildQuery($con);
        }

        $start = $this->getTime();
        $res = $con->query($this->query);
        $end = $this->getTime();

        if (!array_key_exists($target, $this->logs)) {
            $this->logs[$target] = [];
        }
        
        $this->logs[$target][] = [
            //'last_insert_id' => $con->lastInsertId() ,
            'affected_rows' => $res->rowCount(),
            'query' => $this->query,
            'error' => implode(',', $con->errorInfo()),
            'query_time' => $end - $start
        ];

        $this->query = '';
        if (!$res) {
            return [];
        }
        return $res->fetchAll(PDO::FETCH_ASSOC );

    }

    public function select($fields='') {
        if (!array_key_exists('select', $this->condition)) {
            $this->condition['select'] = [];
        }

        if (empty($fields)) {
            return $this;
        }
        if (is_array($fields)) {
            $this->condition['select'] = array_merge($this->condition['select'], $fields);
            return $this;
        }
        $this->condition['select'][] = $fields;
        return $this;
    }

    public function from($from) {
        $this->condition['from'] = $from;
        return $this;
    }

    public function where(...$args) {
        if (!array_key_exists('where', $this->condition)) {
            $this->condition['where'] = [];
        }

        $field = $args[0];
        if (count($args) == 2) {
            $operater = '=';
            $value = $args[1];
        } else {
            $operater = $args[1];
            $value = $args[2];
        }

        $this->condition['where'][] = [$field, $operater, $value];
        return $this;
    }


    public function order_by($order_by) {
        if (!array_key_exists('order_by', $this->condition)) {
            $this->condition['order_by'] = [];
        }
        $this->condition['order_by'][] = $order_by;
        return $this;
    }

    public function limit($limit) {
        $this->condition['limit'] = $limit;
        return $this;
    }

    public function insert($table) {
        $this->condition['insert'] = $table;
        return $this;
    }

    public function update($table) {
        $this->condition['update'] = $table;
        return $this;
    }

    public function delete($table) {
        $this->condition['delete'] = $table;
        return $this;
    }

    public function values($values) {
        $this->condition['values'] = $values;
        return $this;
    }

    public function unescape($field) {
        return function() use ($field) {
            return $field;
        };
    }

    private function buildQuery($con) {
        $query = '';
        if (array_key_exists('select', $this->condition)) {
            $query .= 'SELECT ';
            if (empty($this->condition['select'])) {
                $query .= '*';
            } else {
                $query .= implode(',', $this->condition['select']);
            }
        }

        if (array_key_exists('insert', $this->condition)) {
            $fields = [];
            $values = [];
            foreach($this->condition['values'] as $field => $value) {
                $fields[] = $field;
                $values[] = $this->getQuoteValue($value, $con);
            }
            $query .= 'INSERT INTO '.$this->condition['insert'];
            $query .= ' ('.implode(',', $fields) . ')';
            $query .= ' VALUES ('.implode(',', $values) . ')';
        }

        if (array_key_exists('update', $this->condition)) {
            $set = [];
            foreach($this->condition['values'] as $field => $value) {
                $set[] = sprintf(' %s = %s', $field, $this->getQuoteValue($value, $con));
            }
            $query .= 'UPDATE '.$this->condition['update'];
            $query .= ' SET '.implode(',', $set);
        }

        if (array_key_exists('delete', $this->condition)) {
            $query .= 'DELETE FROM '.$this->condition['delete'];
        }

        if (array_key_exists('from', $this->condition)) {
            $query .= ' FROM '.$this->condition['from'];
        }

        if (array_key_exists('where', $this->condition)) {
            $wheres = [];
            foreach($this->condition['where'] as $row) {
                if ($row[2] !== null) {
                    $wheres[] = sprintf(' %s %s %s', $row[0], $row[1], $this->getQuoteValue($row[2], $con));
                    continue;
                }
                // [IS NULL] or [IS NOT NULL]
                if ($row[1] == '=') {
                    $wheres[] = sprintf(' %s IS NULL', $row[0]);
                } else {
                    $wheres[] = sprintf(' %s IS NOT NULL', $row[0]);
                }
            }
            $query .= ' WHERE ' . implode(' AND ', $wheres);

        }

        if (array_key_exists('order_by', $this->condition)) {
            $query .= ' ORDER BY ' . implode(' , ', $this->condition['order_by']);
        }

        if (array_key_exists('limit', $this->condition)) {
            $query .= ' LIMIT ' . $this->condition['limit'];
        }

        $this->condition = [];

        return $query;

    }

    private function getQuoteValue($value, $con) {
        if (is_callable($value)) {
            return $value();
        }
        return $con->quote($value);
    }

    private function getTime() {
        list($usec, $sec) = explode( ' ', microtime() ); 
        return (float)$sec + (float)$usec;
    }


    public function getSqlLog() {
        return $this->logs;
    }

}
