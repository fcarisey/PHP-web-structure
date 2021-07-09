<?php

namespace Controller;

class ControllerController{
    protected static $table_name;
    protected static $model_class;
    protected static $database;

    public static function INSERT(array $value){
        self::keyExist(static::$database, \Database::$db_array)->request(\Database::ACTION_INSERT, static::$table_name, null, $value);
    }

    public static function UPDATE(array $set = null, array $where = null){
        self::keyExist(static::$database, \Database::$db_array)->request(\Database::ACTION_UPDATE, static::$table_name, null, null, $set, $where);
    }

    public static function SELECT($select = \Database::SELECT_ALL, $where = null, $limit = null, $order_by = null){
        return static::$model_class::format(self::keyExist(static::$database, \Database::$db_array)->request(\Database::ACTION_SELECT, static::$table_name, $select, null, null, $where, $limit, $order_by));
    }

    public static function DELETE(array $where, $limit = null){
        self::keyExist(static::$database, \Database::$db_array)->request(\Database::ACTION_DELETE, static::$table_name, null, null, null, $where, $limit);
    }

    /** 
     * @return null|mixed Return value if exist or null if not exist
     */
    public static function keyExist($key, array $array){
        return (key_exists($key, $array)) ? $array[$key] : null;
    }
}

?>
