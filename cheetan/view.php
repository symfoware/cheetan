<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CView {

    private $template;
    private $viewfile;
    private $variables;
    private $sanitize;
    private $controller;
    private $debug = false;
    
    
    public function setFile( $template, $viewfile ) {
        $this->template = $template;
        $this->viewfile = $viewfile;
    }
    
    
    public function setVariable( &$variable ) {
        $this->variables = $variable;
    }


    public function setSanitize( &$sanitize ) {
        $this->sanitize = $sanitize;
    }
    
    
    public function setController( &$controller ) {
        $this->controller = $controller;
    }
    
    
    public function setSqlLog( $sqllog ) {
        if( $this->debug ) {
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
    }
    
    
    public function setDebug( $debug ) {
        $this->debug = $debug;
    }
    
    
    public function display() {
        if( $this->template ) {
            $this->_display_template();
        } else {
            $this->content();
        }
    }
    
    
    public function content() {
        if( file_exists( $this->viewfile )) {
            $data = $this->variables;
            $sanitize = $this->sanitize;
            $s = $this->sanitize;
            $controller = $this->controller;
            $c = $this->controller;
            extract($this->variables, EXTR_SKIP);
            require_once( $this->viewfile );
        }
    }
    
    
    private function _display_template() {
        if( file_exists( $this->template ) ) {
            $data        = $this->variables;
            $sanitize    = $this->sanitize;
            $s            = $this->sanitize;
            $controller    = $this->controller;
            $c            = $this->controller;
            extract($this->variables, EXTR_SKIP);
            require_once( $this->template );

        } else {
            print "Template '$this->template' is not exist.";
        }
    }
    
    
}
