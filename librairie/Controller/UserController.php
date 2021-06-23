<?php
    namespace Controller;
    Class UserController extends ControllerController{
        protected static $table_name = "user";
        protected static $model_class = \Model\User::class;
        protected static $database = "db1";
    }