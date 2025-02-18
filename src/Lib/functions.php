<?php

if (!function_exists('assets')) {
    function asset($path)
    {
        // Adjust the base URL if necessary
        return '/public/' . ltrim($path, '/');
    }
}



if (!function_exists('url')) {
    /**
     * Generate the URL for a given path relative to the base URL.
     *
     * @param string $path
     * @return string
     */
    function url($path = '')
    {
        // Get the base URL from the .env or configuration file
        $baseUrl = rtrim(getenv('APP_URL') ?: 'http://localhost', '/');

        // Ensure the path starts with a single slash
        $path = ltrim($path, '/');

        // Return the concatenated base URL and path
        return $baseUrl . '/' . $path;
    }
}


function base_url($path = '')
{
    return 'http://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($path, '/');
}
function current_url()
{
    return "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
}
function redirect($url)
{
    header('Location: ' . base_url($url));
    exit();
}
function back($default = "/")
{
    header("location:" . ($_SERVER['HTTP_REFERER'] ?? $default));
    exit();
}
function sanitize($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return strtolower(trim($text, '-'));
}
function d($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}
function dd($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    exit;
}
function old($key, $default = null)
{
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function validate_csrf($token)
{
    return hash_equals($_SESSION['csrf_token'], $token);
}
function flashMessage($data)
{
    $_SESSION[APP]->flashMessage = (object)$data;
}

function access($roles)
{
    return in_array($_SESSION[APP]->user->role, $roles);
}
function jsonResponse($data, $statusCode = 200)
{
    header('content-type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}
