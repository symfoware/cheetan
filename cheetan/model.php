<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CModel {

    private $id = 'id';
    private $name = '';
    private $table = '';
    private $db;
    private $controler;
    private $validatefunc = [];
    private $validatemsg = [];
    private $validateresult = [];
        
    
    public function SetController( &$controller ) {
        $this->controller = &$controller;
        $this->db = &$controller->db;
    }
    
    
    public function SetDatabase( &$db ) {
        $this->db = $db;
    }
    
    
    public function query($query) {
        return $this->db->query($query, $this->name);
    }
    
    
    public function findquery( $query, $condition = '', $order = '', $limit = '', $group = '' ) {
        return $this->db->findquery( $query, $condition, $order, $limit, $group, $this->name );
    }


    public function find($condition = null, $order = '', $limit = '', $group = '') {
        return $this->db->findall($this->table, $condition, $order, $limit, $group, $this->name);
    }
    
    
    public function findone($condition = null, $order = '') {
        $result = $this->find($condition, $order, 1);
        if(count($result)) {
            return $result[0];
        }
        return FALSE;
    }
    
    
    public function findby( $field, $value, $order = '', $limit = '' ) {
        $condition = $this->db->CreateCondition( $field, $value, $this->name );
        return $this->find( $condition, $order, $limit );
    }
    
    
    public function findoneby( $field, $value, $order = '' ) {
        $condition = $this->db->CreateCondition( $field, $value, $this->name );
        return $this->findone( $condition, $order );
    }
    
    
    public function getcount($condition = null, $limit = '') {
        return $this->db->getcount($this->table, $condition, $limit, $this->name);
    }


    public function insert( $datas ) {
        return $this->db->insert( $this->table, $datas, $this->name );
    }


    public function updateby( $datas, $condition ) {
        return $this->db->update( $this->table, $datas, $condition, $this->name );
    }
    
    
    public function update( $datas ) {
        if( array_key_exists( $this->id, $datas )) {
            $copy = array_slice( $datas, 0 );
            unset( $copy[$this->id] );
            $condition = $this->db->CreateCondition( $this->id, $datas[$this->id], $this->name );
            return $this->updateby( $datas, $condition );
        }
        
        return FALSE;
    }


    public function del($condition) {
        return $this->db->del($this->table, $condition, $this->name);
    }
    
    
    public function validate($data) {
        $ret = TRUE;
        $validater = &$this->controller->validate;
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->validatefunc)) {
                $funcs = $this->validatefunc[$key];
                $errors = $this->validatemsg[$key];
                if (!is_array($funcs)) {
                    $funcs = array($funcs);
                }
                if (!is_array($errors)) {
                    $errors = array($errors);
                }

                foreach ($funcs as $i => $func) {
                    if (method_exists($validater, $func)) {
                        if (!$validater->$func($value)) {
                            if (array_key_exists($key, $this->validatemsg) && empty($this->validateresult[$key])) {
                                $this->validateresult[$key] = $errors[$i];
                            }
                            $ret = FALSE;
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    
    public function validatemsg($data) {
        $ret = '';
        $validater = &$this->controller->validate;
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->validatefunc)) {
                $funcs = $this->validatefunc[$key];
                $errors = $this->validatemsg[$key];
                if (!is_array($funcs)) {
                    $funcs = array($funcs);
                }
                if (!is_array($errors)) {
                    $errors = array($errors);
                }

                foreach ($funcs as $i => $func) {
                    if (method_exists($validater, $func)) {
                        if (!$validater->$func($value)) {
                            if (array_key_exists($key, $this->validatemsg) && empty($this->validateresult[$key])) {
                                $this->validateresult[$key] = $errors[$i];
                                $ret .= $errors[$i];
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    
    public function GetValidateError() {
        return $this->validateresult;
    }
    
    
    public function to_datetime( $time = '' ) {
        if( !$time ) {
            $time = time();
        }
        return date( 'Y-m-d H:i:s', $time );
    }
    
    
    public function escape( $str ) {
        return $this->db->escape( $str, $this->name );
    }
    
    
    public function GetLastInsertId() {
        return $this->db->GetLastInsertId( $this->name );
    }
    
    
    public function GetAffectedRows() {
        return $this->db->GetAffectedRows( $this->name );
    }
    
    
    public function GetLastError() {
        return $this->db->GetLastError( $this->name );
    }
    
    
    public function describe() {
        return $this->db->describe($this->table, $this->name);
    }
}
