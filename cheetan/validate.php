<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CValidate {

    public function notempty( $data, $errmsg = "" ) {
        return $this->_check( ( $data !== '' ), $errmsg );
    }


    public function len( $data, $min, $max, $errmsg = "" ) {
        $len    = strlen( $data );
        $ret    = ( $min <= $len && $len <= $max ) ? 1 : 0;
        return $this->_check( $ret, $errmsg );
    }


    public function number( $data, $errmsg = "" ) {
        return $this->_check( is_numeric( $data ), $errmsg );
    }
    
    
    public function eisu( $data, $errmsg = "" ) {
        return $this->_check( preg_match("/^[0-9a-zA-Z]+$/",$data), $errmsg );
    }


    public function email( $data, $errmsg = "" ) {
        return $this->_check( preg_match( '/\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+]*)@([a-z0-9][a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,4}))$)\\z/i', $data ), $errmsg );
    }

    
    private function _check( $b, $errmsg ) {
        if( $b ) {
            if( $errmsg ) {
                return "";
            } else {
                return TRUE;
            }

        } else {
            if( $errmsg ) {
                return $errmsg;
            } else {
                return FALSE;
            }
        }
    }
}
