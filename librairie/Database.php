<?php

class Database{
    public const WHERE_KEY = null;

    public const ACTION_SELECT = 0;
    public const ACTION_INSERT = 1;
    public const ACTION_DELETE = 2;
    public const ACTION_UPDATE = 3;

    public const TYPE_PGSQL = "pgsql";
    public const TYPE_MYSQL = "mysql";

    public const SELECT_ALL = ['*'];

    public static string $database_type = "";
    /*** Default value = "127.0.0.1"*/
    public static string $host = "127.0.0.1";
    /*** Default value = 0*/
    public static int $port = 0;
    public static string $dbname = "";
    public static string $user = "";
    public static string $password = "";

    static function getPDO(){
        static $instance = null;
        if ($instance == null)
            $instance = new \PDO(static::$database_type.":host=".static::$host.";port=".static::$port.";dbname=".static::$dbname.";user=".static::$user.";password=".static::$password);
        return $instance;
    }
    
    static function request(int $action, string $table, array $select = null, array $value = null, array $set = null, array $where = null, string $limit = null, array $order_by = null){
        if (static::$database_type == self::TYPE_PGSQL)
            $tq = '"';
        else if (static::$database_type == self::TYPE_MYSQL)
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

        if (DEBUG_SQL){
            var_dump($SQL);
        }

        $req = self::getPDO()->prepare($SQL);
        
        if ($req->execute($execute)){
            return $req->fetchAll();
        }
        return false;
    }
}
