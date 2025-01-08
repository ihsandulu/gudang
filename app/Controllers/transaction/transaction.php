<?php

namespace App\Controllers\transaction;


use App\Controllers\baseController;

class transaction extends baseController
{

    protected $sesi_user;
    public function __construct()
    {
        $sesi_user = new \App\Models\global_m();
        $sesi_user->ceksesi();
    }


    public function index()
    {
        $data = new \App\Models\transaction\transaction_m();
        $data = $data->data();
        return view('transaction/transaction_v', $data);
    }


    public function gudang()
    {
        $data = new \App\Models\transaction\gudang_m();
        $data = $data->data();
        return view('transaction/gudang_v', $data);
    }
    public function gudangmasuk()
    {
        $data = new \App\Models\transaction\gudang_m();
        $data = $data->data();
        return view('transaction/gudangmasuk_v', $data);
    }

    public function print()
    {

        $store = $this->db
            ->table("store")
            ->get();
        if ($store->getNumRows() > 0) {
            foreach ($store->getResult() as $store) {
                foreach ($this->db->getFieldNames('store') as $field) {
                    $data[$field] = $store->$field;
                }
            }
        } else {
            foreach ($this->db->getFieldNames('store') as $field) {
                $data[$field] = "";
            }
        }
        return view('transaction/transactionprint_v', $data);
    }
    public function printumum()
    {

        $store = $this->db
            ->table("store")
            ->get();
        if ($store->getNumRows() > 0) {
            foreach ($store->getResult() as $store) {
                foreach ($this->db->getFieldNames('store') as $field) {
                    $data[$field] = $store->$field;
                }
            }
        } else {
            foreach ($this->db->getFieldNames('store') as $field) {
                $data[$field] = "";
            }
        }
        $petugas = $this->db
            ->table("petugas")
            ->select("user.user_name as petugas_name,user.user_nip as petugas_nip, user.user_pangkat as petugas_pangkat, position.position_name as petugas_jabatan")
            ->join("user", "user.user_id=petugas.user_id", "left")
            ->join("position", "position.position_id=user.position_id", "left")
            ->orderBy("petugas_id", "DESC")
            ->limit(1)
            ->get();
        // echo $this->db->getLastquery();die;
        if ($petugas->getNumRows() > 0) {
            foreach ($petugas->getResult() as $petugas) {
                $data["petugas_name"] = $petugas->petugas_name;
                $data["petugas_nip"] = $petugas->petugas_nip;
                $data["petugas_pangkat"] = $petugas->petugas_pangkat;
                $data["petugas_jabatan"] = $petugas->petugas_jabatan;
            }
        } else {
            $data["petugas_name"] = "";
            $data["petugas_nip"] = "";
            $data["petugas_pangkat"] = "";
            $data["petugas_jabatan"] = "";
        }
        return view('transaction/transactionprintumum_v', $data);
    }

    public function posisishift()
    {
        $builder = $this->db->table("store")
            // ->where("store_id",session()->get("store_id"))
            ->orderBy("store_id", "DESC")
            ->limit(1)
            ->get();
        $kas_type = 'keluar'; //masuk,keluar
        foreach ($builder->getResult() as $kas) {
            $kas_type = $kas->store_posisishift;
            if ($kas_type == "") {
                $kas_type = 'keluar';
            }
        }
        echo $kas_type;
    }

    public function shift()
    {
        $builder = $this->db->table("store")
            // ->where("store_id",session()->get("store_id"))
            ->orderBy("store_id", "DESC")
            ->limit(1)
            ->get();
        $store_shift = 1; //masuk,keluar
        $store_date = date("Y-m-d");
        foreach ($builder->getResult() as $shift) {
            $store_shift = $shift->store_shift;
            $store_date = $shift->store_date;
            $store_date = $shift->store_date;
        }

        //perhitungan shift
        if ($store_shift == '' || $store_date != date("Y-m-d")) {
            $store_shift = 1;
        } else {
            $store_shift = $store_shift;
        }
        echo $store_shift;
    }

