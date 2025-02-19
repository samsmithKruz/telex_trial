<?php

namespace App\Models;

use App\Lib\Helpers;
use App\Lib\Model;

class Order extends Model
{
    public function listOrder()
    {
        $this->db->query("SELECT * FROM Orders")->resultSet();
    }
    public function getDailyOrderSummary()
    {
        $this->db->query("SELECT * FROM Orders  WHERE DATE(created_at) = CURDATE() - INTERVAL 1 DAY")->resultSet();
    }
    public function placeOrder($data)
    {
        $data = (object)$data;
        $this->db->query("
        INSERT INTO orders(txn_id,product_id,description,amount,status)
        values(:txn_id,product_id,:description,:amount,:status)
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
}
