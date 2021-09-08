<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CController {
    
    private $template = null;
    private $viewfile = null;
    private $viewpath = null;
    private $viewfile_ext = '.html';
    private $variables = [];
    private $db;
    private $sanitize;
    private $validate;

    // Models Array
    private $m = [];
    
    // Components Array
    private $c = [];
    private $post = [];
    private $get = [];
    private $request = [];
    private $data = [];
    private $debug = false;
    
    
    public function addModel( $path, $name = '' ) {
        $cname = basename( $path, '.php' );
        $cname = strtolower( $cname );
        if( !$name ) {
            $name = $cname;
        }
        
        $cname = 'C' . ucfirst( $name );
        if( !file_exists( $path ) ) {
            return false;

        } else {
            require_once( $path );
            $class = new $cname();
            if( !$class->table ) {
                $class->table = $name;
            }
            
            $class->setController( $this );
            $this->m[$name]    = &$class;
            if( empty( $this->{$name} ) ) {
                $this->{$name} = &$this->m[$name];
            }
        }

        return true;
    }
    
    
    public function addComponent( $path, $cname = '', $name = '' ) {

        if( !$cname ) {
            $cname = basename( $path, '.php' );
            $cname = strtolower( $cname );
            if( !$name ) {
                $name = $cname;
            }
            $cname = 'C'.ucfirst( $name );

        } else {
            $name = basename( $path, '.php' );
            $name = strtolower( $name );
        }

        if( !file_exists( $path ) ) {
            print 'Component file $path is not exist.';
            return false;
        } else {
            require_once( $path );
            $class = new $cname();
            $this->c[$name]  = $class;
            if( empty( $this->{$name} ) ) {
                $this->{$name} = &$this->c[$name];            
            }
        }
        return true;
    }
    
    
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
        if( $this->viewfile )
        {
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
    
    
    public function set( $name, $value ) {
        $this->variables[$name] = $value;
    }
    
    
    public function setArray( $datas ) {
        foreach( $datas as $key => $data ) {
            $this->set( $key, $data );
        }
    }


    public function redirect( $url, $is301 = false ) {
        if( $is301 ) {
            header( 'HTTP/1.1 301 Moved Permanently' );
        }
        header( 'Location: ' . $url );
        exit();
    }
    
    
    public function requestHandle() {
        if( count( $_GET ) ) {
            $this->get = $_GET;
        }
        if( count( $_POST ) ) {
            $this->post = $_POST;
        }
        if( count( $_REQUEST ) ) {
            $this->request = $_REQUEST;
        }
        $this->modelItemHandle( $_GET );
        $this->modelItemHandle( $_POST );
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
    
    
    public function &getDatabase() {
        return $this->db;
    }
    
    
    public function setDatabase( &$db ) {
        $this->db = $db;
    }


    public function setSanitize( &$sanitize ) {
        $this->sanitize = $sanitize;
    }


    public function setValidate( &$validate ) {
        $this->validate = $validate;
    }
    
    
    public function setDebug( $debug ) {
        $this->debug = $debug;
    }
    
    
    public function getDebug() {
        return $this->debug;
    }
    
    
    public function getSqlLog() {
        return $this->db->GetSqlLog();
    }
}
