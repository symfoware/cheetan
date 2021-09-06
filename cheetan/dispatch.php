<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CDispatch {

    public function dispatch() {

        $db = new CDatabase();
        if( function_exists( 'config_database' ) ) {
            config_database( $db );
        }
    
        $sanitize = new CSanitize();
        $validate = new CValidate();
        $controller = new CController();

        $controller->RequestHandle();
        $controller->SetDatabase( $db );
        $controller->SetSanitize( $sanitize );
        $controller->SetValidate( $validate );

        if( !function_exists( 'is_session' ) || is_session() ) {
            session_start();
        }

        if( function_exists( 'action' ) ) {
            action( $controller );
        }
        
        $template = $controller->GetTemplateFile();
        $viewfile = $controller->GetViewFile();
        $variable = $controller->GetVariable();
        $sqllog = $controller->GetSqlLog();
        $is_debug = $controller->GetDebug();
        
        $view = new CView();
        $view->SetFile( $template, $viewfile );
        $view->SetVariable( $variable );
        $view->SetSanitize( $sanitize );
        $view->SetController( $controller );
        $view->SetDebug( $is_debug );
        $view->SetSqlLog( $sqllog );
        $view->display();

        return $controller;
    }
}
