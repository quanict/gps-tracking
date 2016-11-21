<div class="content-box block-right">
	<div class="box-body">
		<div class="box-header clear">
		<h2 class="fl" ><?php echo $this->mapgps->pageTitle();?></h2>
		</div>
	</div>
	<div class="box-wrap clear">
	
	<div class="talbe-view dataTables_wrapper">
		<?php 
		if($this->msg){
			echo $this->form->notification($this->msg);
		}
			echo $this->form->build($form,'Save');
		?>
	</div>
	</div>
</div>
