<?php

namespace App\Models\transaction;

use App\Models\core_m;

class petugas_m extends core_m
{
    public function data()
    {
        $data = array();
        $data["message"] = "";
        //cek petugas
        if ($this->request->getVar("petugas_id")) {
            $petugasd["petugas_id"] = $this->request->getVar("petugas_id");
        } else {
            $petugasd["petugas_id"] = -1;
        }
        $us = $this->db
            ->table("petugas")
            ->getWhere($petugasd);
        /* echo $this->db->getLastquery();
        die; */
        $larang = array("log_id", "id", "user_id", "action", "data", "petugas_id_dep", "trx_id", "trx_code");
        if ($us->getNumRows() > 0) {
            foreach ($us->getResult() as $petugas) {
                foreach ($this->db->getFieldNames('petugas') as $field) {
                    if (!in_array($field, $larang)) {
                        $data[$field] = $petugas->$field;
                    }
                }
            }
        } else {
            foreach ($this->db->getFieldNames('petugas') as $field) {
                $data[$field] = "";
            }
            $data["petugas_ppn"] = "0";
            $data["petugas_date"] = date("Y-m-d");
        }

        

        //delete
        if ($this->request->getPost("delete") == "OK") {  
            $petugas_id=   $this->request->getPost("petugas_id");
            $cek=$this->db->table("petugasd")
            ->where("petugas_id", $petugas_id) 
            ->get()
            ->getNumRows();
            if($cek>0){
                $data["message"] = "Masih terdapat data detail petugas!";
            } else{            
                $this->db
                ->table("petugas")
                ->delete(array("petugas_id" => $this->request->getPost("petugas_id")));
                $data["message"] = "Delete Success";
            }
        }

        //insert
        if ($this->request->getPost("create") == "OK") {
            foreach ($this->request->getPost() as $e => $f) {
                if ($e != 'create' && $e != 'petugas_id') {
                    $input[$e] = $this->request->getPost($e);
                }
            }
            $builder = $this->db->table('petugas');
            $builder->insert($input);
            /* echo $this->db->getLastQuery();
            die; */
            $petugas_id = $this->db->insertID();
            $data["message"] = "Insert Data Success";
           
        }
        //echo $_POST["create"];die;
        
        //update
        if ($this->request->getPost("change") == "OK") {
            foreach ($this->request->getPost() as $e => $f) {
                if ($e != 'change' && $e != 'petugas_picture') {
                    $input[$e] = $this->request->getPost($e);
                }
            }
            $this->db->table('petugas')->update($input, array("petugas_id" => $this->request->getPost("petugas_id")));
            $data["message"] = "Update Success";
            //echo $this->db->last_query();die;            
        }
        return $data;
    }
}
