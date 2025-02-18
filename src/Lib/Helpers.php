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
        $logPath = __DIR__ . getenv('LOG_PATH') ?: '/logs.log';

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
    public static function csrf_request(): void
    {
        if (!(self::has('csrf')) || !validate_csrf(self::get('csrf') ?? "")) {
            dd("Invalid Request (Possible CSRF Error detected)");
        }
    }
    public static function isLoggedIn()
    {
        if (!isset($_SESSION[APP]->user)) {
            session_destroy();
            redirect("login");
        }
    }
    public static function Auth($role)
    {
        self::isLoggedIn();
        if (!in_array($_SESSION[APP]->user->role, $role)) {
            flashMessage(['state' => false, 'message' => "You are not authorized to view this page", 'type' => "error"]);
            back("/");
        }
    }
    public static function uploadthumbnail()
    {
        // Allowed file extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (strlen($_FILES['thumbnail']['full_path']) > 0) {

            // Step 2: Handle the file upload
            $uploadDir = __DIR__ . "/../public/uploads/";
            $fileTmpPath = $_FILES['thumbnail']['tmp_name'];
            $fileOriginalName = $_FILES['thumbnail']['name'];
            $fileExtension = strtolower(pathinfo($fileOriginalName, PATHINFO_EXTENSION));

            // Check if the file extension is allowed
            if (!in_array($fileExtension, $allowedExtensions)) {
                return [
                    'state' => false,
                    'message' => "Error: File type not allowed.",
                ];
            }

            $newFileName =  bin2hex(random_bytes(4)) . date('Y_m_d_is') . '.' . $fileExtension;
            $dest_path =  $uploadDir . $newFileName;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }


            // Check file size
            if ($_FILES['thumbnail']["size"] > getenv('UPLOAD_FILE_SIZE') * 1024 * 1024) {
                return [
                    'state' => false,
                    'message' => "Sorry, your file is too large.",
                ];
            }



            // Move the file to the upload directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                return [
                    "state" => true,
                    "filename" => $newFileName
                ];
            }
            return [
                'state' => false,
                'message' => "There was an error moving the uploaded file.",
            ];
        }
        return [
            'state' => false,
            'message' => "No file found.",
        ];
    }
    public static function uploadFiles($name)
    {
        $file = $_FILES[$name];
        error_reporting(E_ALL);
        $uploaded = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $uploadDir = __DIR__ . "/../public/uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }


        if (is_array($file['name'])) {
            // Loop over the files and process them
            for ($i = count($file['name']) - 1; $i >= 0; $i--) {
                // Check if the file has been uploaded
                if (strlen($file['full_path'][$i]) > 0) {

                    // Step 2: Handle the file upload
                    $fileTmpPath = $file['tmp_name'][$i];
                    $fileOriginalName = $file['name'][$i];
                    $fileExtension = strtolower(pathinfo($fileOriginalName, PATHINFO_EXTENSION));

                    // Check if the file extension is allowed
                    if (!in_array($fileExtension, $allowedExtensions)) {
                        return [
                            'state' => false,
                            'message' => "Error: File type not allowed.",
                        ];
                    }

                    $newFileName =  bin2hex(random_bytes(4)) . date('Y_m_d_is') . '.' . $fileExtension;
                    $dest_path = $uploadDir . $newFileName;

                    // Check file size
                    if ($file["size"][$i] > getenv('UPLOAD_FILE_SIZE') * 1024 * 1024) {
                        return [
                            'state' => false,
                            'message' => "Sorry, your file is too large.",
                        ];
                    }

                    // Move the file to the upload directory
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $uploaded[] = $newFileName;
                    } else {
                        return [
                            'state' => false,
                            'message' => "There was an error moving the uploaded file.",
                        ];
                    }
                }
            }
        } else {
            // Check if the file has been uploaded
            if (strlen($file['full_path']) > 0) {

                // Step 2: Handle the file upload
                $fileTmpPath = $file['tmp_name'];
                $fileOriginalName = $file['name'];
                $fileExtension = strtolower(pathinfo($fileOriginalName, PATHINFO_EXTENSION));

                // Check if the file extension is allowed
                if (!in_array($fileExtension, $allowedExtensions)) {
                    return [
                        'state' => false,
                        'message' => "Error: File type not allowed.",
                    ];
                }

                $newFileName =  bin2hex(random_bytes(4)) . date('Y_m_d_is') . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;

                // Check file size
                if ($file["size"] > getenv('UPLOAD_FILE_SIZE') * 1024 * 1024) {
                    return [
                        'state' => false,
                        'message' => "Sorry, your file is too large.",
                    ];
                }

                // Move the file to the upload directory
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $uploaded = $newFileName;
                } else {
                    return [
                        'state' => false,
                        'message' => "There was an error moving the uploaded file.",
                    ];
                }
            }
        }

        // Return result after processing all files
        if (empty($uploaded)) {
            return [
                'state' => false,
                'message' => "No file found.",
            ];
        }

        return [
            'state' => true,
            'message' => "Files uploaded successfully.",
            'filenames' => $uploaded
        ];
    }
    public static function deleteFiles($files)
    {
        // Define the upload directory
        $uploadDir = __DIR__ . "/../public/uploads/";

        // Check if the directory exists
        if (!is_dir($uploadDir)) {
            return [
                'state' => false,
                'message' => "Upload directory does not exist.",
            ];
        }
        // Initialize an array to keep track of files that were successfully deleted
        $deleted = [];

        // Check if $files is an array, if not, make it an array
        if (!is_array($files)) {
            $files = [$files];
        }
        // Loop through each file in the array and attempt to delete it
        foreach ($files as $file) {
            // Construct the full file path
            $filePath = $uploadDir . $file;

            // Check if the file exists
            if (file_exists($filePath)) {
                // Attempt to delete the file
                if (unlink($filePath)) {
                    $deleted[] = $file; // If successful, add to deleted array
                } else {
                    return [
                        'state' => false,
                        'message' => "Error: Could not delete file '$file'.",
                    ];
                }
            } else {
                return [
                    'state' => false,
                    'message' => "Error: File '$file' does not exist.",
                ];
            }
        }
        // If no files were deleted, return an error
        if (empty($deleted)) {
            return [
                'state' => false,
                'message' => "No files were deleted.",
            ];
        }

        // If files were successfully deleted
        return [
            'state' => true,
            'message' => "Files deleted successfully.",
            'deleted' => $deleted, // Return the names of the deleted files
        ];
    }
}
