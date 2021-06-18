<?php

namespace Controller;

class UserController extends ControllerController{
    protected static $table_name = DATABASE_TABLE['user'];
    protected static $model_class = \Model\User::class;

    public static function login($mail = null, $password = null){
        if ($mail == null)
            $mail = \Controller\ControllerController::keyExist('mail', $_POST);

        if ($password == null)
            $password = \Controller\ControllerController::keyExist('password', $_POST);

        $err = null;

        // Mail
        $mail_parse = false;
        if (!empty($mail)){
            if (filter_var($mail, FILTER_VALIDATE_EMAIL)){
                $mail_parse = true;
            }else
                $err['mail'] = "L'adresse mail n'est pas valide exemple: john@doe@exemple.com";
        }else
            $err['mail'] = "Ce champ est obligatoire !";
        
        // Password
        $password_parse = false;
        if (!empty($password)){
            if (strlen($password) >= \Model\User::$password_min_length){
                $password_parse = true;     
            }else
                $err['password'] = "Le mot de passe doit contenit au moins ".\Model\User::$password_min_length." caractères !";
        }else
            $err['password'] = "Ce champ est obligatoire !";
                

        if ($mail_parse && $password_parse){
            $user_mail = self::SELECT(\Database::SELECT_ALL, ['mail' => $mail], 1);
            if ($user_mail != null){
                $user = $user_mail[0];
                if (password_verify($password, '$2y$10$'.$user->getPassword())){
                    $_SESSION['id'] = $user->getId();
                    $_SESSION['username'] = $user->getUsername();
                    $_SESSION['first_name'] = $user->getFirstName();
                    $_SESSION['last_name'] = $user->getLastName();
                    $_SESSION['mail'] = $user->getMail();
                    $_SESSION['tel'] = $user->getTel();
                    $_SESSION['password'] = $user->getPassword();
                    $_SESSION['role'] = $user->getRole();
        
                    return true;
                }else
                    $err['error'] = "L'utilisateur n'existe pas !";
            }else
                $err['error'] = "L'utilisateur n'existe pas !";
        }
        return $err;
    }

    public static function register($username = null, $first_name = null, $last_name = null, $mail = null, $tel = null, $password = null, $conf_password = null){
        if ($username == null)
            $username = \Controller\ControllerController::keyExist('username', $_POST);
        
        if ($first_name == null)
            $first_name = \Controller\ControllerController::keyExist('first_name', $_POST);
        
        if ($last_name == null)
            $last_name = \Controller\ControllerController::keyExist('last_name', $_POST);
            
        if ($mail == null)
            $mail = \Controller\ControllerController::keyExist('mail', $_POST);
            
        if ($tel == null)
            $tel = \Controller\ControllerController::keyExist('tel', $_POST);
        
        if ($password == null)
            $password = \Controller\ControllerController::keyExist('password', $_POST);

        if ($conf_password == null)
            $conf_password = \Controller\ControllerController::keyExist('conf_password', $_POST);
        
        $err = null;

        // Username
        $username_set = false;
        $username_parse = true;
        if ($username !== null){
            $username_set = true;
            $username_parse = false;
            if (!empty($username)){
                if (strlen($username) <= \Model\User::$username_max_length){
                    $username_parse = true;
                }else
                    $err['username'] = "Le nom d'utilisateur doit contenir au maximum ".\Model\User::$username_max_length." caractères !";
            }else
                $err['username'] = "Le nom d'utilisateur est obligatoire !";
        }

        // First name
        $first_name_set = false;
        $first_name_parse = true;
        if ($first_name !== null){
            $first_name_set = true;
            $first_name_parse = false;
            if (!empty($first_name)){
                $first_name_parse = true;
            }else
                $err['first_name'] = "Le prénom est obligatoire !";
        }

        // Last name
        $last_name_set = false;
        $last_name_parse = true;
        if ($last_name !== null){
            $last_name_set = true;
            $last_name_parse = false;
            if (!empty($last_name)){
                $last_name_parse = true;
            }else
                $err['last_name'] = "Le prénom est obligatoire !";
        }

        // Mail
        $mail_set = false;
        $mail_parse = true;
        if ($mail !== null){
            $mail_set = true;
            $mail_parse = false;
            if (!empty($mail)){
                if (filter_var($mail, FILTER_VALIDATE_EMAIL)){
                    if (\Controller\UserController::SELECT(['id'], ['mail'=>$mail], 1) == null)
                        $mail_parse = true;
                    else
                        $err['mail'] = "Cette adresse mail existe déjà !";
                }else
                    $err['mail'] = "L'adresse mail n'est pas valide exemple: john@doe@exemple.com";
            }else
                $err['mail'] = "Ce champ est obligatoire !";
        }

        // Tel
        $tel_set = false;
        $tel_parse = true;
        if ($tel !== null){
            $tel_set = true;
            $tel_parse = false;
            if (!empty($tel)){
                $tel_parse = true;
            }else
                $err['tel'] = "Le téléphone est obligatoire !";
        }

        // Password
        $password_set = false;
        $password_parse = true;
        if ($password !== null){
            $password_set = true;
            $password_parse = false;
            if (!empty($password)){
                if (strlen($password) >= \Model\User::$password_min_length){
                    $password_parse = true;
                }else
                    $err['password'] = "Le mot de passe doit contenit au moins ".\Model\User::$password_min_length." caractères !";
            }else
                $err['password'] = "Ce champ est obligatoire !";
        }

        // Conf password
        if ($conf_password !== null){
            $conf_password_parse = false;
            if (!empty($conf_password)){
                if (strlen($conf_password) >= \Model\User::$password_min_length){
                    if ($conf_password == $password)
                        $conf_password_parse = true;
                    else
                        $err['conf_password'] = "Les mots de passe doivent être identique !";
                }else
                    $err['conf_password'] = "Le mot de passe doit contenit au moins ".\Model\User::$password_min_length." caractères !";
            }else
                $err['conf_password'] = "Ce champ est obligatoire !";
        }

        if ($username_parse && $first_name_parse && $last_name_parse && $mail_parse && $tel_parse && $password_parse && $conf_password_parse && $err === null){
            $value = ['role' => "customer"];
            if ($username_set)
                $value['username'] = $username;
            if ($first_name_set)
                $value['first_name'] = $first_name;
            if ($last_name_set)
                $value['last_name'] = $last_name;
            if ($mail_set)
                $value['mail'] = $mail;
            if ($tel_set)
                $value['tel'] = $tel;
            if ($password_set)
                $value['password'] = str_replace('$2y$10$', '', password_hash($password, PASSWORD_DEFAULT));
            
            self::INSERT($value);
            self::login($mail, $password);

            return true;
        }

        return $err;
    }

    public static function logout(){
        session_destroy();
        \Controller\ViewController::redirect("/");
    }
}

?>
