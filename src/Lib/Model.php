<?php 
namespace App\Lib;

class Model extends Database{
    protected $db;
    public function __construct(){
        $this->db = new Database();
    }
}