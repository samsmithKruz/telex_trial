<?php 
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Lib/functions.php';
use Dotenv\Dotenv;
use App\Lib\Helpers;
use App\Lib\Core;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Start the session
session_start();

// Populate the environment for getenv() to work
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}
define("APP", getenv('APP_NAME') ?: "App");
define('DOMAIN', getenv("APP_URL")?:'http://localhost:5500');
$_SESSION[APP] = $_SESSION[APP] ?? new stdClass;


// Set up custom error handling and logging
set_error_handler([Helpers::class, 'customErrorHandler']);
set_exception_handler([Helpers::class, 'customExceptionHandler']);
register_shutdown_function([Helpers::class, 'customShutdownFunction']);
print_r('hi');
exit();
// Initialize the application
$app = new Core;
$app->serve($_SERVER);