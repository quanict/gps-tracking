<div class="content-box block-right">
	<div class="box-body">
		<div class="box-header clear">
		<h2 class="fl" ><?php echo $this->mapgps->pageTitle();?></h2>
		</div>
	</div>
	<div class="box-wrap clear">
	<?php if($this->msg){
		echo $this->form->notification($this->msg);
	}?>
	<div class="talbe-view dataTables_wrapper">
		<div class="account-info" >
		<?php
			$button = (isset($buttons))?$buttons:null;  
			echo $this->form->build($fields,$button);
		?>
		</div>
	</div>
	</div>
</div>



