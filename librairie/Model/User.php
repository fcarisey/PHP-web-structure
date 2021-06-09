<?php

namespace Model;

class User{
    private $id, $username, $first_name, $last_name, $mail, $tel, $password, $role;
    public static $password_min_length = 8, $username_max_length = 12;

    public function __construct($id = null, $username = null, $first_name = null, $last_name = null, $mail = null, $tel = null, $password = null, $role = null){
        $this->setId($id)
             ->setUsername($username)
             ->setFirstName($first_name)
             ->setLastName($last_name)
             ->setMail($mail)
             ->setTel($tel)
             ->setPassword($password)
             ->setRole($role);
    }

    public function setId($id){
        $this->id = $id;
        return $this;
    }
    public function getId(){
        return $this->id;
    }

    public function setUsername($username){
        $this->username = $username;
        return $this;
    }
    public function getUsername(){
        return $this->username;
    }

    public function setFirstName($first_name){
        $this->first_name = $first_name;
        return $this;
    }
    public function getFirstName(){
        return $this->first_name;
    }

    public function setLastName($last_name){
        $this->last_name = $last_name;
        return $this;
    }
    public function getLastName(){
        return $this->last_name;
    }

    public function setMail($mail){
        $this->mail = $mail;
        return $this;
    }
    public function getMail(){
        return $this->mail;
    }

    public function setTel($tel){
        $this->tel = $tel;
        return $this;
    }
    public function getTel(){
        return $this->tel;
    }

    public function setPassword($password){
        $this->password = $password;
        return $this;
    }
    public function getPassword(){
        return $this->password;
    }

    public function setRole($role){
        $this->role = $role;
        return $this;
    }
    public function getRole(){
        return $this->role;
    }

    public static function format($data){
        $objs = [];

        foreach ($data as $d) {
            $id = \Controller\ControllerController::keyExist('id', $d);
            $username = \Controller\ControllerController::keyExist('username', $d);
            $first_name = \Controller\ControllerController::keyExist('first_name', $d);
            $last_name = \Controller\ControllerController::keyExist('last_name', $d);
            $mail = \Controller\ControllerController::keyExist('mail', $d);
            $tel = \Controller\ControllerController::keyExist('tel', $d);
            $password = \Controller\ControllerController::keyExist('password', $d);
            $role = \Controller\ControllerController::keyExist('role', $d);

            $user = new self($id, $username, $first_name, $last_name, $mail, $tel, $password, $role);
            array_push($objs, $user);
        }

        return ($objs == []) ? null : $objs;
    }
}

?>
