<?php echo $this->include("template/headersaja_v"); ?>
<style>
    .separator {
        border-bottom: 1px dashed #aaa;
    }

    .text-small {
        font-size: 8px;
    }

    .img_product {
        width: 100%;
        height: 150px !important;
        border: rgba(155, 155, 155, 0.5) solid 1px;
        border-radius: 4px;
    }

    .pointer {
        cursor: pointer;
    }

    .centerpage {
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    .hide {
        display: none;
    }

    .absolute-top-right {
        position: absolute;
        right: 5px;
        top: 5px;
    }




    @media print {

        html,
        body,
        div {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0px !important;
            line-height: 100%;
        }

        #storename_title {
            margin: bottom 30px, im !important;
        }

        p {
            margin-bottom: 0px;
            font-size: 50px;
        }

        @page {}

        .tebal10 {
            font-size: 50px;
            font-weight: bold;
        }

        th,
        td {
            padding: 0px 1px 0px 1px;
            font-size: 15px;
            line-height: 100% !important;
        }

        .pagebreak {
            page-break-after: always;
        }
    }

    .border {
        border: black solid 1px;
    }
</style>
<?php
$builder = $this->db->table("transaction")
    ->join("user", "user.user_id=transaction.user_id", "left")
    ->join("position", "position.position_id=user.position_id", "left")
    ->where("transaction_id", $this->request->getGet("transaction_id"));
