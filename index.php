<?php

use PHPMailer\PHPMailer\SMTP;

require_once("module/phpmailer/vendor/autoload.php");

const DEBUG = true;
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

require_once("librairie/autoloader.php");

\Database::$database_type = \Database::TYPE_PGSQL;
\Database::$port = 5432;
\Database::$dbname = "test";
\Database::$user = "administrateur";
\Database::$password = "6bub94z4";
\Database::$tables = [
    "table" => "table"
];

if (DEBUG_MAIL)
    \Controller\MailController::$smtp_debug = SMTP::DEBUG_SERVER;

\Controller\ViewController::process();

?>
