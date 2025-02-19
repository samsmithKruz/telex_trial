<?php

namespace App\Lib;

use Exception;

class Controller
{
    protected $model;
    public function view($view, $data = [])
    {
        // Paths for .php and .html views
        $phpViewPath = __DIR__ . '/../views/' . $view . '.php';
        $htmlViewPath = __DIR__ . '/../views/' . $view . '.html';

        // Check if the .php view file exists
        if (file_exists($phpViewPath)) {
            extract((array)$data);  // Make data variables available to the view
            require_once $phpViewPath;
        }
        // Check if the .html view file exists
        elseif (file_exists($htmlViewPath)) {
            extract((array)$data);  // Make data variables available to the view
            require_once $htmlViewPath;
        }
        // If neither file exists, load the 404 error page
        else {
            $error = "View not found.";
            require_once __DIR__ . '/../views/errors/404.php';
        }
        exit();
    }
    public function model($model)
    {
        require_once __DIR__ . '/../Models/' . $model . '.php';
        $model = "App\\Models\\" . $model;
        $this->model = new $model();
    }
    public function modelForeign($model)
    {
        require_once __DIR__ . '/../Models/' . $model . '.php';
        $model = "App\\Models\\" . $model;
        return new $model();
    }
}