    public function kasmodal()
    {
        $data["message"] = "";
        $kas_nominal = $this->request->getGet("kas_nominal");
        $kas_typeinput = $this->request->getGet("kas_type"); //inputan
        $store_id = $this->request->getGet("store_id");
        $kas_shiftinput = $this->request->getGet("kas_shift");

        //cek kas realtime
        $storekas = $this->db->table("store")
            ->select("*,COUNT(store.store_id) AS count, modal.kas_id AS kas_id, store.modal_id AS modal_id")
            ->join("modal", "modal.modal_id=store.modal_id")
            ->join("kas", "kas.kas_id=modal.kas_id")
            ->where("store.store_id", $store_id)
            ->orderBy("store.store_id", "DESC")
            ->limit(1);
        $store = $storekas->get();
        $countstorekas = $store->getRow()->count;
        $data["message"] = $this->db->getLastQuery();

        $kas_lasttype = 'keluar'; //masuk,keluar

        if ($countstorekas > 0) {
            foreach ($store->getResult() as $kas) {
                $modalid = $kas->modal_id; //id modal
                $modalawal = $kas->store_modal; //modal awal
                $kasrealtime = $kas->store_kas; //kas aktual
                $store_shift = $kas->store_shift; //shift ke berapa
                $store_date = $kas->store_date; //tgl shift yg sedang berlaku                

                $kas_lasttype = $kas->store_posisishift; //masuk, keluar

                //cek shift
                if ($kas_lasttype == '' || $store_date != date("Y-m-d")) {
                    $store_shift = 1;
                } elseif ($kas_lasttype == 'keluar' && $kas_typeinput == 'masuk') {
                    $store_shift += 1;
                } else {
                    $store_shift = $store_shift;
                }

                // echo $kas_lasttype."-".$kas_typeinput."-".$store_date."-".date("Y-m-d");

                if (
                    ($kas_lasttype == 'keluar' && $kas_typeinput == 'masuk')
                    || (
                        $kas_lasttype == 'masuk' && $kas_typeinput == 'masuk'
                        && $store_date != date("Y-m-d")
                    )
                ) {
                    //Keluar(Terakhir)-Masuk(Input) atau Masuk(Terakhir)-Masuk(Input) tapi Di Hari yang Sama

                    //insert kas modal awal
                    // $input["store_id"]=session()->get("store_id");
                    $input["kas_modal"] = 1;
                    $input["kas_shift"] = $store_shift;
                    $input["kas_nominal"] = $kas_nominal;
                    $input["kas_type"] = $kas_typeinput;
                    $input["account_id"] = '1';
                    $input["kas_description"] = "Modal Awal";
                    $input["kas_date"] = date("Y-m-d");
                    $builder = $this->db->table("kas")
                        ->insert($input);
                    $kas_id = $this->db->insertID();
                    $lastQuery = $this->db->getLastQuery();
                    $data["message"] = "Store modal awal berhasil.";
                    // $data["message"]="1".$lastQuery;

                    //insert modal
                    $input2["kas_id"] = $kas_id;
                    // $input2["store_id"]=session()->get("store_id");
                    $input2["account_id"] = '1';
                    $input2["modal_shift"] = $store_shift;
                    $input2["modal_nominal"] = $kasrealtime + $kas_nominal;
                    $input2["modal_date"] = date("Y-m-d");
                    $this->db->table("modal")
                        ->insert($input2);
                    $modal_id = $this->db->insertID();

                    //update store
                    $input1["modal_id"] = $modal_id;
                    $input1["store_kas"] = $kasrealtime + $kas_nominal;
                    $input1["store_modal"] = $kasrealtime + $kas_nominal; //nambah dari kas bukan dari modal
                    $input1["store_shift"] = $store_shift;
                    $input1["store_posisishift"] = $kas_typeinput;
                    $input1["store_date"] = date("Y-m-d");
                    $where1["store_id"] = $store_id;
                    $this->db->table("store")
                        ->update($input1, $where1);
                    // $data["message"] = $this->db->getLastQuery();

                } elseif ($kas_lasttype == 'masuk' && $kas_typeinput == 'masuk') {
                    //Masuk(Terakhir)-Masuk(Input) di Hari yang Sama



                    //update kas modal awal
                    $input["kas_nominal"] = $kas_nominal;
                    $input["kas_shift"] = $store_shift;
                    $where["kas_id"] = $kas->kas_id;
                    $where["store_id"] = $store_id;
                    $builder = $this->db->table("kas")
                        ->update($input, $where);
                    $kas_id = $kas->kas_id;
                    $data["message"] = "Modal awal diupdate, ID=" . $kas_id;

                    //update modal
                    $input2["modal_nominal"] = ($modalawal - $kasrealtime) + $kas_nominal;
                    $input2["modal_shift"] = $store_shift;
                    $input2["modal_date"] = date("Y-m-d");
                    $where2["modal_id"] = $modalid;
                    $this->db->table("modal")
                        ->update($input2, $where2);

                    //update store
                    $input1["store_kas"] = ($kasrealtime - $kasrealtime) + $kas_nominal;
                    $input1["store_modal"] = ($modalawal - $kasrealtime) + $kas_nominal;
                    $input1["store_posisishift"] = $kas_typeinput;
                    $where1["store_id"] = $store_id;
                    $this->db->table("store")
                        ->update($input1, $where1);
                } elseif ($kas_lasttype == 'masuk' && $kas_typeinput == 'keluar') {
                    //Masuk(Terakhir)-Keluar(Input)

                    //input kas tariksetoran
                    // $input["store_id"]=session()->get("store_id");
                    $input["kas_tariksetoran"] = 1;
                    $input["kas_nominal"] = $kas_nominal;
                    $input["kas_shift"] = $store_shift;
                    $input["kas_type"] = $kas_typeinput;
                    $input["kas_description"] = "Penarikan Kas";
                    $input["kas_date"] = date("Y-m-d");
                    $input["account_id"] = '15';
                    $builder = $this->db->table("kas")
                        ->insert($input);
                    $kas_id = $this->db->insertID();
                    $data["message"] = "Penarikan kas berhasil.";

                    //update store
                    $kastoko = $kasrealtime - $kas_nominal;
                    $input1["store_kas"] = $kastoko;
                    $input1["store_modal"] = $kastoko;
                    $input1["store_posisishift"] = $kas_typeinput;
                    $where1["store_id"] = $store_id;
                    $this->db->table("store")
                        ->update($input1, $where1);
                } elseif ($kas_lasttype == 'keluar' && $kas_typeinput == 'keluar') {
                    //Masuk(Terakhir)-Keluar(Input) di Hari yang Sama



                    //update kas tarik setoran
                    $input["kas_nominal"] = $kas_nominal;
                    $input["kas_shift"] = $store_shift;
                    $where["kas_id"] = $kas->kas_id;
                    $where["store_id"] = $store_id;
                    $builder = $this->db->table("kas")
                        ->update($input, $where);
                    $kas_id = $kas->kas_id;
                    $data["message"] = "Modal Akhir diupdate, ID=" . $kas_id;

                    // echo $kasrealtime."+".$kasrealtime."-".$kas_nominal;
                    //update store
                    $kastoko = ($kasrealtime + $kasrealtime) - $kas_nominal;
                    $input1["store_kas"] = $kastoko;
                    $input1["store_modal"] = $kastoko;
                    $input1["store_posisishift"] = $kas_typeinput;
                    $where1["store_id"] = $store_id;
                    $this->db->table("store")
                        ->update($input1, $where1);
                }
            }
        } else {
            $store_shift = 1;
            $kasrealtime = 0;
            //insert kas
            // $input["store_id"]=session()->get("store_id");
            $input["kas_modal"] = 1;
            $input["kas_shift"] = $store_shift;
            $input["kas_nominal"] = $kas_nominal;
            $input["kas_type"] = $kas_typeinput;
            $input["kas_description"] = "Modal Awal";
            $input["kas_date"] = date("Y-m-d");
            $input["account_id"] = '1';
            $builder = $this->db->table("kas")
                ->insert($input);
            $kas_id = $this->db->insertID();
            $lastQuery = $this->db->getLastQuery();
            $data["message"] = "Store modal awal berhasil.";
            // $data["message"]="2".$lastQuery;


            //insert modal
            $input2["kas_id"] = $kas_id;
            // $input2["store_id"]=session()->get("store_id");
            $input2["modal_shift"] = $store_shift;
            $input2["modal_nominal"] = $kas_nominal;
            $input2["modal_date"] = date("Y-m-d");
            $input2["account_id"] = '1';
            $this->db->table("modal")
                ->insert($input2);
            $modal_id = $this->db->insertID();

            //update store
            $input1["store_kas"] = $kasrealtime + $kas_nominal;
            $input1["store_modal"] = $kasrealtime + $kas_nominal; //nambah dari kas bukan dari modal
            $input1["store_shift"] = $store_shift;
            $input1["modal_id"] = $modal_id;
            $input1["store_posisishift"] = 'masuk';
            $input1["store_date"] = date("Y-m-d");
            $where1["store_id"] = $store_id;
            $this->db->table("store")
                ->update($input1, $where1);
            // $data["message"] = $this->db->getLastQuery();
        }

        echo $data["message"];
    }

    public function nominalkas()
    {
        $builder = $this->db->table("store")
            // ->where("store_id",session()->get("store_id"))
            ->get();
        $nominal = 0;
        foreach ($builder->getResult() as $store) {
            $nominal = $store->store_kas;
        }
        echo $nominal;
    }

    public function modalawalkas()
    {
        $builder = $this->db->table("store")
            // ->where("store_id",session()->get("store_id"))
            ->get();
        $modalawal = 0;
        foreach ($builder->getResult() as $store) {
            $modalawal = $store->store_modal;
        }
        echo $modalawal;
    }

