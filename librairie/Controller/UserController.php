<?php
    namespace Controller;
    Class UserController extends ControllerController{
        protected static $table_name = DATABASE_TABLE['user'];
        protected static $model_class = \Model\User::class;
    }