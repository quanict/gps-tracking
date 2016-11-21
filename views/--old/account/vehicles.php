<div class="content-box block-right">
	<div class="box-body">
		<div class="box-header clear">
		<h2 class="fl" ><?php echo lang('Device List');?></h2>
		</div>
	</div>
	<div class="box-wrap clear">
	<p>Tổng số <?php echo count($rows);?> thiết bị trong danh sách quản lý</p>
	<div class="talbe-view dataTables_wrapper">
		<table  class="style1" id="node-data">
			<thead><tr>
				<th style="width: 150px;"><?php echo lang('Device Name')?></th>
				<th style="width: 100px;"><?php echo lang('Plate Number')?></th>
				<th style="width: 50px;"><?php echo lang('IMEI Number')?></th>
				<th style="width: 50px;" class="center"><?php echo lang('Created')?></th>
				<th style="width: 50px;" class="center"><?php echo lang('Expiry')?></th>
				<th style="width: 50px;" class="center"><?php echo lang('Status')?></th>
				<th style="width: 80px;" class="center"><?php echo lang('Actions')?></th>
				
				<th style="width: 50px;" class="center"><?php echo lang('Shutdown Device')?></th>
				
			</tr></thead>
			<tbody>
			<?php foreach($rows AS $k=>$it):?>
			<tr class="<?php echo ($k%2)?'old':'even'?>">
				<td><?php echo ($it->name !='' )?$it->name:'viettracker-'.base64_encode($it->id);?></td>
				<td><?php echo $it->plate_number?></td>
				<td class="nowrap-50" ><?php echo ($it->imei !='' )?$it->imei:md5($it->id)?></td>
				<td class="center"><?php echo $this->mapgps->printDate($it->created)?></td>
				<td class="center"><?php echo $this->mapgps->printDate($it->expiry)?></td>
				<td class="center"><button class="icon_only item-publish" ></button><?php // echo lang('status')?></td>
				<td class="center" >
					<?php if($it->permission == 'owner'):?>
					<input type="button" class="icon_only item-edit" title="<?php echo lang('Edit Data');?>" >
					<?php endif;?>
					<input type="button" class="icon_only item-tracking" title="Tracking" >
					<input type="button" class="icon_only item-report" title="<?php echo lang('View Report');?>" >
					<input type="button" class="icon_only item-history" title="<?php echo lang('View History');?>" >
					<input type="hidden" name="<?php echo $this->form->protection('imei')?>" value="<?php echo mortorID($it->id);?>" >
				</td>
				
				<td class="center" >
					<?php if($it->permission == 'owner'):?>
					<?php if($it->shutdown == 0):?>
						<input type="button" class="icon_only item-shutdown" title="<?php echo lang('Shutdown Device');?>" >
					<?php else:?>
						<input type="button" class="icon_only item-turnon" title="<?php echo lang('Turn On Device');?>" >
					<?php endif;?>
						<input type="hidden" name="<?php echo $this->form->protection('imei')?>" value="<?php echo mortorID($it->id);?>" >
					<?php endif;?>
				</td>
			</tr>
			<?php endforeach;?>
			</tbody>
			<tfoot></tfoot>
		</table>
		<form action="<?php echo site_url('quan-ly/sua-thiet-bi')?>" method="post" name="update-vehicle" >
			<input type="hidden" name="<?php echo $this->form->protection('imei')?>" value="" >
			<?php echo $this->form->inputToken();?>
		</form>
	</div>
	</div>
</div>
