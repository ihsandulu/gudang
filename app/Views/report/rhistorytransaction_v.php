<?php echo $this->include("template/header_v"); ?>

<div class='container-fluid'>
    <div class='row'>
        <div class='col-12'>
            <div class="card">
                <div class="card-body">


                    <div class="row">
                        <?php if (!isset($_GET['user_id']) && !isset($_POST['new']) && !isset($_POST['edit'])) {
                            $coltitle = "col-md-10";
                        } else {
                            $coltitle = "col-md-8";
                        } ?>
                        <div class="<?= $coltitle; ?>">
                            <h4 class="card-title"></h4>
                            <!-- <h6 class="card-subtitle">Export data to Copy, CSV, Excel, PDF & Print</h6> -->
                        </div>
                    </div>

                    <?php
                    if (isset($_GET["from"]) && $_GET["from"] != "") {
                        $from = $_GET["from"];
                    } else {
                        $from = date("Y-m-d");
                    }

                    if (isset($_GET["to"]) && $_GET["to"] != "") {
                        $to = $_GET["to"];
                    } else {
                        $to = date("Y-m-d");
                    }

                    ?>
                    <form class="form-inline">
                        <label for="from">Dari:</label>&nbsp;
                        <input type="date" id="from" name="from" class="form-control" value="<?= $from; ?>">&nbsp;
                        <label for="to">Ke:</label>&nbsp;
                        <input type="date" id="to" name="to" class="form-control" value="<?= $to; ?>">&nbsp;
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>

                    <?php if ($message != "") { ?>
                        <div class="alert alert-info alert-dismissable">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <strong><?= $message; ?></strong>
                        </div>
                    <?php } ?>

                    <div class="table-responsive m-t-40">
                        <table id="example231" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <!-- <table id="dataTable" class="table table-condensed table-hover w-auto dtable"> -->
                            <thead class="">
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal</th>
                                    <th>No. Transaksi</th>
                                    <th>Pengaju</th>
                                    <th>Petugas</th>
                                    <th>ACC</th>
                                    <th>Produk</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $arrproduk = array();
                                $product = $this->db->table("transactiond")
                                    ->join("product", "product.product_id=transactiond.product_id", "left")
                                    ->get();
                                foreach ($product->getResult() as $product) {
                                    $arrproduk[$product->transaction_id][] = $product->product_name . " (" . $product->transactiond_qty . "), ";
                                }

                                $builder = $this->db
                                    ->table("transaction")
                                    ->select("*,user.user_name as pengaju_name, cashieru.user_name as petugas_name, pic.user_name as pic_name")
                                    ->join("store", "store.store_id=transaction.store_id", "left")
                                    ->join("user", "user.user_id=transaction.user_id", "left")
                                    ->join("user AS cashieru", "cashieru.user_id=transaction.cashier_id", "left")
                                    ->join("user AS pic", "pic.user_id=transaction.pic_id", "left")
                                    ->where("transaction.store_id", session()->get("store_id"));
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
                                $usr = $builder
                                    ->orderBy("transaction_id", "ASC")
                                    ->get();
                                //echo $this->db->getLastquery();
                                $no = 1;
                                $tbill = 0;
                                $tpay = 0;
                                $tchange = 0;
                                foreach ($usr->getResult() as $usr) {
                                    if ($usr->transaction_bill == null) {
                                        $usr->transaction_bill = 0;
                                    }
                                    if ($usr->transaction_pay == null) {
                                        $usr->transaction_pay = 0;
                                    }
                                    if ($usr->transaction_change == null) {
                                        $usr->transaction_change = 0;
                                    }
                                ?>
                                    <tr>
                                        <td>
                                            <!-- <a href="<?= base_url("rtransactiond?transaction_id=" . $usr->transaction_id); ?>" class="btn btn-xs btn-info"><span class="fa fa-cubes"></span> <?= $no++; ?></a> -->

                                            <a target="_blank" href="<?= base_url("transactionprintumum?transaction_id=" . $usr->transaction_id); ?>" class="btn btn-xs btn-warning fa fa-print"></a>
                                        </td>
                                        <td><?= $usr->transaction_date; ?></td>
                                        <td><?= $usr->transaction_no; ?></td>
                                        <td><?= $usr->pengaju_name; ?></td>
                                        <td><?= $usr->petugas_name; ?></td>
                                        <td id="username<?= $no; ?>">
                                            <?php if ($usr->pic_name == "" && $usr->transaction_type=="keluar") { ?>
                                                <button type="button" class="btn btn-warning btn-xs" onclick="acc('<?=session()->get('user_id');?>', '<?=session()->get('user_name');?>', '<?=$usr->transaction_id;?>', '<?=$no;?>')">Belum ACC</button>
                                            <?php } else {
                                                echo $usr->pic_name;
                                            } ?>
                                        </td>
                                        <td class="text-left">
                                            <?php
                                            if (isset($arrproduk[$usr->transaction_id])) {
                                                echo implode(' ', $arrproduk[$usr->transaction_id]);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            echo ucfirst($usr->transaction_type); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <script>
                            function acc(pic_id, user_name, transaction_id, no) {
                                // alert("<?= base_url("api/acc"); ?>?pic_id="+pic_id+"&transaction_id="+transaction_id);
                                $.get("<?= base_url("api/acc"); ?>", {
                                        pic_id: pic_id,
                                        transaction_id: transaction_id
                                    })
                                    .done(function() {
                                        $("#username" + no).text(user_name);
                                    });
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('.select').select2();
    var title = "History Transaksi";
    $("title").text(title);
    $(".card-title").text(title);
    $("#page-title").text(title);
    $("#page-title-link").text(title);
</script>

<?php echo  $this->include("template/footer_v"); ?>