$transaction = $builder->get();
if ($builder->countAll() > 0) {
    foreach ($transaction->getResult() as $transaction) {
?>
        <div class='container-fluid'>
            <div class='row'>
                <div class="col-md-12 row" style=" border-top:black solid  1px; border-bottom:black solid 1px; padding: 20px;">
                    <div class="col-2" style=" ">
                        <img src="<?= base_url("images/kejaksaan.png"); ?>" class="img" style="width:auto; height:auto; max-height:100px;" />
                    </div>
                    <div class="col-8" >
                        <div class="col-12 text-center" id="storename_title" style="font-weight:bold; padding:0px; font-size:20px;">KEJAKSAAN REPUBLIK INDONESIA</div>
                        <div class="col-12 text-center" style="font-weight:bold; padding:0px; font-size:20px;">KEJAKSAAN TINGGI JAWA BARAT</div>
                        <div class="col-12 text-center" style="font-weight:bold; padding:0px; font-size:25px;">KEJAKSAAN NEGERI KABUPATEN BOGOR</div>
                        <div class="col-12 text-center" style="padding:0px; font-size:12px;">Jl. Raya Tegar Beriman, Cibinong Kabupaten Bogor, Jawa Barat 16914</div>
                        <div class="col-12 text-center" style="padding:0px; font-size:12px;">Telp. (021) 8758787 fax. (021) 8750838 Email : http//kejari-cibinong.go.id</div>
                    </div>
                    <div class="col-2" style=" ">
                    <!-- <img src="<?= base_url("images/store_picture/" . $store_picture); ?>" class="img" style="width:auto; height:auto; max-height:100px;" /> -->
                    </div>
                </div>
                <div class="col-12 mt-3 p-0 tebal10 text-center" style="font-size:20px;">TANDA TERIMA PENERIMAAN ALAT TULIS KANTOR (ATK)</div>
                <div class="col-12 p-0 tebal10 text-center" style="font-size:20px;">PADA SUB BAGIAN PEMBINAAN</div>
                <div class="col-12 p-0 text-center" style="font-size:15px;">(<?= $transaction->transaction_no; ?>)</div>
                <?php
                $transactionDate = strtotime($transaction->transaction_date);
                $days = [
                    'Sunday' => 'Minggu',
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                ];
                $months = [
                    'January' => 'Januari',
                    'February' => 'Februari',
                    'March' => 'Maret',
                    'April' => 'April',
                    'May' => 'Mei',
                    'June' => 'Juni',
                    'July' => 'Juli',
                    'August' => 'Agustus',
                    'September' => 'September',
                    'October' => 'Oktober',
                    'November' => 'November',
                    'December' => 'Desember',
                ];
                $dayName = $days[date('l', $transactionDate)];
                $dateNumber = date('d', $transactionDate);
                $monthName = $months[date('F', $transactionDate)];
                $yearNumber = date('Y', $transactionDate);

                // Menggabungkan semua bagian untuk menghasilkan format yang diinginkan
                $formattedDate = "$dayName $dateNumber $monthName $yearNumber";
                ?>
                <div class="col-12 mt-2 p-0" style="font-size:15px;">Pada hari ini <?= $formattedDate; ?> bertempat Kejaksaan Negeri Kabupaten Bogor, saya :</div>
                <div class="col-2 p-0" style="font-size:15px;">Nama</div>
                <div class="col-10 p-0" style="font-size:15px;">: <?= $transaction->user_name; ?></div>
                <div class="col-2 p-0" style="font-size:15px;">Pangkat</div>
                <div class="col-10 p-0" style="font-size:15px;">: <?= $transaction->user_pangkat; ?></div>
                <div class="col-2 p-0" style="font-size:15px;">NIP</div>
                <div class="col-10 p-0" style="font-size:15px;">: <?= $transaction->user_nip; ?></div>
                <div class="col-2 p-0" style="font-size:15px;">Jabatan</div>
                <div class="col-10 p-0" style="font-size:15px;">: <?= $transaction->position_name; ?></div>
                <div class="col-12 mt-2 p-0" style="font-size:15px;">berdasarkan bukti pada Buku Ambil Barang Alat Tulis Kantor (ATK) pada bulan Oktober 2024 , telah menerima ATK dari :</div>
                <div class="col-2 p-0" style="font-size:15px;">Nama</div>
                <div class="col-10 p-0" style="font-size:15px;">: <?= $petugas_name; ?></div>
                <div class="col-2 p-0" style="font-size:15px;">Pangkat</div>
                <div class="col-10 p-0" style="font-size:15px;">: <?= $petugas_pangkat; ?></div>
                <div class="col-2 p-0" style="font-size:15px;">NIP</div>
                <div class="col-10 p-0" style="font-size:15px;">: <?= $petugas_nip; ?></div>
                <div class="col-2 p-0" style="font-size:15px;">Jabatan</div>
                <div class="col-10 p-0" style="font-size:15px;">: <?= $petugas_jabatan; ?></div>
                <div class="col-12 mb-2 p-0" style="font-size:15px;">sebagai berikut :</div>
                <div class="col-12" style="padding:0px;">
                    <table id="" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                        <!-- <table id="dataTable" class="table table-condensed table-hover w-auto dtable"> -->
                        <thead class="">
                            <tr>
                                <th>NO</th>
                                <th>BATCH</th>
                                <th>NAMA BARANG</th>
                                <th>JUMLAH</th>
                                <th>SATUAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $usr = $this->db
                                ->table("transactiond")
                                ->select("*,SUM(transactiond_qty)AS qty")
                                ->join("product", "product.product_id=transactiond.product_id", "left")
                                ->join("unit", "unit.unit_id=product.unit_id", "left")
                                ->where("transactiond.transaction_id", $this->request->getGet("transaction_id"))
                                ->groupBy("transactiond.product_id")
                                ->orderBy("product_name", "ASC")
                                ->get();
                            //echo $this->db->getLastquery();
                            $no = 1;
                            foreach ($usr->getResult() as $usr) {
                            ?>
                                <tr>
                                    <td><?= $no++; ?>. </td>
                                    <td><?= $usr->product_batch; ?></td>
                                    <td class="text-left"><?= $usr->product_name; ?></td>
                                    <td><?= number_format($usr->qty, 0, ".", ",") ?></td>
                                    <td><?= $usr->unit_name; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-12 mt-3 pt-0 text-center" style="  ">

                </div>
                <div class="col-5 row mt-5 p-0 text-center" style="">
                    <div class="col-12">Yang Menerima,</div>
                    <div class="col-12" style="height:50px;">&nbsp;</div>
                    <div class="col-12" style="font-weight: bold; text-decoration: underline!important;"><?= $transaction->user_name; ?></div>
                    <div class="col-12" class="nipsign"><?= $transaction->user_nip; ?></div>
                </div>
                <div class="col-2 row mt-5 p-0 text-center" style="">
                </div>
                <div class="col-5 row mt-5 p-0 text-center" style="">
                    <div class="col-12">Yang Menyerahkan,</div>
                    <div class="col-12" style="height:50px;">&nbsp;</div>
                    <div class="col-12" style="font-weight: bold; text-decoration: underline!important;"><?= $petugas_name; ?></div>
                    <div class="col-12" class="nipsign"><?= $petugas_nip; ?></div>
                </div>
            </div>
        </div>

    <?php }
} else { ?>
    <h1 class="centerpage">Data tidak ditemukan!</h1>
<?php } ?>
<script>
    window.print();
    setTimeout(function() {
        window.close();
    }, 500);
</script>

<?php echo  $this->include("template/footersaja_v"); ?>