    public function listnota()
    {
        $builder = $this->db->table("transaction")
            // ->where("store_id",session()->get("store_id"))
            ->where("transaction_status", $this->request->getGet("transaction_status"));
        if (isset($_GET["from"]) && $_GET["from"] != "") {
            $builder->where("transaction.transaction_date >=", $this->request->getGet("from"));
        } else {
            $builder->where("transaction.transaction_date", date("Y-m-d"));
        }

        if (isset($_GET["to"]) && $_GET["to"] != "") {
            $builder->where("transaction.transaction_date <=", $this->request->getGet("to"));
        } else {
            $builder->where("transaction.transaction_date", date("Y-m-d"));
        }
        $listnota = $builder
            ->get();
        foreach ($listnota->getResult() as $listnota) {
?>
            <button onclick="nota(<?= $listnota->transaction_id; ?>);" class="btn btn-outline-secondary mb-2  btn-child" type="button"><small><?= $listnota->transaction_no; ?></small></button>
        <?php
        }
    }

    public function listnotaumum()
    {
        $transaction_type = $this->request->getGet("transaction_type");
        $builder = $this->db->table("transaction")
            ->where("transaction_status", $this->request->getGet("transaction_status"));
        if (isset($_GET["from"]) && $_GET["from"] != "") {
            $builder->where("transaction.transaction_date >=", $this->request->getGet("from"));
        } else {
            $builder->where("transaction.transaction_date", date("Y-m-d"));
        }

        if (isset($_GET["to"]) && $_GET["to"] != "") {
            $builder->where("transaction.transaction_date <=", $this->request->getGet("to"));
        } else {
            $builder->where("transaction.transaction_date", date("Y-m-d"));
        }

        $listnota = $builder
            ->where("transaction.transaction_type", $transaction_type)
            ->get();
        // echo $this->db->getLastQuery();
        foreach ($listnota->getResult() as $listnota) {
        ?>
            <button onclick="nota(<?= $listnota->transaction_id; ?>);" class="btn btn-outline-secondary mb-2  btn-child" type="button"><small><?= $listnota->transaction_no; ?></small></button>
        <?php
        }
    }

    public function createnota()
    {
        $petugas = $this->db->table("petugas")->orderBy("petugas_id", "DESC")->limit(1)->get();
        $cashier_id = 0;
        foreach ($petugas->getResult() as $petugas) {
            $cashier_id = $petugas->user_id;
        }
        $input["cashier_id"] = $cashier_id;
        $input["transaction_date"] = date("Y-m-d");
        $input["transaction_no"] = "POS" . date("YmdHis");
        $input["transaction_status"] = 2;
        // $input["transaction_shift"] = $this->request->getGet("transaction_shift");

        $builder = $this->db->table('transaction');
        $builder->insert($input);
        // $data["message"] = $this->db->getLastQuery();
        $transaction_id = $this->db->insertID();

        $data["message"] = $transaction_id;
        echo $data["message"];
    }

    public function createnotaumum()
    {
        $petugas = $this->db->table("petugas")->orderBy("petugas_id", "DESC")->limit(1)->get();
        $cashier_id = 0;
        foreach ($petugas->getResult() as $petugas) {
            $cashier_id = $petugas->user_id;
        }
        $input["cashier_id"] = $cashier_id;
        $input["transaction_date"] = date("Y-m-d");
        $input["transaction_no"] = "POS" . date("YmdHis");
        $input["transaction_status"] = 2;
        $input["transaction_type"] = $this->request->getGet("transaction_type");
        // $input["transaction_shift"] = $this->request->getGet("transaction_shift");

        $builder = $this->db->table('transaction');
        $builder->insert($input);
        // $data["message"] = $this->db->getLastQuery();
        $transaction_id = $this->db->insertID();

        $data["message"] = $transaction_id;
        echo $data["message"];
    }

    public function insertnota()
    {
        if ($this->request->getGet("positionm_id") > 0) {
            $positionm_id = $this->request->getGet("positionm_id");
        } else {
            $positionm_id = 0;
        }
        $transaction_id = $this->request->getGet("transaction_id");
        $builder = $this->db->table("product")
            ->join("(SELECT product_id AS prodid, positionm_id, sell_price FROM sell)sell", "sell.prodid=product.product_id AND sell.positionm_id=" . $positionm_id, "left");
        if (isset($_GET["product_id"])) {
            $product_id = $this->request->getGet("product_id");
            $product = $builder->where("product_id", $product_id);
        }
        if (isset($_GET["product_batch"])) {
            $product_batch = $this->request->getGet("product_batch");
            $product = $builder->where("product_batch", $product_batch);
        }
        $transactiond_qty = 1;
        if (isset($_GET["transactiond_qty"])) {
            $transactiond_qty = $this->request->getGet("transactiond_qty");
        }
        $pro = $product->get();
        // $sell=$pro->getRow()->product_sell;
        $buy = $pro->getRow()->product_buy;
        $sell = $pro->getRow()->sell_price;

        if ($pro->getNumRows() > 0) {
            $where["transaction_id"] = $transaction_id;
            $where["product_id"] = $pro->getRow()->product_id;
            // $where["store_id"] = session()->get("store_id");

            $cari = $this->db->table('transactiond')
                ->where($where)
                ->get();

            $transactiond = $this->db->table('transactiond');
            if ($cari->getNumRows() > 0) {
                foreach ($cari->getResult() as $cari) {
                    $qty = $cari->transactiond_qty;
                    $price = $cari->transactiond_price;

                    $input["transactiond_qty"] = $qty + $transactiond_qty;
                    $input["transactiond_price"] = $price + ($sell * $qty);
                    $transactiond->update($input, $where);


                    $where1["product_id"] = $pro->getRow()->product_id;
                    $product = $this->db->table('product');
                    $product_stockawal = $product->getWhere($where1)->getRow()->product_stock;
                    $product_stock = $product_stockawal - $transactiond_qty;
                    $input1["product_stock"] = $product_stock;
                    $product->update($input1, $where1);


                    $input2["transactiond_stokawal"] = $product_stockawal;
                    $input2["transactiond_stokakhir"] = $product_stock;
                    $transactiondp = $this->db->table("transactiond");
                    $transactiondp->update($input2, $where);
                }
            } else {
                // $where["store_id"]=session()->get("store_id");
                $where["transactiond_qty"] = $transactiond_qty;
                $where["transactiond_price"] = $sell * $transactiond_qty;
                $transactiond->insert($where);
                $transactiond_id = $this->db->insertID();

                $where1["product_id"] = $pro->getRow()->product_id;
                $product = $this->db->table('product');
                $product_stockawal = $product->getWhere($where1)->getRow()->product_stock;
                $product_stock = $product_stockawal - $transactiond_qty;
                $input1["product_stock"] = $product_stock;
                $product->update($input1, $where1);


                $where2["transactiond_id"] = $transactiond_id;
                $input2["transactiond_stokawal"] = $product_stockawal;
                $input2["transactiond_stokakhir"] = $product_stock;
                $transactiondp = $this->db->table("transactiond");
                $transactiondp->update($input2, $where2);
            }

            // $data["message"] = $this->db->getLastQuery();
            $data["message"] = $transactiond_qty;
        } else {
            $data["message"] = 0;
        }
        echo $data["message"];
    }

