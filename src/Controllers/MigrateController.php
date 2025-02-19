<?php

use App\Lib\Controller;

class MigrateController extends Controller
{
    public function index()
    {
        $this->model("Order");
        $this->model->initTable();
        
        jsonResponse([
            'message' => 'Table Orders created',
            'status' => 'active'
        ]);
    }
}
