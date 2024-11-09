<?php

namespace App\Models\master;

use App\Models\core_m;

class mdivision_m extends core_m
{
    public function data()
    {
        $data = array();
        $data["message"] = "";
        //cek division
        if ($this->request->getVar("division_id")) {
            $divisiond["division_id"] = $this->request->getVar("division_id");
        } else {
            $divisiond["division_id"] = -1;
        }
        $us = $this->db
            ->table("division")
            ->getWhere($divisiond);
        /* echo $this->db->getLastquery();
        die; */
        $larang = array("log_id", "id", "user_id", "action", "data", "division_id_dep", "trx_id", "trx_code");
        if ($us->getNumRows() > 0) {
            foreach ($us->getResult() as $division) {
                foreach ($this->db->getFieldNames('division') as $field) {
                    if (!in_array($field, $larang)) {
                        $data[$field] = $division->$field;
                    }
                }
            }
        } else {
            foreach ($this->db->getFieldNames('division') as $field) {
                $data[$field] = "";
            }
        }

        

        //delete
        if ($this->request->getPost("delete") == "OK") {  
            $division_id=   $this->request->getPost("division_id");
            $cek=$this->db->table("user")
            ->where("division_id", $division_id) 
            ->get()
            ->getNumRows();
            if($cek>0){
                $data["message"] = "Division masih dipakai di data user!";
            } else{            
                $this->db
                ->table("division")
                ->delete(array("division_id" => $this->request->getPost("division_id")));
                $data["message"] = "Delete Success";
            }
        }

        //insert
        if ($this->request->getPost("create") == "OK") {
            foreach ($this->request->getPost() as $e => $f) {
                if ($e != 'create' && $e != 'division_id') {
                    $input[$e] = $this->request->getPost($e);
                }
            }
            $builder = $this->db->table('division');
            $builder->insert($input);
            /* echo $this->db->getLastQuery();
            die; */
            $division_id = $this->db->insertID();

            $data["message"] = "Insert Data Success";
        }
        //echo $_POST["create"];die;
        
        //update
        if ($this->request->getPost("change") == "OK") {
            foreach ($this->request->getPost() as $e => $f) {
                if ($e != 'change' && $e != 'division_picture') {
                    $input[$e] = $this->request->getPost($e);
                }
            }
            $this->db->table('division')->update($input, array("division_id" => $this->request->getPost("division_id")));
            $data["message"] = "Update Success";
            //echo $this->db->last_query();die;
        }
        return $data;
    }
}