    public function insertnotaumum()
    {
        $transaction_type = $this->request->getGet("transaction_type");
        $transaction_id = $this->request->getGet("transaction_id");
        $builder = $this->db->table("product");
        if (isset($_GET["product_id"])) {
            $product_id = $this->request->getGet("product_id");
            $product = $builder->where("product_id", $product_id);
        }
        if (isset($_GET["product_batch"])) {
            $product_batch = $this->request->getGet("product_batch");
            $product = $builder->where("product_batch", $product_batch);
        }
        $transactiond_qty = 1;
        if (isset($_GET["transactiond_qty"])) {
            $transactiond_qty = $this->request->getGet("transactiond_qty");
        }
        $pro = $product->get();
        // echo $this->db->getLastQuery();
        if ($pro->getNumRows() > 0) {
            //cek ketersediaan
            if ($pro->getRow()->product_stock < $transactiond_qty) {
                $data["message"] = -1;
            } else {
                $where["transaction_id"] = $transaction_id;
                $where["product_id"] = $pro->getRow()->product_id;

                $cari = $this->db->table('transactiond')
                    ->where($where)
                    ->get();

                $transactiond = $this->db->table('transactiond');
                if ($cari->getNumRows() > 0) {
                    foreach ($cari->getResult() as $cari) {
                        $qty = $cari->transactiond_qty;

                        $input["transactiond_qty"] = $qty + $transactiond_qty;
                        $input["transactiond_type"] = $transaction_type;
                        $transactiond->update($input, $where);


                        $where1["product_id"] = $pro->getRow()->product_id;
                        $product = $this->db->table('product');
                        $product_stockawal = $product->getWhere($where1)->getRow()->product_stock;
                        if ($transaction_type == "keluar") {
                            $product_stock = $product_stockawal - $transactiond_qty;
                        } else {
                            $product_stock = $product_stockawal + $transactiond_qty;
                        }
                        $input1["product_stock"] = $product_stock;
                        $product->update($input1, $where1);


                        $input2["transactiond_stokawal"] = $product_stockawal;
                        $input2["transactiond_stokakhir"] = $product_stock;
                        $transactiondp = $this->db->table("transactiond");
                        $transactiondp->update($input2, $where);
                    }
                } else {
                    // $where["store_id"]=session()->get("store_id");
                    $where["transactiond_qty"] = $transactiond_qty;
                    $where["transactiond_type"] = $transaction_type;
                    $transactiond->insert($where);
                    $transactiond_id = $this->db->insertID();

                    $where1["product_id"] = $pro->getRow()->product_id;
                    $product = $this->db->table('product');
                    $product_stockawal = $product->getWhere($where1)->getRow()->product_stock;
                    if ($transaction_type == "keluar") {
                        $product_stock = $product_stockawal - $transactiond_qty;
                    } else {
                        $product_stock = $product_stockawal + $transactiond_qty;
                    }
                    $input1["product_stock"] = $product_stock;
                    $product->update($input1, $where1);


                    $where2["transactiond_id"] = $transactiond_id;
                    $input2["transactiond_stokawal"] = $product_stockawal;
                    $input2["transactiond_stokakhir"] = $product_stock;
                    $transactiondp = $this->db->table("transactiond");
                    $transactiondp->update($input2, $where2);
                }

                // $data["message"] = $this->db->getLastQuery();
                $data["message"] = $transactiond_qty;
            }
        } else {
            $data["message"] = 0;
        }
        echo $data["message"];
    }

    public function deletenota()
    {
        $transaction_id = $this->request->getGet("transaction_id");
        $input["transaction_id"] = $transaction_id;

        $builder = $this->db->table('transactiond');
        $transactiond = $builder
            ->select("product_id,SUM(transactiond_qty)AS qty")
            ->where("transaction_id", $transaction_id)
            ->groupBy("product_id")
            ->get();
        foreach ($transactiond->getResult() as $transactiond) {
            $where2["product_id"] = $transactiond->product_id;
            $product = $this->db->table('product');
            $product_stock = $product->getWhere($where2)->getRow()->product_stock;
            $product_stock = $product_stock + $transactiond->qty;
            $input2["product_stock"] = $product_stock;
            $product->update($input2, $where2);
            // echo $this->db->getLastQuery()."<br/>";
        }
        $builder->delete($input);

        $builder = $this->db->table('transaction');
        $builder->delete($input);
        // $data["message"] = $this->db->getLastQuery();

        $data["message"] = $transaction_id;
        echo $data["message"];

        //delete kas
        $input1["transaction_id"] = $transaction_id;
        $builder = $this->db->table("kas")
            ->delete($input1);

        // echo $this->db->getLastQuery();
    }


    public function deletetransactiond()
    {
        $transaction_type = $this->request->getGet("transaction_type");
        $transaction_id = $this->request->getGet("transaction_id");
        $product_id = $this->request->getGet("product_id");
        $product_qty = $this->request->getGet("product_qty");
        $input["transaction_id"] = $transaction_id;
        $input["product_id"] = $product_id;

        $builder = $this->db->table('transactiond');
        $builder->delete($input);
        $data["message"] = $this->db->getLastQuery();

        echo $data["message"];

        $where1["product_id"] = $product_id;
        $product = $this->db->table('product');
        $product_stock = $product->getWhere($where1)->getRow()->product_stock;
        if ($transaction_type == "keluar") {
            $product_stock = $product_stock + $product_qty;
        } else {
            $product_stock = $product_stock - $product_qty;
        }

        $input1["product_stock"] = $product_stock;
        $product->update($input1, $where1);
    }

