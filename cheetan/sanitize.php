<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CSanitize {

    public function html($data) {
        return htmlspecialchars($data);
    }
    
    function post($key) {
        return $this->html($_POST[$key]);
    }

    function postt($key) {
        $data = $this->post($key);
        return trim( $data );
    }

    function posts($key) {
        $data = $this->post($key);
        $_SESSION[$key] = $data;
        return $data;
    }

    function postst($key) {
        $data = $this->postt($key);
        $_SESSION[$key] = $data;
        return $data;
    }
    
    function get($key) {
        return $this->html($_GET[$key]);
    }

    function gett($key) {
        $data = $this->get($key);
        return trim($data);
    }


    function gets($key) {
        $data = $this->get($key);
        $_SESSION[$key] = $data;
        return $data;
    }

    function getst($key) {
        $data = $this->gett($key);
        $_SESSION[$key] = $data;
        return $data;
    }
}
