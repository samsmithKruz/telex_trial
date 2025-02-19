<?php 
error_reporting(E_ALL); // Report all errors and warnings
ini_set('display_errors', 1); // Display errors on the screen
ini_set('display_startup_errors', 1); // Show startup errors

require_once __DIR__ . '/vendor/autoload.php';
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

require_once __DIR__ . '/src/Lib/functions.php';

// Set up custom error handling and logging
// set_error_handler([Helpers::class, 'customErrorHandler']);
// set_exception_handler([Helpers::class, 'customExceptionHandler']);
// register_shutdown_function([Helpers::class, 'customShutdownFunction']);

// Initialize the application
$app = new Core;
$app->serve($_SERVER);