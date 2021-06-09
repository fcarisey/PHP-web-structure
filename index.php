<?php

use PHPMailer\PHPMailer\SMTP;

require_once("module/phpmailer/vendor/autoload.php");

const DEBUG = true;
const DEBUG_SQL = false;
const DEBUG_MAIL = false;

if (DEBUG)
    ini_set('display_errors', E_ALL);
if (DEBUG_MAIL)
    \Controller\MailController::$smtp_debug = SMTP::DEBUG_SERVER;

require_once("librairie/autoloader.php");

\Database::$database_type = \Database::TYPE_MYSQL;
\Database::$port = 3306;
\Database::$dbname = "test";
\Database::$user = "root";
\Database::$password = "";
const DATABASE_TABLE = [
    'user' => "user"
];

session_name("SUID");
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => "strict"
]);
session_start();

\Controller\ViewController::process();

?>
