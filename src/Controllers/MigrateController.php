<?php

use App\Lib\Controller;
use App\Lib\Database;
use App\Lib\Helpers;

class MigrateController extends Controller
{
    private $db;
    public function index()
    {
        $db = new Database();
        // DROP TABLE IF EXISTS users;
        // CREATE TABLE users (
        //     user_id INT AUTO_INCREMENT PRIMARY KEY,
        //     username VARCHAR(255) NOT NULL,
        //     email VARCHAR(255) NOT NULL,
        //     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        // );
        $db->query("
        DROP TABLE IF EXISTS orders;

        CREATE TABLE orders (
            order_id INT AUTO_INCREMENT PRIMARY KEY,
            txn_id VARCHAR(255) NOT NULL,
            product_id INT NOT NULL,
            description TEXT,
            amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'completed', 'cancel') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        );
        ");
        jsonResponse([
            'message' => 'Table Orders created',
            'status' => 'active'
        ]);
    }
    public function seed($table = "")
    {
        if (!Helpers::isMethod("POST")) {
            jsonResponse([
                'message' => "This route requires POST method to add seed",
                'status' => 'error'
            ], 405);
        }
        $data = get_data();
        if (empty($table) || count($data) == 0) {
            jsonResponse([
                'message' => "This request needs a complete params for table(Orders), data([txn_id,product_id,description,amount,status,user_id])",
                'status' => 'error'
            ], 422);
        }
        $db = new Database();

        $allowedTables = [
            // 'users' => ['username', 'email'],
            'orders' => ['txn_id', 'product_id', 'description', 'amount', 'user_id']
        ];

        $table = strtolower($table);
        if (!array_key_exists($table, $allowedTables)) {
            jsonResponse([
            'message' => "Invalid table name provided",
            'status' => 'error'
            ], 422);
        }

        $columns = $allowedTables[$table];
        $placeholders = implode(',', array_map(fn($col) => ":$col", $columns));
        $db->query("INSERT INTO $table(" . implode(',', $columns) . ") VALUES($placeholders)");

        foreach ($columns as $column) {
            if (!isset($data[$column])) {
            jsonResponse([
                'message' => ["{$column} is required"],
                'status' => 'error'
            ], 422);
            }
            $db->bind(":{$column}", $data[$column]);
        }

        $db->execute();
        return $db->rowCount() ? $db->lastInsertId() : null;
    }
}
