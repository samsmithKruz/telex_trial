<?php 

use App\Lib\Controller;

class IndexController extends Controller{
    public function index(){
        jsonResponse([
            'message'=>'welcome'
        ]);
    }
}