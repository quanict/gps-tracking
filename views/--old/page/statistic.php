
<div class="gps-left">
	<div class="obi  clfloat" style="padding: 0 5px;" >
		<h3>Dữ Liệu Thống Kê</h3>
		<div class="obi-l" >
			<label>Xem Dữ Liệu Ngày</label>
			<select id="obi-day" name="rday">
				<option value="0" ><?php echo lang('Day')?></option>
				<?php for($i=1; $i <= 31 ;$i++) echo '<option value="'.$i.'" >'.$i.'</option>';?>
			</select>
		</div>
		<?php if($repo): ?>
			<?php 
			if( isset($repo->name) ){
				echo '<div class="obi-l" ><label>Thiết Bị</label>'.$repo->name.'</div>';
			}
			if( isset($repo->plate_number) ){
				echo '<div class="obi-l" ><label>Biển Số Xe</label>'.$repo->plate_number.'</div>';
			}	
			?>
			<div class="obi-l" ><label>Quãng Đường Đã Đi</label><span name="length_road"></span></div>
			<div class="obi-l" ><label>Thời gian di chuyển</label><span name="moving_time"></span></div>
			<div class="obi-group speed anchor" >
				<div class="obi-l" ><label>Vận tốc cực đại</label><span name="max_speed"></span> KM/h</div>
				<div class="obi-l" ><label>Vào lúc</label><span name="time_max"></span></div>
			</div>
			<div class="obi-group fuel" >
				<div class="obi-l" ><label>Nhiên Liệu Tiêu Thụ</label><span name="fuel"></span></div>
				<div class="obi-l" ><label>Chi Phí Nhiên Liệu</label><span name="fuel_price"></span></div>
				<div class="obi-l" ><span name="fuel_price_vnd" class="money-vnd" ></span></div>
			</div>
		<?php endif;?>

	</div>
</div>
<div class="gps-right">
	<?php if( isset($repo->type) && $repo->type== 2 && isset($repo->fuel_current) && $repo->fuel_current==1 ):?>
	<input type="button" value="Dữ Liệu Đường Đi" title="Dữ Liệu Đường Đi" name="waydata" class="gps-button" disabled="disabled" >
	<input type="button" value="Dữ Liệu Nhiên Liệu" title="Dữ Liệu Nhiên Liệu" name="fueldata" class="gps-button"  >
	<?php endif;?>
	<div id="chart" style="height: 250px; width:850px;" ></div>
</div>
<div class="report clearfix" style="padding: 0 10px;">
<div class="talbe-view dataTables_wrapper">
	<h3 class="table-title" ></h3>
	<p class="export crfloat" ></p>
	<table  class="style1" id="node-data-stop" >
		<thead><tr>
			<th width="70"></th>
			<th width="50"></th>
			<th width="50"></th>
			<th width="50"></th>
			<th width="50"></th>
			<th width="200"></th>
		</tr></thead>
		<tbody></tbody><tfoot></tfoot>
	</table>
</div></div>

<?php 
/*
<thead><tr>
<th width="70">Thời Gian</th>
<th width="50">Tín Hiệu GPS</th>
<th width="50">Tín Hiệu GSM</th>
<th width="50">Điện Áp Nguồn</th>
<th width="50">Thời Gian Dừng</th>
<th width="200">Vị Trí</th>
</tr></thead>
<tbody></tbody><tfoot></tfoot>
*/
?>
