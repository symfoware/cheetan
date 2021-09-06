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
    private $s;
    private $validate;
    private $v;

    // Models Array
    private $m = [];
    
    // Components Array
    private $c = [];
    private $post = [];
    private $get = [];
    private $request = [];
    private $data = [];
    private $debug = false;
    
    
    public function AddModel( $path, $name = '' ) {
        $cname = basename( $path, '.php' );
        $cname = strtolower( $cname );
        if( !$name ) {
            $name = $cname;
        }
        
        $cname = 'C' . ucfirst( $name );
        if( !file_exists( $path ) ) {
            return FALSE;

        } else {
            require_once( $path );
            $class = new $cname();
            if( !$class->table ) {
                $class->table = $name;
            }
            
            $class->SetController( $this );
            $this->m[$name]    = &$class;
            if( empty( $this->{$name} ) ) {
                $this->{$name} = &$this->m[$name];
            }
        }

        return TRUE;
    }
    
    
    public function AddComponent( $path, $cname = '', $name = '' ) {

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
            return FALSE;
        } else {
            require_once( $path );
            $class = new $cname();
            $this->c[$name]  = $class;
            if( empty( $this->{$name} ) ) {
                $this->{$name} = &$this->c[$name];            
            }
        }
        return TRUE;
    }
    
    
    public function SetTemplateFile( $template ) {
        $this->template = $template;
    }
    
    
    public function SetViewFile( $viewfile ) {
        $this->viewfile = $viewfile;
    }
    
    
    public function SetViewPath( $viewpath ) {
        $this->viewpath = $viewpath;
    }
    
    
    public function SetViewExt( $ext ) {
        if( $ext{0} != '.' ) {
            $ext = '.' . $ext;
        }
        $this->viewfile_ext = $ext;
    }
    
    
    public function GetTemplateFile() {
        return $this->template;
    }
    
    
    public function GetViewFile() {
        if( $this->viewfile )
        {
            return $this->viewfile;
        }
        
        $pos = strpos( SCRIPTFILE, '.' );
        if( $pos === FALSE ) {
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
    
    
    public function setarray( $datas ) {
        foreach( $datas as $key => $data ) {
            $this->set( $key, $data );
        }
    }


    public function redirect( $url, $is301 = FALSE ) {
        if( $is301 ) {
            header( 'HTTP/1.1 301 Moved Permanently' );
        }
        header( 'Location: ' . $url );
        exit();
    }
    
    
    public function RequestHandle() {
        if( count( $_GET ) ) {
            $this->get = $_GET;
        }
        if( count( $_POST ) ) {
            $this->post = $_POST;
        }
        if( count( $_REQUEST ) ) {
            $this->request = $_REQUEST;
        }
        $this->ModelItemHandle( $_GET );
        $this->ModelItemHandle( $_POST );
    }
    
    
    public function ModelItemHandle( $requests ) {
        foreach( $requests as $key => $request ) {
            if( strpos( $key, '/' ) !== FALSE ) {
                list( $model, $element ) = explode( '/', $key );
                $this->data[$model][$element] = $request;
            }
        }
    }
    
    
    public function GetVariable() {
        return $this->variables;
    }
    
    
    public function &GetDatabase() {
        return $this->db;
    }
    
    
    public function SetDatabase( &$db ) {
        $this->db = $db;
    }


    public function SetSanitize( &$sanitize ) {
        $this->sanitize = $sanitize;
        $this->s = &$this->sanitize;
    }


    public function SetValidate( &$validate ) {
        $this->validate = $validate;
        $this->v = &$this->validate;
    }
    
    
    public function SetDebug( $debug ) {
        $this->debug = $debug;
    }
    
    
    public function GetDebug() {
        return $this->debug;
    }
    
    
    public function GetSqlLog() {
        return $this->db->GetSqlLog();
    }
}
