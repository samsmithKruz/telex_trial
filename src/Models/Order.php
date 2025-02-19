<?php

namespace App\Models;

use App\Lib\Helpers;
use App\Lib\Model;

class Order extends Model
{
    public function initTable()
    {
        $this->db->query("
        DROP TABLE IF EXISTS orders;
        CREATE TABLE orders (
            order_id INT AUTO_INCREMENT PRIMARY KEY,
            txn_id VARCHAR(255) NOT NULL,
            product_id INT NOT NULL,
            description TEXT,
            amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'completed', 'cancel') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ")->execute();
    }
    public function listOrder()
    {
        return $this->db->query("SELECT * FROM orders")->resultSet();
    }
    public function getDailyOrderSummary()
    {
        return $this->db->query("SELECT * FROM orders  WHERE DATE(created_at) = CURDATE() - INTERVAL 1 DAY")->resultSet();
    }
    public function placeOrder($data)
    {
        $data = (object)$data;
        $this->db->query("
        INSERT INTO orders(txn_id,product_id,description,amount,status)
        values(:txn_id,:product_id,:description,:amount,:status)
        ")
            ->bind(":txn_id", Helpers::safe_data($data->txn_id))
            ->bind(":product_id", Helpers::safe_data($data->product_id))
            ->bind(":description", Helpers::safe_data($data->description))
            ->bind(":amount", Helpers::safe_data($data->amount))
            ->bind(":status", "pending")
            ->execute();
        return $this->db->rowCount() ? $this->db->lastInsertId() : null;
    }
    public function processOrder($order_id)
    {
        $this->db->query("UPDATE orders SET status='completed' WHERE order_id=:order_id")
            ->bind(":order_id", Helpers::safe_data($order_id))
            ->execute();
        return $this->db->rowCount();
    }
    public function cancelOrder($order_id)
    {
        $this->db->query("UPDATE orders SET status='cancel' WHERE order_id=:order_id")
            ->bind(":order_id", Helpers::safe_data($order_id))
            ->execute();
        return $this->db->rowCount();
    }
    public function deleteOrder($order_id)
    {
        $this->db->query("DELETE FROM orders WHERE order_id=:order_id")
            ->bind(":order_id", Helpers::safe_data($order_id))
            ->execute();
        return $this->db->rowCount();
    }
    public function backDateOrder($order_id)
    {
        $this->db->query("UPDATE orders SET created_at=CURDATE() - INTERVAL 1 DAY WHERE order_id=:id")->bind(":id", $order_id)->execute();
        return $this->db->rowCount() ? $this->db->lastInsertId() : null;
    }
}