    public function updateqty()
    {
        if ($this->request->getGet("positionm_id") > 0) {
            $positionm_id = $this->request->getGet("positionm_id");
        } else {
            $positionm_id = 0;
        }
        $transactiond_id = $this->request->getGet("transactiond_id");
        $type = $this->request->getGet("type");
        $transactiond_qty = $this->request->getGet("transactiond_qty");

        $input["transactiond_id"] = $transactiond_id;
        //cek qty
        $transactiond = $this->db->table('transactiond')
            ->join("product", "product.product_id=transactiond.product_id", "left")
            ->join("(SELECT product_id AS prodid, positionm_id, sell_price FROM sell)sell", "sell.prodid=product.product_id AND sell.positionm_id=" . $positionm_id, "left")
            ->where($input)
            ->get();
        foreach ($transactiond->getResult() as $transactiond) {
            // $sell=$transactiond->product_sell*$transactiond_qty;
            $buy = $transactiond->product_buy;
            $sell = $transactiond->sell_price;
            $product_id = $transactiond->product_id;

            $qty = $transactiond->transactiond_qty;
            $price = $transactiond->transactiond_price;
            // $data["message"] = $qty;
            if ($type == "tambah") {
                $qty += 1;
                $price = $price + $sell;
            }
            if ($type == "kurang") {
                $qty -= 1;
                $price = $price - $sell;
            }
            if ($type == "update") {
                $qty = $transactiond_qty;
                $price = $sell * $transactiond_qty;
            }

            // echo $data["message"] =$price."==".$sell;die;

            $input2["transactiond_qty"] = $qty;
            $input2["transactiond_price"] = $price;
            $where2["transactiond_id"] = $transactiond_id;
            $builder = $this->db->table('transactiond');
            $builder->update($input2, $where2);
            $data["message"] = $this->db->getLastQuery();

            // $data["message"] = $transactiond_id;

            $wherep["product_id"] = $product_id;
            $product = $this->db->table('product');
            $product_stockawal = $product->getWhere($wherep)->getRow()->product_stock;
            if ($type == "tambah") {
                $product_stock = $product_stockawal - $transactiond_qty;
            }
            if ($type == "kurang") {
                $product_stock = $product_stockawal + $transactiond_qty;
            }
            if ($type == "update") {
                $product_stock = $product_stockawal + $transactiond->transactiond_qty - $transactiond_qty;
            }
            $inputp["product_stock"] = $product_stock;
            $product->update($inputp, $wherep);
            // $data["message"] = $this->db->getLastQuery();


            $where2["transactiond_id"] = $transactiond_id;
            $input2["transactiond_stokawal"] = $product_stockawal;
            $input2["transactiond_stokakhir"] = $product_stock;
            $transactiond = $this->db->table("transactiond");
            $transactiond->update($input2, $where2);
        }
        echo $data["message"];
    }

    public function pelunasan()
    {
        $user_id = $this->request->getGet("user_id");
        $user_name = $this->request->getGet("user_name");
        $user_nip = $this->request->getGet("user_nip");
        $division_id = $this->request->getGet("division_id");
        $transaction_id = $this->request->getGet("transaction_id");
        $transaction_status = 0;

        if ($user_id > 0) {
            $input["transaction_status"] = $transaction_status;
            $input["user_id"] = $user_id;

            $this->db->table("transaction")
                ->where("transaction_id", $transaction_id)
                ->update($input);
        } else {
            $inputuser["user_nip"] = $user_nip;
            $inputuser["user_name"] = $user_name;
            $inputuser["division_id"] = $division_id;
            $user = $this->db->table("user")
                ->insert($inputuser);
            $user_id = $this->db->insertID();

            $input["transaction_status"] = $transaction_status;
            $input["user_id"] = $user_id;

            $this->db->table("transaction")
                ->where("transaction_id", $transaction_id)
                ->update($input);
        }
    }

    public function updatestatus()
    {
        $input["transaction_status"] = $this->request->getGet("transaction_status");
        $this->db->table("transaction")
            ->where("transaction_id", $this->request->getGet("transaction_id"))
            ->update($input);
        echo $data["message"] = $input["transaction_status"];
    }

    public function cekstatus()
    {
        $status = $this->db->table("transaction")
            ->where("transaction_id", $this->request->getGet("transaction_id"))
            ->get();
        $data["message"] = 2;
        foreach ($status->getResult() as $status) {
            $data["message"] = $status->transaction_status;
        }
        echo $data["message"];
    }

