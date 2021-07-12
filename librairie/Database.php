<?php

class Database{
    public const WHERE_KEY = null;

    public const ACTION_SELECT = 0;
    public const ACTION_INSERT = 1;
    public const ACTION_DELETE = 2;
    public const ACTION_UPDATE = 3;

    public const ORDER_ASC = "ASC";
    public const ORDER_DESC = "DESC";

    public const TYPE_PGSQL = "pgsql";
    public const TYPE_MYSQL = "mysql";

    public const SELECT_ALL = ['*'];

    private string $database_type = "";
    /*** Default value = "127.0.0.1"*/
    private string $host = "127.0.0.1";
    /*** Default value = 0*/
    private int $port = 0;
    private string $dbname = "";
    private string $user = "";
    private string $password = "";

    public static $db_array = [];

    private $instance = null;

    public function __construct($database_type, $host, $port, $dbname, $user, $password){
        $this->setDatabaseType($database_type)->setHost($host)->setPort($port)->setDBName($dbname)->setUser($user)->setPassword($password);
    }

    public function setDatabaseType($database_type){
        $this->database_type = $database_type;
        return $this;
    }
    public function getDatabaseType(){
        return $this->database_type;
    }

    public function setHost($host){
        $this->host = $host;
        return $this;
    }
    public function getHost(){
        return $this->host;
    }

    public function setPort($port){
        $this->port = $port;
        return $this;
    }
    public function getPort(){
        return $this->port;
    }

    public function setDBName($dbname){
        $this->dbname = $dbname;
        return $this;
    }
    public function getDBName(){
        return $this->dbname;
    }

    public function setUser($user){
        $this->user = $user;
        return $this;
    }
    public function getUser(){
        return $this->user;
    }

    public function setPassword($password){
        $this->password = $password;
        return $this;
    }
    public function getPassword(){
        return $this->password;
    }

    public function getPDO(){
        if ($this->instance == null)
            $this->instance = new \PDO($this->database_type.":host=".$this->host.";port=".$this->port.";dbname=".$this->dbname.";user=".$this->user.";password=".$this->password);
        return $this->instance;
    }
    
    public function request(int $action, string $table, array $select = null, array $value = null, array $set = null, array $where = null, string $limit = null, array $order_by = null){
        if ($this->database_type == self::TYPE_PGSQL)
            $tq = '"';
        else if ($this->database_type == self::TYPE_MYSQL)
            $tq = '`';

        $where_string = null;
        $where_execute = [];
        if ($where != null){
            $where_string = "WHERE";
            foreach ($where as $key => $value) {
                if ($key == "AND")
                    $where_string .= " $key";
                else if ($key == "OR")
                    $where_string .= " $key";
                else{
                    if ($key == "1")
                        $where_string .= " $key = :where_$key";
                    else
                        $where_string .= " $tq$key$tq = :where_$key";
                    $where_execute[":where_$key"] = $value;
                }
            }
        }

        $order_by_string = null;
        if($order_by != null){
            $order_by_string = "ORDER BY";
            foreach($order_by as $key => $v){
                $order_by_string .= " $tq$key$tq $v";
            }
        }

        $select_string = null;
        if ($select != null){
            $select_string = "";
            foreach($select as $key => $v){
                if ($select_string != "")
                    $select_string .= ", ";
                if ($v == "*")
                    $select_string .= "$v";
                else
                    $select_string .= "$tq$v$tq";
            }
        }

        $limit_string = null;
        if ($limit != null){
            $limit_string = "LIMIT $limit";
        }

        $value_string = null;
        $value_want = null;
        $value_execute = [];
        if ($value != null){
            $value_string = "VALUES(";
            $value_want = "(";

            foreach ((array) $value as $key => $v) {
                $value_string .= " :value_$key,";

                $value_execute[":value_$key"] = $v;
                $value_want .= " $tq$key$tq,";
            }

            if ($value_string[strlen($value_string)-1] == ',')
                $value_string[strlen($value_string)-1] = ' ';

            if ($value_want[strlen($value_want)-1] == ',')
                $value_want[strlen($value_want)-1] = ' ';

            $value_want .= ")";
            $value_string .= ")";
        }

        $set_string = null;
        $set_execute = [];
        if ($set != null){
            $set_string = "SET";
            foreach ($set as $key => $value) {
                $set_string .= " $tq$key$tq = :set_$key,";
                $set_execute[":set_$key"] = $value;
            }

            if ($set_string[strlen($set_string)-1] == ',')
                $set_string[strlen($set_string)-1] = ' ';
        }

        if ($action == self::ACTION_SELECT){
            $SQL = "SELECT $select_string FROM $tq$table$tq $where_string $order_by_string $limit_string";
            $execute = $where_execute;
        }else if ($action == self::ACTION_INSERT){
            $SQL = "INSERT INTO $tq$table$tq $value_want $value_string";
            $execute = $value_execute;
        }else if ($action == self::ACTION_DELETE){
            $SQL = "DELETE FROM $tq$table$tq $where_string $limit_string";
            $execute = $where_execute;
        }else if ($action == self::ACTION_UPDATE){
            $SQL = "UPDATE $tq$table$tq $set_string $where_string";
            $execute = $where_execute;
            foreach ($set_execute as $key => $value) {
                $execute[$key] = $value;
            }
        }

        if (DEBUG_SQL)
            var_dump($SQL);

        $req = $this->getPDO()->prepare($SQL);
        if ($req->execute($execute))
            return $req->fetchAll();
        return false;
    }

    public static function TryPDO(self $db){
        return $db->getPDO();
    }
}
