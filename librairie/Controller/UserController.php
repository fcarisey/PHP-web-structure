<?php

namespace Controller;

class UserController extends ControllerController{
    protected static $table_name = \Database::$tables['user'];
    protected static $model_class = \Model\User::class;
}

?>
