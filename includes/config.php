<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'obe_db');
define('SITE_URL', 'http://localhost/obe-php');
define('SITE_NAME', 'OBE System, Tezpur University');

// IMPORTANT: Please generate a long random string for production use!
define('JWT_SECRET_KEY', 'ChangeThisToAStrongRandomSecretKey1234567890!');

date_default_timezone_set('UTC');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
?>
