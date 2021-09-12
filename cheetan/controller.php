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

    public $db = null;

    // Request Data
    public $method = 'GET';
    public $post = [];
    public $get = [];
    public $request = [];
    public $data = [];
    public $json = [];
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
        if( $this->viewfile ) {
            return $this->viewfile;
        }
        
        $pos = strpos( SCRIPTFILE, '.' );
        if( $pos === false ) {
            return SCRIPTFILE . $this->viewfile_ext;
        }
        if( !$pos ) {
            return $this->viewfile_ext;
        }
        
        list( $title, $ext ) = explode( '.', SCRIPTFILE );
        if( $this->viewpath ) {
            $path    = $this->viewpath;
            switch( $this->viewpath[strlen($this->viewpath)-1] ) {
            case '/';
            case "\\";
                $path    = $this->viewpath . $title . $this->viewfile_ext;
                break;
            default:
                $path    = $this->viewpath . DIRECTORY_SEPARATOR . $title . $this->viewfile_ext;
                break;
            }

        } else {
            $path    = $title . $this->viewfile_ext;
        }
        return $path;
    }
    
    
    public function set( $name, $value, $sanitize=true) {
        if ($sanitize) {
            $value = $this->sanitize($value);
        }
        $this->variables[$name] = $value;
    }
    
    
    public function setArray( $datas, $sanitize=true) {
        foreach( $datas as $key => $data ) {
            $this->set( $key, $data, $sanitize );
        }
    }

    public function sanitize($data) {
        return htmlspecialchars($data);
    }


    public function redirect( $url, $is301 = false ) {
        if( $is301 ) {
            header( 'HTTP/1.1 301 Moved Permanently' );
        }
        header( 'Location: ' . $url );
        exit();
    }
    
    
    public function requestHandle() {

        $this->headers = getallheaders();
        $this->raw = file_get_contents('php://input');
        $this->method = $_SERVER['REQUEST_METHOD'];

        if( count( $_GET ) ) {
            $this->get = $_GET;
        }
        if( count( $_POST ) ) {
            $this->post = $_POST;
        }
        if( count( $_REQUEST ) ) {
            $this->request = $_REQUEST;
        }
        $this->modelItemHandle( $this->request );
        
        if (isset($this->headers['']) && ($this->headers[''] === '')) {
            // application/json
            $this->json = json_decode($this->raw, true);
        }
        
    }
    
    
    public function modelItemHandle( $requests ) {
        foreach( $requests as $key => $request ) {
            if( strpos( $key, '/' ) !== false ) {
                list( $model, $element ) = explode( '/', $key );
                $this->data[$model][$element] = $request;
            }
        }
    }
    
    
    public function getVariable() {
        return $this->variables;
    }
    
    
    public function getDatabase() {
        return $this->db;
    }
    
    public function setDatabase( &$db ) {
        $this->db = $db;
    }

    // --------------------------------------------------------
    // 表示関連
    public function display() {
        $this->setSqlLog();
        if( $this->template ) {
            $this->displayTemplate();
        } else {
            $this->content();
        }
    }
    
    public function content() {
        $viewfile = $this->getViewFile();
        if( file_exists( $viewfile )) {
            $c = $this;
            extract($this->variables, EXTR_SKIP);
            require_once( $viewfile );
        }
    }
    
    private function displayTemplate() {
        if( file_exists( $this->template ) ) {
            $c = $this;
            extract($this->variables, EXTR_SKIP);
            require_once( $this->template );

        } else {
            print "Template '$this->template' is not exist.";
        }
    }

    private function setSqlLog() {
        if( !$this->getDebug() ) {
            $this->variables['cheetan_sql_log'] = '';
            return;
        }

        $sqllog = $this->db->GetSqlLog();

        $log    = '<table class="cheetan_sql_log">'
                . '<tr>'
                . '<th width="60%">SQL</th>'
                . '<th width="10%">ERROR</th>'
                . '<th width="10%">ROWS</th>'
                . '<th width="10%">TIME</th>'
                . '</tr>'
                ;
        foreach( $sqllog as $name => $rows ) {
            $log    .= '<tr>'
                    . '<td colspan="4"><b>' . htmlspecialchars( $name ) . '</b></td>'
                    . '</tr>'
                    ;
            foreach( $rows as $i => $row ) {
                $log    .= '<tr>'
                        . '<td>' . htmlspecialchars( $row['query'] ) . '</td>'
                        . '<td>' . htmlspecialchars( $row['error'] ) . '</td>'
                        . '<td>' . $row['affected_rows'] . '</td>'
                        . '<td>' . sprintf( '%.5f', $row['query_time'] ) . '</td>'
                        . '</tr>'
                        ;
            }
        }
        $log    .= '</table>';
        $this->variables['cheetan_sql_log'] = $log;
    }
    
    public function setDebug( $debug ) {
        if ($debug) {
            ini_set('display_errors', 'on');
        } else {
            ini_set('display_errors', 'off');
        }
        $this->debug = $debug;
    }
    
    public function getDebug() {
        return $this->debug;
    }
    
    public function getSqlLog() {
        return $this->db->GetSqlLog();
    }
}
