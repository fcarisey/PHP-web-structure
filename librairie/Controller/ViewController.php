<?php

namespace Controller;

class ViewController{
    public static function process(){
        $page = (isset($_GET['page'])) ? $_GET['page'] : 'home';
        self::userPermission($page);

        if (!isset($_POST['ajax']))
            require_once("librairie/View/template/header.php");

        switch($page){
            case 'home': require_once("librairie/View/home.php");break;
        }

        if (!isset($_POST['ajax']))
            require_once("librairie/View/template/footer.php");
    }

    public static function userPermission($page){
        $basic = ['home'];
        
        $allow = false;
        if (!in_array($page, $basic)){
            if (isset($_SESSION['id']));
        }else
            $allow = true;

        if ($allow)
            return true;
        else{
            require_once("error/401.html");
            die;
        }
    }

    public static function redirect($location){
        header("Location: $location", true);
        die;
    }
}

?>
