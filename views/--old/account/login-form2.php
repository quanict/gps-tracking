
<h2><?php echo lang('Form Login GPS Motor')?></h2>
<div class="support" ><label> Hỗ trợ kỹ thuật:</label>04.62956875</br>0988.710.468</div>
<form autocomplete="off" method="post" action="" >
	
	<?php if(isset($fields['username'])):?>
	<fieldset><label class="icon25 icon25-user" ></label>
		<div><input type="text" class="field csspie" value="" placeholder="<?php echo $fields['username']['lable'];?>" aria-label="<?php echo $fields['username']['lable'];?>" name="<?php echo $this->form->protection('username')?>" autocomplete="off"></div>
	</fieldset>
	<?php endif;?>
	<?php if(isset($fields['password'])):?>
	<fieldset><label class="icon25 icon25-key" ></label>
		<div><input type="password" class="field csspie" value="" placeholder="<?php echo $fields['password']['lable'];?>" aria-label="<?php echo $fields['password']['lable'];?>" name="<?php echo $this->form->protection('password')?>" autocomplete="off"></div>
	</fieldset>
	<?php endif;?>
	<div class="clearfix button_bar"><input type="submit" value="Đăng nhập" class="csspie" ></div>
	<?php echo $this->form->inputToken()?>
</form>
<?php 
if($this->msg){
	echo $this->form->notification($this->msg);
}
// bug($fields);exit;
?>

