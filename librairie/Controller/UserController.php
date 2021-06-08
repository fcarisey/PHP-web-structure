<?php

namespace Controller;

class UserController extends ControllerController{
    protected static $table_name = \Database::$tables['user'];
    protected static $model_class = \Model\User::class;

    public static function login($mail = null, $password = null){
        if ($mail == null)
            $mail = \Controller\ControllerController::keyExist('mail', $_POST);

        if ($password == null)
            $password = \Controller\ControllerController::keyExist('password', $_POST);

        $err = null;
        $mail_parse = false;
        if (!empty($mail)){
            if (filter_var($mail, FILTER_VALIDATE_EMAIL)){
                $mail_parse = true;
            }else
                $err['mail'] = "L'adresse mail n'est pas valide exemple: john@doe@exemple.com";
        }else
            $err['mail'] = "Ce champ est obligatoire !";
        
        $password_parse = false;
        if (!empty($password)){
            if ($password >= \Model\User::$password_min_length){
                $password_parse = true;     
            }else
                $err['password'] = "Le mot de passe doit contenit au moins ".\Model\User::$password_min_length." caractères !";
        }else
            $err['password'] = "Ce champ est obligatoire !";
                

        if ($mail_parse && $password_parse){
            $user_mail = self::SELECT(\Database::SELECT_ALL, ['mail' => $mail], 1);
            if ($user_mail != null){
                $user = $user_mail[0];
                if (password_verify($password, $user->getPassword())){
                    $_SESSION['id'] = $user->getId();
                    $_SESSION['username'] = $user->getUsername();
                    $_SESSION['first_name'] = $user->getFirstName();
                    $_SESSION['last_name'] = $user->getLastName();
                    $_SESSION['mail'] = $user->getMail();
                    $_SESSION['tel'] = $user->getTel();
                    $_SESSION['password'] = $user->getPassword();
        
                    return true;
                }else
                    $err['error'] = "L'utilisateur n'existe pas !";
            }else
                $err['error'] = "L'utilisateur n'existe pas !";
        }
        return $err;
    }
}

?>
