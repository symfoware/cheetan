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
        
    
    public function setController( &$controller ) {
        $this->controller = &$controller;
        $this->db = &$controller->db;
    }
    
    
    public function setDatabase( &$db ) {
        $this->db = $db;
    }
    
    
    public function query($query) {
        return $this->db->query($query, $this->name);
    }
    
    
    public function findQuery( $query, $condition = '', $order = '', $limit = '', $group = '' ) {
        return $this->db->findQuery( $query, $condition, $order, $limit, $group, $this->name );
    }


    public function find($condition = null, $order = '', $limit = '', $group = '') {
        return $this->db->findAll($this->table, $condition, $order, $limit, $group, $this->name);
    }
    
    
    public function findOne($condition = null, $order = '') {
        $result = $this->find($condition, $order, 1);
        if(count($result)) {
            return $result[0];
        }
        return false;
    }
    
    
    public function findBy( $field, $value, $order = '', $limit = '' ) {
        $condition = $this->db->createCondition( $field, $value, $this->name );
        return $this->find( $condition, $order, $limit );
    }
    
    
    public function findOneBy( $field, $value, $order = '' ) {
        $condition = $this->db->createCondition( $field, $value, $this->name );
        return $this->findOne( $condition, $order );
    }
    
    
    public function getCount($condition = null, $limit = '') {
        return $this->db->getCount($this->table, $condition, $limit, $this->name);
    }


    public function insert( $datas ) {
        return $this->db->insert( $this->table, $datas, $this->name );
    }


    public function updateBy( $datas, $condition ) {
        return $this->db->update( $this->table, $datas, $condition, $this->name );
    }
    
    
    public function update( $datas ) {
        if( array_key_exists( $this->id, $datas )) {
            $copy = array_slice( $datas, 0 );
            unset( $copy[$this->id] );
            $condition = $this->db->createCondition( $this->id, $datas[$this->id], $this->name );
            return $this->updateBy( $datas, $condition );
        }
        
        return false;
    }


    public function del($condition) {
        return $this->db->del($this->table, $condition, $this->name);
    }
    
    
    public function validate($data) {
        $ret = true;
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
                            $ret = false;
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
    
    
    public function getValidateError() {
        return $this->validateresult;
    }
    
    
    public function toDateTime( $time = '' ) {
        if( !$time ) {
            $time = time();
        }
        return date( 'Y-m-d H:i:s', $time );
    }
    
    
    public function escape( $str ) {
        return $this->db->escape( $str, $this->name );
    }
    
    
    public function getLastInsertId() {
        return $this->db->getLastInsertId( $this->name );
    }
    
    
    public function getAffectedRows() {
        return $this->db->getAffectedRows( $this->name );
    }
    
    
    public function getLastError() {
        return $this->db->getLastError( $this->name );
    }
    
    
    public function describe() {
        return $this->db->describe($this->table, $this->name);
    }
}
