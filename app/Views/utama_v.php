<?php echo $this->include("template/header_v"); ?>

<div class='container'>
	<div class='row'>
		<div class='col'>
			<div class="row">
				<!-- Column -->
				<div class="col-lg-12">
					<div class="card">
						<div class="card-body">
							<div class="card-two">
								<header>
									<div class="avatar">
										<img src="images/global/user.png" alt="<?= session()->get("user_name"); ?>" />
									</div>
								</header>
								<h3><?= session()->get("position_name"); ?></h3>
								<div class="desc">
									<?= session()->get("store_name"); ?>
								</div>
								<!-- <div class="contacts"><?= session()->get("position_name"); ?>
									<div class="clear"></div>
								</div> -->
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<?php
				$from = date("Y-m-01");
				$to = date("Y-m-t");
				$builder = $this->db
					->table("transaction")
					->select("COUNT(transaction_id)AS jumlah,transaction_date AS tanggal");
				$builder->where("transaction.transaction_date >=", $from);
				$builder->where("transaction.transaction_date <=", $to);
				$transaksi = $builder->groupBy("transaction_date")->get();
				$tanggal="";
				$jumlah="";
				foreach ($transaksi->getResult() as $transaksi) {
					$tanggal.="'".$transaksi->tanggal."',";
					$jumlah.=$transaksi->jumlah.",";
				} 
				
				// echo $this->db->getLastquery();
				?>
				<div class="col-md-6 col-xs-12">
					<script src="https://code.highcharts.com/highcharts.js"></script>
					<script src="https://code.highcharts.com/modules/data.js"></script>
					<script src="https://code.highcharts.com/modules/series-label.js"></script>
					<script src="https://code.highcharts.com/modules/exporting.js"></script>
					<script src="https://code.highcharts.com/modules/export-data.js"></script>
					<script src="https://code.highcharts.com/modules/accessibility.js"></script>
					<style>
						.highcharts-figure,
						.highcharts-data-table table {
							min-width: 360px;
							max-width: 800px;
							margin: 1em auto;
						}

						.highcharts-data-table table {
							font-family: Verdana, sans-serif;
							border-collapse: collapse;
							border: 1px solid #ebebeb;
							margin: 10px auto;
							text-align: center;
							width: 100%;
							max-width: 500px;
						}

						.highcharts-data-table caption {
							padding: 1em 0;
							font-size: 1.2em;
							color: #555;
						}

						.highcharts-data-table th {
							font-weight: 600;
							padding: 0.5em;
						}

						.highcharts-data-table td,
						.highcharts-data-table th,
						.highcharts-data-table caption {
							padding: 0.5em;
						}

						.highcharts-data-table thead tr,
						.highcharts-data-table tr:nth-child(even) {
							background: #f8f8f8;
						}

						.highcharts-data-table tr:hover {
							background: #f1f7ff;
						}

						.highcharts-description {
							margin: 0.3rem 10px;
						}
					</style>

					<figure class="highcharts-figure">
						<div id="container"></div>
						<p class="highcharts-description">

						</p>
					</figure>
					<?php
					$bulan = [
						'Jan' => 'Januari',
						'Feb' => 'Februari',
						'Mar' => 'Maret',
						'Apr' => 'April',
						'May' => 'Mei',
						'Jun' => 'Juni',
						'Jul' => 'Juli',
						'Aug' => 'Agustus',
						'Sep' => 'September',
						'Oct' => 'Oktober',
						'Nov' => 'November',
						'Dec' => 'Desember'
					];
					?>
					<script>
						// A point click event that uses the Renderer to draw a label next to the point
						// On subsequent clicks, move the existing label instead of creating a new one.
						Highcharts.addEvent(Highcharts.Point, 'click', function() {
							if (this.series.options.className.indexOf('popup-on-click') !== -1) {
								const chart = this.series.chart;
								const date = Highcharts.dateFormat('%A, %b %e, %Y', this.x);
								const text = `<b>${date}</b><br/>${this.y} ${this.series.name}`;

								const anchorX = this.plotX + this.series.xAxis.pos;
								const anchorY = this.plotY + this.series.yAxis.pos;
								const align = anchorX < chart.chartWidth - 200 ? 'left' : 'right';
								const x = align === 'left' ? anchorX + 10 : anchorX - 10;
								const y = anchorY - 30;
								if (!chart.sticky) {
									chart.sticky = chart.renderer
										.label(text, x, y, 'callout', anchorX, anchorY)
										.attr({
											align,
											fill: 'rgba(0, 0, 0, 0.75)',
											padding: 10,
											zIndex: 7 // Above series, below tooltip
										})
										.css({
											color: 'white'
										})
										.on('click', function() {
											chart.sticky = chart.sticky.destroy();
										})
										.add();
								} else {
									chart.sticky
										.attr({
											align,
											text
										})
										.animate({
											anchorX,
											anchorY,
											x,
											y
										}, {
											duration: 250
										});
								}
							}
						});


						Highcharts.chart('container', {

							chart: {
								scrollablePlotArea: {
									minWidth: 700
								}
							},

							data: {

							},

							title: {
								text: 'Transaksi Bulanan',
								align: 'left'
							},

							subtitle: {
								text: 'Bulan: <?= $bulan[date("M")]; ?>',
								align: 'left'
							},

							xAxis: {
								categories: [
									<?php echo $tanggal;?>
								]
							},

							yAxis: [{ // left y axis
								title: {
									text: null
								},
								labels: {
									align: 'left',
									x: 3,
									y: 16,
									format: '{value:.,0f}'
								},
								showFirstLabel: false
							}, { // right y axis
								linkedTo: 0,
								gridLineWidth: 0,
								opposite: true,
								title: {
									text: null
								},
								labels: {
									align: 'right',
									x: -3,
									y: 16,
									format: '{value:.,0f}'
								},
								showFirstLabel: false
							}],

							legend: {
								align: 'left',
								verticalAlign: 'top',
								borderWidth: 0
							},

							tooltip: {
								shared: true,
								crosshairs: true
							},

							plotOptions: {
								series: {
									cursor: 'pointer',
									className: 'popup-on-click',
									marker: {
										lineWidth: 1
									}
								}
							},

							series: [{
								name: '',
								data: [
									<?php echo $jumlah;?>
								]
							}]
						});
					</script>
				</div>
				<div class="col-md-6 col-xs-12">
					<div class="card">
						<div class="card-title">
							<h4>Daftar Stock Limited</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-hover ">
									<thead>
										<tr>
											<th>Product</th>
											<th>Jumlah</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$builder = $this->db
											->table("product");
										$builder->where("product_stock<=product_countlimit");
										$usr = $builder
											->where("store_id", session()->store_id)
											->orderBy("product_name", "ASC")
											->get();
										// echo $this->db->getLastquery();
										foreach ($usr->getResult() as $usr) {
										?>
											<tr>
												<td><?= $usr->product_name; ?></td>
												<td><?= $usr->product_stock; ?></td>
											</tr>
										<?php }	?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php echo  $this->include("template/footer_v"); ?>

<?php //echo $this->endSection(); 
?>