    public function nota()
    {
        $transaction = $this->db->table("transaction")
            ->join("member", "member.member_id=transaction.member_id", "left")
            ->join("positionm", "positionm.positionm_id=member.positionm_id", "left")
            ->join("(SELECT store_id AS dstore_id, positionm_id AS dpositionm_id FROM positionm WHERE positionm_default='1')as pdefault", "pdefault.dstore_id=transaction.store_id", "left")
            ->where("transaction_id", $this->request->getGet("transaction_id"))
            ->get();
        // echo $this->db->getLastQuery();
        foreach ($transaction->getResult() as $transaction) {
            if ($transaction->transaction_status == 0) {
                $iconprint = "";
            } else {
                $iconprint = "hide";
            }
            if ($transaction->member_id > 0) {
                $positionm_id = $transaction->positionm_id;
            } else {
                $positionm_id = $transaction->dpositionm_id;
            }
        ?>
            <div class="row">
                <div class="col-9">
                    NOTA : <i id="transactionno"><?= $transaction->transaction_no; ?></i>
                    <input type="hidden" id="member_id" value="<?= $transaction->member_id; ?>" />
                    <input type="hidden" id="positionm_id" value="<?= $positionm_id; ?>" />
                    <?php if ($transaction->member_id > 0) { ?>( <?= $transaction->member_name; ?> )<?php } ?>
                    <script>
                        cariproduk();
                    </script>
                </div>
                <div class="col-3 text-right">
                    <button onclick="print(<?= $transaction->transaction_id; ?>);" id="printicon" class="btn btn-warning btn-xs btn-right fa fa-print mb-2" type="button"></button>
                    <?php
                    if (
                        (
                            isset(session()->get("position_administrator")[0][0])
                            && (
                                session()->get("position_administrator") == "1"
                                || session()->get("position_administrator") == "2"
                            )
                        ) ||
                        (
                            isset(session()->get("halaman")['13']['act_delete'])
                            && session()->get("halaman")['13']['act_delete'] == "1"
                        )
                    ) { ?>
                        <button onclick="deletenota(<?= $transaction->transaction_id; ?>);" class="btn btn-danger btn-xs btn-right fa fa-close mb-2" type="button"></button>
                    <?php } ?>
                </div>
            </div>
            <div>
                <input type="hidden" id="transaction_status" value="<?= $transaction->transaction_status; ?>" />
                <input type="hidden" id="transaction_id" value="<?= $transaction->transaction_id; ?>" />
                <input type="hidden" id="transactiond_id" value="0" />
                <input type="hidden" id="transaction_no" value="<?= $transaction->transaction_no; ?>" />
                <table id="" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <!-- <table id="dataTable" class="table table-condensed table-hover w-auto dtable"> -->
                    <thead class="">
                        <tr>
                            <th></th>
                            <th>No.</th>
                            <th>Batch</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $usr = $this->db
                            ->table("transactiond")
                            ->select("*,SUM(transactiond_qty)AS qty, SUM(transactiond_price)AS price,")
                            ->join("product", "product.product_id=transactiond.product_id", "left")
                            ->join("unit", "unit.unit_id=product.unit_id", "left")
                            ->where("product.store_id", session()->get("store_id"))
                            ->where("transactiond.transaction_id", $this->request->getGet("transaction_id"))
                            ->groupBy("transactiond.product_id")
                            ->orderBy("product_name", "ASC")
                            ->get();
                        //echo $this->db->getLastquery();
                        $no = 1;
                        $tprice = 0;
                        foreach ($usr->getResult() as $usr) {
                        ?>
                            <tr class="">
                                <?php if (!isset($_GET["report"])) { ?>
                                    <td style="padding-left:0px; padding-right:0px;">
                                        <?php
                                        if (
                                            (
                                                isset(session()->get("position_administrator")[0][0])
                                                && (
                                                    session()->get("position_administrator") == "1"
                                                    || session()->get("position_administrator") == "2"
                                                )
                                            ) ||
                                            (
                                                isset(session()->get("halaman")['13']['act_delete'])
                                                && session()->get("halaman")['13']['act_delete'] == "1"
                                            )
                                        ) { ?>
                                            <button class="btn btn-xs btn-danger delete m-2" onclick="deletetransactiond(<?= $usr->transaction_id; ?>,<?= $usr->product_id; ?>,<?= $usr->qty; ?>);" name="delete" value="OK"><span class="fa fa-close" style="color:white;"></span> </button>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                                <td class="text-small0"><?= $no++; ?></td>
                                <td class="text-small0"><?= $usr->product_batch; ?></td>
                                <td class="text-small0"><?= $usr->product_name; ?></td>
                                <?php
                                $qty = $usr->qty;
                                $price = $usr->price;
                                $tprice += $price;
                                ?>
                                <td class="text-small0">
                                    <?php
                                    if (
                                        (
                                            isset(session()->get("position_administrator")[0][0])
                                            && (
                                                session()->get("position_administrator") == "1"
                                                || session()->get("position_administrator") == "2"
                                            )
                                        ) ||
                                        (
                                            isset(session()->get("halaman")['13']['act_update'])
                                            && session()->get("halaman")['13']['act_update'] == "1"
                                        )
                                    ) { ?>
                                        <?php if ($qty > 0) { ?>
                                            <i onclick="updateqty(<?= $usr->transactiond_id; ?>,'kurang','1')" class="fa fa-minus text-small text-danger pointer"></i>
                                        <?php } ?>
                                        <button type="button" class="btn btn-xs btn-warning" onclick="insertjmlnota(<?= $usr->product_id; ?>);$('#transactiond_id').val(<?= $usr->transactiond_id; ?>);$('#qtyproduct').val(<?= $qty; ?>);"> <?= number_format($qty, 0, ",", ".") ?> <?= $usr->unit_name; ?> </button>
                                        <?php if ($usr->product_stock > 0) { ?>
                                            <i onclick="updateqty(<?= $usr->transactiond_id; ?>,'tambah','1')" class="fa fa-plus text-small text-success pointer"></i>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?= number_format($qty, 0, ",", ".") ?> <?= $usr->unit_name; ?>
                                    <?php } ?>
                                </td>
                                <td class="text-small0"><?= number_format($price, 0, ",", ".") ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th colspan="5">Total</th>
                            <th>
                                <?= number_format($tprice, 0, ",", "."); ?>
                                <input type="hidden" id="tagihan" value="<?= $tprice; ?>" />
                            </th>
                        </tr>
                        <tr>
                            <th colspan="5">
                                <?php
                                if (
                                    (
                                        isset(session()->get("position_administrator")[0][0])
                                        && (
                                            session()->get("position_administrator") == "1"
                                            || session()->get("position_administrator") == "2"
                                        )
                                    ) ||
                                    (
                                        isset(session()->get("halaman")['13']['act_create'])
                                        && session()->get("halaman")['13']['act_create'] == "1"
                                    )
                                ) { ?>
                                    <button class="btn btn-md btn-success" onclick="bayar();"><span class="fa fa-money" style="color:white;"></span> Bayar</button>
                                <?php } else {
                                    echo "Bayar";
                                } ?>
                                <input type="hidden" id="bayarannya" value="<?= $transaction->transaction_pay; ?>" />
                            </th>
                            <th class="dibayar"><?= $transaction->transaction_pay; ?></th>
                        </tr>
                        <tr>
                            <th colspan="5">
                                Kembalian
                                <input type="hidden" id="kembaliannya" value="<?= $transaction->transaction_change; ?>" />
                                <input type="hidden" id="status" value="<?= $transaction->transaction_status; ?>" />
                            </th>
                            <th class="kembalian"><?= $transaction->transaction_change; ?></th>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php
        }
    }

    public function notaumum()
    {
        $transaction = $this->db->table("transaction")
            ->join("member", "member.member_id=transaction.member_id", "left")
            ->where("transaction_id", $this->request->getGet("transaction_id"))
            ->get();
        // echo $this->db->getLastQuery();
        foreach ($transaction->getResult() as $transaction) {
            if ($transaction->transaction_status == 0) {
                $iconprint = "";
            } else {
                $iconprint = "hide";
            }
        ?>
            <div class="row">
                <div class="col-9">
                    NOTA : <i id="transactionno"><?= $transaction->transaction_no; ?></i>
                    <input type="hidden" id="transaction_id" value="<?= $transaction->transaction_id; ?>" />
                    <script>
                        cariproduk();
                    </script>
                </div>
                <div class="col-3 text-right">
                    <button onclick="print(<?= $transaction->transaction_id; ?>);" id="printicon" class="btn btn-warning btn-xs btn-right fa fa-print mb-2" type="button"></button>
                    <?php
                    if (
                        (
                            isset(session()->get("position_administrator")[0][0])
                            && (
                                session()->get("position_administrator") == "1"
                                || session()->get("position_administrator") == "2"
                            )
                        ) ||
                        (
                            isset(session()->get("halaman")['13']['act_delete'])
                            && session()->get("halaman")['13']['act_delete'] == "1"
                        )
                    ) { ?>
                        <button onclick="deletenota(<?= $transaction->transaction_id; ?>);" class="btn btn-danger btn-xs btn-right fa fa-close mb-2" type="button"></button>
                    <?php } ?>
                </div>
            </div>
            <div>
                <input type="hidden" id="transaction_status" value="<?= $transaction->transaction_status; ?>" />
                <input type="hidden" id="transaction_id" value="<?= $transaction->transaction_id; ?>" />
                <input type="hidden" id="transactiond_id" value="0" />
                <input type="hidden" id="transaction_no" value="<?= $transaction->transaction_no; ?>" />
                <table id="" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <!-- <table id="dataTable" class="table table-condensed table-hover w-auto dtable"> -->
                    <thead class="">
                        <tr>
                            <th></th>
                            <th>No.</th>
                            <th>Batch</th>
                            <th>Produk</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $usr = $this->db
                            ->table("transactiond")
                            ->select("*,SUM(transactiond_qty)AS qty, SUM(transactiond_price)AS price,")
                            ->join("product", "product.product_id=transactiond.product_id", "left")
                            ->join("unit", "unit.unit_id=product.unit_id", "left")
                            ->where("transactiond.transaction_id", $this->request->getGet("transaction_id"))
                            ->groupBy("transactiond.product_id")
                            ->orderBy("product_name", "ASC")
                            ->get();
                        //echo $this->db->getLastquery();
                        $no = 1;
                        $tprice = 0;
                        foreach ($usr->getResult() as $usr) {
                        ?>
                            <tr class="">
                                <?php if (!isset($_GET["report"])) { ?>
                                    <?php if ($transaction->transaction_date == date("Y-m-d")) { ?>
                                        <td style="padding-left:0px; padding-right:0px;">
                                            <button class="btn btn-xs btn-danger delete m-2" onclick="deletetransactiond(<?= $usr->transaction_id; ?>,<?= $usr->product_id; ?>,<?= $usr->qty; ?>);" name="delete" value="OK"><span class="fa fa-close" style="color:white;"></span> </button>
                                        </td>
                                    <?php } ?>
                                <?php } ?>
                                <td class="text-small0"><?= $no++; ?></td>
                                <td class="text-small0"><?= $usr->product_batch; ?></td>
                                <td class="text-small0"><?= $usr->product_name; ?></td>
                                <?php
                                $qty = $usr->qty;
                                $price = $usr->price;
                                $tprice += $price;
                                ?>
                                <td class="text-small0">

                                    <?= number_format($qty, 0, ",", ".") ?> <?= $usr->unit_name; ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($_GET["transaction_type"] == "keluar") { ?>
                            <tr>
                                <th colspan="4">
                                    <button class="btn btn-md btn-success" onclick="bayar();"><span class="fa fa-hand-lizard-o" style="color:white;"></span> Ajukan</button>
                                    <input type="hidden" id="bayarannya" value="<?= $transaction->transaction_pay; ?>" />
                                </th>
                                <th class="dibayar"><?= $transaction->transaction_pay; ?></th>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php
        }
    }

    public function listproductgambarumum()
    {
        if ($this->request->getGet("positionm_id") > 0) {
            $positionm_id = $this->request->getGet("positionm_id");
        } else {
            $positionm_id = 0;
        }
        $builder = $this->db->table("product");
        if ($this->request->getGet("product_name") != "") {
            $builder->like("product.product_name", $this->request->getGet("product_name"), "BOTH");
        }
        $product = $builder->orderBy("product_name")
            ->limit(20)
            ->get();
        foreach ($product->getResult() as $product) { ?>
            <?php

            if ($product->product_stock > 0) {
                // $insertnota = "insertnota(".$product->product_id.")";
                $insertnota = "insertjmlnota(" . $product->product_id . ")";
                $disabled = "";
            } else {
                $insertnota = "toast('Info Stock', 'Stock Kosong!')";
                $disabled = "disabled";
            }

            if ($product->product_picture == "") {
                $gambar = "no_image.png";
            } else {
                $gambar = $product->product_picture;
            }

            ?>
            <div class="col-md-3 col-sm-6 divimg_product <?= $disabled; ?>" onclick="<?= $insertnota; ?>">
                <figure class="caption-1 pointer">
                    <img src="<?= base_url("images/product_picture/" . $gambar); ?>" alt="" class="w-100 card-img-top  img_product">
                    <figcaption class="px-5 py-4 text-center text-light">
                        <h2 class="h5 font-weight-bold mb-0 text-small1 text-light figcaption"><?= $product->product_name; ?></h2>
                        <p class="text-small2 figcaption">Stok: <?= number_format($product->product_stock, 0, ",", "."); ?></p>
                    </figcaption>
                </figure>
            </div>
        <?php }
    }

    public function listproductgambar()
    {
        if ($this->request->getGet("positionm_id") > 0) {
            $positionm_id = $this->request->getGet("positionm_id");
        } else {
            $positionm_id = 0;
        }
        $builder = $this->db->table("product")
            ->join("(SELECT sell_percent, sell_price, positionm_id, product_id AS prodid FROM sell)sell", "sell.prodid=product.product_id AND sell.positionm_id=" . $positionm_id, "left");
        if ($this->request->getGet("product_name") != "") {
            $builder->like("product.product_name", $this->request->getGet("product_name"), "BOTH");
        }
        $builder->where("product.store_id", session()->get("store_id"));
        $product = $builder->orderBy("product_name")
            ->limit(20)
            ->get();
        foreach ($product->getResult() as $product) { ?>
            <?php
            if (
                (
                    isset(session()->get("position_administrator")[0][0])
                    && (
                        session()->get("position_administrator") == "1"
                        || session()->get("position_administrator") == "2"
                    )
                ) ||
                (
                    isset(session()->get("halaman")['13']['act_create'])
                    && session()->get("halaman")['13']['act_create'] == "1"
                )
            ) {
                if ($product->product_stock > 0) {
                    // $insertnota = "insertnota(".$product->product_id.")";
                    $insertnota = "insertjmlnota(" . $product->product_id . ")";
                    $disabled = "";
                } else {
                    $insertnota = "toast('Info Stock', 'Stock Kosong!')";
                    $disabled = "disabled";
                }
            } else {
                $insertnota = "";
                $disabled = "disabled";
            }

            if ($product->product_picture == "") {
                $gambar = "no_image.png";
            } else {
                $gambar = $product->product_picture;
            }

            $buy = $product->product_buy;
            $sell = $product->sell_price;
            if ($sell == "") {
                $sell = 0;
            }
            ?>
            <div class="col-3 divimg_product <?= $disabled; ?>" onclick="<?= $insertnota; ?>">
                <figure class="caption-1 pointer">
                    <img src="<?= base_url("images/product_picture/" . $gambar); ?>" alt="" class="w-100 card-img-top  img_product">
                    <figcaption class="px-5 py-4 text-center text-light">
                        <h2 class="h5 font-weight-bold mb-0 text-small1 text-light figcaption"><?= $product->product_name; ?></h2>
                        <p class="text-small2 figcaption"><?= number_format($sell, 0, ",", "."); ?></p>
                    </figcaption>
                </figure>
            </div>
        <?php }
    }

    public function listproductlist()
    {
        ?>
        <!-- <table id="" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%"> -->
        <table id="dataTable" class="table table-condensed table-hover ">
            <thead class="">
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Ube</th>
                    <th>Batch</th>
                    <th>Exp Date</th>
                    <th>Stock</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($this->request->getGet("positionm_id") > 0) {
                    $positionm_id = $this->request->getGet("positionm_id");
                } else {
                    $positionm_id = 0;
                }
                $builder = $this->db->table("product")
                    ->join("(SELECT sell_percent, sell_price, positionm_id, product_id AS prodid FROM sell)sell", "sell.prodid=product.product_id AND sell.positionm_id=" . $positionm_id, "left");
                $usr1 = $builder->join("category", "category.category_id=product.category_id", "left")
                    ->join("unit", "unit.unit_id=product.unit_id", "left")
                    ->join("store", "store.store_id=product.store_id", "left")
                    ->where("product.store_id", session()->get("store_id"));
                if ($this->request->getGet("product_name") != "") {
                    $usr1->like("product.product_name", $this->request->getGet("product_name"), "BOTH");
                }
                $usr = $usr1->orderBy("product_name", "ASC")
                    ->limit(20)
                    ->get();
                // echo $this->db->getLastquery();
                $no = 1;
                foreach ($usr->getResult() as $usr) { ?>
                    <?php
                    if (
                        (
                            isset(session()->get("position_administrator")[0][0])
                            && (
                                session()->get("position_administrator") == "1"
                                || session()->get("position_administrator") == "2"
                            )
                        ) ||
                        (
                            isset(session()->get("halaman")['13']['act_create'])
                            && session()->get("halaman")['13']['act_create'] == "1"
                        )
                    ) {
                        if ($usr->product_stock > 0) {
                            $insertnota = "insertjmlnota(" . $usr->product_id . ")";
                            $disabled = "";
                        } else {
                            $insertnota = "toast('Info Stock', 'Stock Kosong!')";
                            $disabled = "disabled";
                        }
                    } else {
                        $insertnota = "";
                    } ?>
                    <tr class="pointer <?= $disabled; ?>" onclick="<?= $insertnota; ?>">
                        <td><?= $usr->category_name; ?></td>
                        <td><?= $usr->product_name; ?></td>
                        <td><?= $usr->product_ube; ?></td>
                        <td><?= $usr->product_batch; ?></td>
                        <td><?= $usr->product_expiredate; ?></td>
                        <?php
                        $limit = $usr->product_countlimit;
                        $stock = $usr->product_stock;
                        if ($limit >= $stock) {
                            $alstock = "danger";
                        } else {
                            $alstock = "default";
                        }
                        ?>
                        <td><span class="text-<?= $alstock; ?>"><?= number_format($stock, 0, ",", "."); ?></span> <?= $usr->unit_name; ?></td>
                        <?php
                        $buy = $usr->product_buy;
                        // $sell=$usr->product_sell;
                        // $sell=($buy*$positionm_id/100)+ $buy;
                        $sell = $usr->sell_price;
                        if ($sell == "") {
                            $sell = 0;
                        }
                        ?>
                        <td><?= number_format($sell, 0, ",", "."); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php
    }

    public function listproductlistumum()
    {
    ?>
        <!-- <table id="" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%"> -->
        <table id="dataTable" class="table table-condensed table-hover ">
            <thead class="">
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Ube</th>
                    <th>Batch</th>
                    <th>Exp Date</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $builder = $this->db->table("product");
                $usr1 = $builder->join("category", "category.category_id=product.category_id", "left")
                    ->join("unit", "unit.unit_id=product.unit_id", "left")
                    ->join("store", "store.store_id=product.store_id", "left");
                if ($this->request->getGet("product_name") != "") {
                    $usr1->like("product.product_name", $this->request->getGet("product_name"), "BOTH");
                }
                $usr = $usr1->orderBy("product_name", "ASC")
                    ->limit(20)
                    ->get();
                // echo $this->db->getLastquery();
                $no = 1;
                foreach ($usr->getResult() as $usr) { ?>
                    <?php

                    if ($usr->product_stock > 0) {
                        $insertnota = "insertjmlnota(" . $usr->product_id . ")";
                        $disabled = "";
                    } else {
                        $insertnota = "toast('Info Stock', 'Stock Kosong!')";
                        $disabled = "disabled";
                    } ?>
                    <tr class="pointer <?= $disabled; ?>" onclick="<?= $insertnota; ?>">
                        <td><?= $usr->category_name; ?></td>
                        <td><?= $usr->product_name; ?></td>
                        <td><?= $usr->product_ube; ?></td>
                        <td><?= $usr->product_batch; ?></td>
                        <td><?= $usr->product_expiredate; ?></td>
                        <?php
                        $limit = $usr->product_countlimit;
                        $stock = $usr->product_stock;
                        if ($limit >= $stock) {
                            $alstock = "danger";
                        } else {
                            $alstock = "default";
                        }
                        ?>
                        <td><span class="text-<?= $alstock; ?>"><?= number_format($stock, 0, ",", "."); ?></span> <?= $usr->unit_name; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php
    }

    public function listmember()
    {
    ?>
        <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
            <!-- <table id="dataTable" class="table table-condensed table-hover w-auto dtable"> -->
            <thead class="">
                <tr>
                    <?php if (isset($_GET["transaction_id"])) { ?>
                        <th>Action</th>
                    <?php } ?>
                    <th>No.</th>
                    <th>Store</th>
                    <th>Grade</th>
                    <th>Member No.</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $member_id = $this->request->getGet("member_id");
                $builder = $this->db
                    ->table("member")
                    ->join("positionm", "positionm.positionm_id=member.positionm_id", "left")
                    ->join("store", "store.store_id=member.store_id", "left")
                    ->where("member.store_id", session()->get("store_id"));

                if (isset($_GET["member_no"])) {
                    $builder->like("member_no", $this->request->getGet("member_no"), "BOTH");
                }

                if (isset($_GET["member_name"])) {
                    $builder->like("member_name", $this->request->getGet("member_name"), "BOTH");
                }

                $usr = $builder->orderBy("member_id", "desc")
                    ->get();
                // echo $this->db->getLastquery();
                $no = 1;
                foreach ($usr->getResult() as $usr) { ?>
                    <tr>
                        <?php if (isset($_GET["transaction_id"]) && $_GET["transaction_id"] > 0) {
                            $transaction_id = $this->request->getGet("transaction_id");
                        ?>
                            <td style="padding-left:0px; padding-right:0px;">
                                <form method="post" class="btn-action" style="">
                                    <button type="button" class="btn btn-sm btn-success" onclick="insertmember(<?= $transaction_id; ?>,<?= $usr->member_id; ?>)"><span class="fa fa-check" style="color:white;"></span> </button>
                                </form>
                            </td>
                        <?php } ?>
                        <td><?= $no++; ?></td>
                        <td><?= $usr->store_name; ?></td>
                        <td><?= $usr->positionm_name; ?></td>
                        <td><?= $usr->member_no; ?></td>
                        <td><?= $usr->member_name; ?></td>
                        <td><?= $usr->member_email; ?></td>
                        <td><?= $usr->member_address; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
<?php
    }

    public function insertmember()
    {
        $input["member_id"] = $this->request->getGet("member_id");
        $where["transaction_id"] = $this->request->getGet("transaction_id");
        $this->db->table("transaction")
            ->update($input, $where);
        // echo $this->db->getLastQuery();
        echo $where["transaction_id"];

        //rubah harga
        $transactiond = $this->db->table("transactiond")
            ->join("transaction", "transaction.transaction_id=transactiond.transaction_id", "left")
            ->join("member", "member.member_id=transaction.member_id", "left")
            ->join("product", "product.product_id=transactiond.product_id", "left")
            ->join("sell", "sell.product_id=product.product_id AND sell.positionm_id=member.positionm_id AND sell.store_id=transactiond.store_id", "left")
            ->where("transactiond.transaction_id", $where["transaction_id"])
            ->get();
        foreach ($transactiond->getResult() as $transactiond) {
            $where1["transaction_id"] = $transactiond->transaction_id;
            $input1["transactiond_price"] = $transactiond->sell_price * $transactiond->transactiond_qty;
            $builder = $this->db->table('transactiond');
            $builder->update($input1, $where1);
        }
    }
}
