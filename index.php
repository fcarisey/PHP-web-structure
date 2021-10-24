<?php

use PHPMailer\PHPMailer\SMTP;

require_once("module/phpmailer/vendor/autoload.php");

const DEBUG = false;
const DEBUG_SQL = false;
const DEBUG_MAIL = false;

session_name("SUID");
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => "strict"
]);
session_start();

if (DEBUG)
    ini_set('display_errors', E_ALL);
if (DEBUG_MAIL)
    \Controller\MailController::$smtp_debug = SMTP::DEBUG_SERVER;

require_once("librairie/autoloader.php");

function readDatabaseFile(){
    $file = fopen("librairie/config/database.json", "r");
    $res = fread($file, 4000);
    fclose($file);

    return json_decode(trim($res), true);
}

$databases = readDatabaseFile();

$db_array = [];
foreach($databases as $db){
    $db_array[$db['name']] = new \Database($db['type'], $db['host'], $db['port'], $db['database'], $db['user'], $db['password']);
}

foreach($db_array as $key => $db){
    try{
        \Database::TryPDO($db);
    }catch (PDOException $e){
        $bdd_name = $key;
        require_once("error/BddConnection.php");
        die;
    }
}

\Database::$db_array = $db_array;

\Controller\ViewController::process();

?>
