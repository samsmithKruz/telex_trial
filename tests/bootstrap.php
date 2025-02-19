<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Lib/functions.php';


use Dotenv\Dotenv;

$rootPath = dirname(__DIR__); // Move one level up
$dotenv = Dotenv::createImmutable($rootPath);

// Debugging: Check if .env is found
if (!file_exists($rootPath . '/.env')) {
    fwrite(STDERR, "⚠️  .env file not found at: {$rootPath}/.env\n");
}

$dotenv->load();
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}