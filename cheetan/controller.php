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
class CController {
    
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
    
    public function setTemplateFile( $template ) {
        $this->template = $template;
    }
    
    public function setViewFile( $viewfile ) {
        $this->viewfile = $viewfile;
    }
    
    public function setViewPath( $viewpath ) {
        $this->viewpath = $viewpath;
    }
    
    public function setViewExt( $ext ) {
        if( $ext[0] != '.' ) {
            $ext = '.' . $ext;
        }
        $this->viewfile_ext = $ext;
    }
    
    public function getTemplateFile() {
        return $this->template;
    }
    
    public function getViewFile() {
        if ( $this->viewfile ) {
            return $this->viewfile;
        }
        
        list( $title, $ext ) = explode( '.', SCRIPTFILE );
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

    public function setConfig($config) {
        $this->config = array_merge($this->config, $config);
        if ($this->db) {
            $this->db->setConfig($config);
        }
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

    public function redirect( $url, $is301 = false ) {
        if ( $is301 ) {
            header( 'HTTP/1.1 301 Moved Permanently' );
        }
        header( 'Location: ' . $url );
        exit();
    }
    
    public function requestHandle() {
        $this->headers = getallheaders();
        $this->raw = file_get_contents('php://input');
        $this->method = $_SERVER['REQUEST_METHOD'];

        $this->get = $_GET;
        $this->post = $_POST;
        $this->request = $_REQUEST;
    }

    // --------------------------------------------------------
    // 表示関連
    public function display() {
        $this->setSqlLog();
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

    public function sendJson($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($json);
        exit();
    }

    private function setSqlLog() {
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
