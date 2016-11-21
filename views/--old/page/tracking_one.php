<div class="gps-left">
	<div class="obi  clfloat" style="padding: 0 5px;" >
		<h3 ><?php echo $vehicle->fullname?> </h3>
		<div class="obi-t fweightupcase cenal"><span class="value" name="time" ></span></div>
		<div class="obi-pn fweightupcase cenal" name="vehicle-name" ><?php echo $vehicle->plate_number?></div>
		<div class="obi-s cenal fweightupcase" >
			<div class="icon-h30 icon-motor" ></div><span class="value" name="speed"></span><div class="icon-h30 icon-speed" ></div>
		</div>
		<div class="obi-l" ><label>Tín hiệu GPS</label><span class="value" name="gps" ></span></div>
		<div class="obi-l" ><label>Tín hiệu GSM</label><span class="value" name="gsm"></span></div>
		<div class="obi-l" ><label>Vĩ tuyến</label><span class="value" name="la"></span></div>
		<div class="obi-l" ><label>Kinh tuyến</label><span class="value" name="lon"></span></div>
		<div class="obi-l" ><label>Điện áp nguồn</label><span class="value" name="vaq"></span><span class="unit" >V</span></div>
		<?php 
		if($vehicle->conditioner){
			echo '<div class="obi-l" ><label>Nhiệt Độ Điều Hòa</label><span class="value" name="cooler"></span><span class="unit" >°C</span></div>';
		}
		if($vehicle->heat){
			echo '<div class="obi-l" ><label>Nhiệt Độ Xe</label><span class="value" name="temp"></span><span class="unit" >°C</span></div>';
		}
		if($vehicle->fuel_current){
			echo '<div class="obi-l" ><label>Xăng Trong Bình</label><span class="value" name="fuel"></span><span class="unit" >lít</span></div>';
		}
		if($vehicle->door){
			echo '<div class="obi-l" ><label>Cửa Xe</label><span class="value" name="door"></span></div>';
		}
		?>
		<input type="image" style="border-width:0px; display: none;" title="Cập nhật ví trí mới nhất của thiết bị" id="ImageButton1" name="ImageButton1">
	</div>
	
	<div class="obi  clfloat" style="width: 240px;" >
		<h3 style="width: 240px; left: 0px;">Nhật ký hành trình </h3>
		<div id="obi-calendar" class="ui-date"></div>
		<div class="obi-li" ><input type="text" name="gps-date" /> <span class="value" ><?php echo anchor('#',$this->lang->line('Reset'),' name="reset" ');?></span></div>
		<div class="obi-li" style="text-align: center; display: none;">Hiển Thị<span name="gps-number" class="value" >1</span> / <span name="gps-total" class="value" >1</span>Điểm  <input type="hidden" value="1" name="page" /><?php echo anchor('#',$this->lang->line('Next'),' name="next" class="hidden" ');?></div>
	</div>
</div>
<div class="gps-right gmap-area" >
	<div id="gmap" ></div>
	<div id="gmap-status"></div>
	<div id="gmap-address" lo="" la="" ></div>
</div>
