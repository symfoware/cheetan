<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
define( 'DBKIND_MYSQL', '0' );
define( 'DBKIND_PGSQL', '1' );
define( 'DBKIND_TEXTSQL', '2' );


class CDatabase {

    private $config = [];
    private $connection = [];
    private $driver = [];
    private $class = [
        'CDBMysql',
        'CDBPgsql',
        'CDBTextsql',
    ];
    
    
    public function add( $name, $host, $user, $pswd, $db, $kind = 0, $port = 0 ) {
        $config = [];
        $config['host'] = $host;
        $config['user'] = $user;
        $config['pswd'] = $pswd;
        $config['db'] = $db;
        $config['kind'] = $kind;
        $config['port'] = $port;
        $this->config[$name] = $config;
    }
    
    
    public function &GetDriver( $name ) {
        if( empty( $this->config[$name] )) {
            return null;
        }

        if( empty( $this->driver[$name] )) {
            $this->driver[$name] = new $this->class[$this->config[$name]['kind']]();
        }

        if( empty( $this->connection[$name] )) {
            $this->connect( $name );
        }
        
        return $this->driver[$name];
    }
    
    
    public function connect( $name ) {
        $config = $this->config[$name];
        $connect = $this->driver[$name]->connect( $config );

        if( !$connect ) {
            print "Failed connect to $name.<br>";
            return false;
        }
        $this->connection[$name] = $connect;
        return true;
    }
    
    
    public function query( $query, $name = '' ) {
        $driver    =& $this->GetDriver( $name );
        $ret    = $driver->query( $query, $this->connection[$name] );
        if( !$ret )
        {
            print "[DBERR] $query<BR>";
        }
        
        return $ret;
    }
    
    
    public function GetFindQuery( $query, $condition = '', $order = '', $limit = '', $group = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->GetFindQuery( $query, $condition, $order, $limit, $group );
    }
    
    
    public function findquery( $query, $condition = '', $order = '', $limit = '', $group = '', $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->findquery( $this->connection[$name], $query, $condition, $order, $limit, $group );
    }
    
    
    public function findall($table, $condition = null, $order = '', $limit = '', $group = '', $name = '') {
        $driver =& $this->GetDriver( $name );
        return $driver->findall($this->connection[$name], $table, $condition, $order, $limit, $group);
    }
    
    
    public function find( $query, $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->find( $query, $this->connection[$name] );
    }
    
    
    public function count( $query, $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->count( $query, $this->connection[$name] );
    }
    
    
    public function insert( $table, $datas, $name = '' )
    {
        $driver =& $this->GetDriver( $name );
        return $driver->insert( $table, $datas, $this->connection[$name] );
    }
    
    
    public function getcount($table, $condition = null, $limit = '', $name = '') {
        $driver =& $this->GetDriver($name);
        return $driver->getcount($this->connection[$name], $table, $condition, $limit);
    }
    
    
    public function update( $table, $datas, $condition, $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->update( $table, $datas, $condition, $this->connection[$name] );
    }
    
    
    public function del( $table, $condition, $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->del( $table, $condition, $this->connection[$name] );
    }
    
    
    public function CreateCondition( $field, $value, $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->CreateCondition( $field, $value, $this->connection[$name] );
    }
    
    
    public function escape( $str, $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->escape( $str );
    }
    
    
    public function GetLastInsertId( $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->GetLastInsertId();
    }
    
    
    public function GetAffectedRows( $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->GetAffectedRows();
    }
    
    
    public function GetLastError( $name = '' ) {
        $driver =& $this->GetDriver( $name );
        return $driver->GetLastError();
    }
    
    
    public function GetSqlLog() {
        $logs = [];
        foreach( $this->driver as $name => $driver ) {
            $logs[$name] = $driver->GetSqlLog();
        }
        return $logs;
    }
    
    
    public function describe($table, $name = '') {
        $driver =& $this->GetDriver($name);
        return $driver->describe($this->connection[$name], $table);
    }
}
