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

// ----------------------------------------------------------------------------
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

    public function from($table) {
        return $this->setCondition('from', $table);
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
        return $this->setCondition('limit', $limit);
    }

    public function insert($table) {
        return $this->setCondition('insert', $table);
    }

    public function update($table) {
        return $this->setCondition('update', $table);
    }

    public function delete($table) {
        return $this->setCondition('delete', $table);
    }

    public function values($values) {
        return $this->setCondition('values', $values);
    }

    private function setCondition($key, $value) {
        $this->condition[$key] = $value;
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



// ----------------------------------------------------------------------------
class Cheetan {

    private $template = null;
    private $viewfile = null;
    private $viewpath = null;
    private $viewfile_ext = '.html';
    private $variables = [];
    private $debug = false;

    public $config = [];
    public $db = null;

    // Request Data
    public $method = 'GET';
    public $post = [];
    public $get = [];
    public $request = [];
    public $raw = null;
    public $headers = [];

    public function dispatch() {

        $this->db = new CDatabase();
        $this->loadConfig();
        $this->requestHandle();

        // 呼び出し元のaction関数実行
        if (function_exists('action')) {
            action( $this );
        }

        // 呼び出し元にaction_[method]が存在すれば実行
        $func = 'action_'.strtolower($this->method);
        if (function_exists($func)) {
            $func( $this );
        }

        $this->display();
    }

    private function loadConfig() {
        // cheetan.phpと同じ階層のconfig.php
        // 実行スクリプトと同じ階層のconfig.phpを読み取り設定
        $files = [
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php',
            'config.php',
        ];
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }
            $this->config = array_merge($this->config, require($file));
        }

        $this->setConfig($this->config);
    }

    public function setConfig($config) {
        $this->config = array_merge($this->config, $config);
        foreach(['template', 'viewfile', 'viewpath', 'viewfile_ext'] as $key) {
            if (!array_key_exists($key, $this->config)) {
                continue;
            }
            $this->{$key} = $this->config[$key];
        }

        if ($this->db) {
            $this->db->setConfig($config);
        }
    }
    
    public function getViewFile() {
        if ( $this->viewfile ) {
            return $this->viewfile;
        }
        
        $scriptfile = basename($_SERVER['SCRIPT_FILENAME']);
        list( $title, $ext ) = explode( '.', $scriptfile );
        if ( $this->viewpath ) {
            $path = $this->viewpath;
            switch ( $this->viewpath[strlen($this->viewpath)-1] ) {
                case '/';
                case "\\";
                    $path = $this->viewpath . $title . $this->viewfile_ext;
                break;
                default:
                    $path = $this->viewpath . DIRECTORY_SEPARATOR . $title . $this->viewfile_ext;
                break;
            }

        } else {
            $path = $title . $this->viewfile_ext;
        }
        return $path;
    }

    public function getVariable() {
        return $this->variables;
    }
    
    /**
     * テンプレートに値を設定
     * set('key', 'value', true | false [sanitize option, default true])
     * set(['key' => 'value'], true | false [sanitize option, default true])
    */
    public function set($data, ...$args) {
        $sanitize = true;
        if (is_array($data)) {
            if (count($args)) {
                $sanitize = $args[0];
            }
        } else {
            $data = [$data => $args[0]];
            if (count($args) == 2) {
                $sanitize = $args[1];
            }
        }

        foreach($data as $key => $value) {
            $this->variables[$key] = $this->parseSetValue($value, $sanitize);
        }
    }
    
    private function parseSetValue( $data, $sanitize) {
        if (is_array($data)) {
            $result = [];
            foreach($data as $key => $value) {
                $result[$key] = $this->parseSetValue($value, $sanitize);
            }
            return $result;
        }
        
        if ($sanitize) {
            $data = $this->sanitize($data);
        }
        return $data;
    }

    public function sanitize($data) {
        return htmlspecialchars($data);
    }

    public function sendJson($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($json);
        exit();
    }
    
    public function requestHandle() {
        $this->method = $_SERVER['REQUEST_METHOD'];


        $this->headers = getallheaders();
        $this->raw = file_get_contents('php://input');
        $this->get = $_GET;
        $this->post = $_POST;
        
    }

    // --------------------------------------------------------
    // 表示関連
    public function display() {
        $this->makeSqlLog();
        if ( $this->template ) {
            $this->displayTemplate();
        } else {
            $this->content();
        }
    }
    
    public function content() {
        $viewfile = $this->getViewFile();
        if ( file_exists( $viewfile )) {
            $c = $this;
            extract($this->variables, EXTR_SKIP);
            require_once( $viewfile );
        }
    }
    
    private function displayTemplate() {
        if ( file_exists( $this->template ) ) {
            $c = $this;
            extract($this->variables, EXTR_SKIP);
            require_once( $this->template );

        } else {
            print "Template '$this->template' is not exist.";
        }
    }

    private function makeSqlLog() {
        if ( !$this->getDebug() ) {
            $this->variables['cheetan_sql_log'] = '';
            return;
        }

        $sqllog = $this->db->GetSqlLog();
        $log = '<table class="cheetan_sql_log">';
        $log .= '<tr><th width="60%">SQL</th><th width="10%">ERROR</th><th width="10%">ROWS</th><th width="10%">TIME</th></tr>';
        foreach( $sqllog as $name => $rows ) {
            $log .= sprintf('<tr><td colspan="4"><b>%s</b></td></tr>', $this->sanitize($name));
            foreach( $rows as $row ) {
                $log .= sprintf('<tr><td>%s</td><td>%s</td><td>%d</td><td>%.5f</td></tr>',
                    $this->sanitize($row['query']),
                    $this->sanitize($row['error']),
                    $row['affected_rows'],
                    $row['query_time']
                );
            }
        }
        $log .= '</table>';
        $this->variables['cheetan_sql_log'] = $log;
    }
    
    public function setDebug( $debug ) {
        ini_set('display_errors', $debug);
        $this->debug = $debug;
    }
    
    public function getDebug() {
        return $this->debug;
    }
}


$c = new Cheetan();
$c->dispatch();
extract($c->getVariable());

