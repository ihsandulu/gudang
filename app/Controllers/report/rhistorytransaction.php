<?php

namespace App\Controllers\report;


use App\Controllers\baseController;

class rhistorytransaction extends baseController
{

    protected $sesi_user;
    public function __construct()
    {
        $sesi_user = new \App\Models\global_m();
        $sesi_user->ceksesi();
    }


    public function index()
    {
        $data["message"]="";
        return view('report/rhistorytransaction_v', $data);
    } 
    
    public function print()
    {
        return view('report/rhistorytransactionprint_v');
    }
    
    
}
