<?php

namespace App\Lib;

use Exception;


class Helpers
{
    public static function customErrorHandler($errno, $errstr, $errfile, $errline)
    {
        $errorType = match ($errno) {
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED',
            default => 'UNKNOWN ERROR',
        };

        $logMessage = "[$errorType] $errstr in $errfile on line $errline";

        // Log the error
        self::errorLogger($logMessage);
        jsonResponse(["errorMsg" => "$errstr in $errfile on line $errline", "errorHead" => "$errorType"], 500);
    }
    public static function customExceptionHandler($exception)
    {
        $logMessage = '[EXCEPTION] ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();

        // Log the exception
        self::errorLogger($logMessage);

        if (getenv('APP_DEBUG') === 'true') {
            // Show exception details if in debug mode
            echo "<b>Exception:</b> " . $exception->getMessage() . "<br>";
            echo "in <b>" . $exception->getFile() . "</b> on line <b>" . $exception->getLine() . "</b><br>";
        } else {
            // Handle non-debug environment (e.g., show a generic error message)
            echo self::view("errors/error", [
                "errorMsg" => $exception->getMessage() . "in <b>" . $exception->getFile() . "</b> on line <b>" . $exception->getLine() . "</b><br>",
                "errorHead" => "Exception"
            ]);
        }
    }

    public static function customShutdownFunction()
    {
        $error = error_get_last();
        if ($error !== null) {
            $logMessage = '[SHUTDOWN] ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'];

            // Log the fatal error
            self::errorLogger($logMessage);

            if (getenv('APP_DEBUG') === 'true') {
                echo "<b>Fatal Error:</b> " . $error['message'] . " in <b>" . $error['file'] . "</b> on line <b>" . $error['line'] . "</b><br>";
            } else {
                echo self::view("errors/error", [
                    "errorMsg" => $error['message'] . " in <b>" . $error['file'] . "</b> on line <b>" . $error['line'] . "</b><br>",
                    "errorHead" => "Tatal Error:"
                ]);
                // echo "A critical error occurred. Please try again later.";
            }
        }
    }

    public static function errorLogger($message)
    {
        $logPath = __DIR__ ."/". (getenv('LOG_PATH') ?: '/logs.log');

        if (!is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0777, true);
        }

        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] $message" . PHP_EOL;

        file_put_contents($logPath, $logMessage, FILE_APPEND);
    }
    public static function view($view, $data = [])
    {
        require_once __DIR__ . "/./Controller.php";
        $controller = new Controller;

        return $controller->view($view, $data);
    }
    public static function safe_data($data)
    {
        $sanitize = function ($value) {
            return addslashes(htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8'));
        };
        if (is_array($data)) {
            return array_map($sanitize, $data);
        }
        return $sanitize($data);
    }



    // Updated for Request Class
    public static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    public static function isMethod(string $method): bool
    {
        return strtolower(self::getMethod()) === strtolower($method);
    }
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    public static function getInput(): array
    {
        $input = [];
        if (self::isMethod('POST')) {
            $input = $_POST;
        } elseif (self::isMethod('GET')) {
            $input = $_GET;
        } elseif (in_array(self::getMethod(), ['PUT', 'DELETE', 'PATCH'])) {
            parse_str(file_get_contents('php://input'), $input);
        }
        return $input;
    }
    public static function get(string $key, $default = null)
    {
        $input = self::getInput();
        return self::safe_data($input[$key]) ?? $default;
    }
    public static function has(string $key): bool
    {
        $input = self::getInput();
        return isset($input[$key]);
    }
}
