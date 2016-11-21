

<div class="content-box block-right">
	<div class="box-body">
		<div class="box-header clear">
		<h2 class="fl" ><?php echo lang('Account Info')?></h2>
		</div>
	</div>
	<div class="box-wrap clear">
	<div class="talbe-view dataTables_wrapper">
		<div class="account-info" >
		<?php echo $this->form->build($fields,null); ?>
		<div class="button_bar clearfix">
			<?php echo $this->form->button('Change Info','button',' class="change-info ui-button" ');?></div>
		</div>
	</div>
	</div>
</div>

<script>
$(function() {
	$('button.change-info').click(function(){
		window.location.href = 'tai-khoan/sua-thong-tin.html';

	});
});
</script>
