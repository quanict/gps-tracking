<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head><?php $this->load->view('template/head');?></head>
<body>
	<div class="header wrappage"><?php echo anchor(config('home-url'),' ',' class="logo" target="_blank" ');?><div
			class="menu">
			<ul>
				<li
					<?php echo ($this->uri->segment(1)=='ban-do')?' class="selected" ':''; ?>><?php echo anchor('ban-do',lang('Map'))?></li>
				<li
					<?php echo ($this->uri->segment(1)=='theo-doi')?' class="selected" ':''; ?>><?php echo anchor('theo-doi',lang('Tracking'))?></li>
				<li
					<?php echo ($this->uri->segment(1)=='lich-su')?' class="selected" ':''; ?>><?php echo anchor('lich-su',lang('View History'))?></li>
				<li
					<?php echo ($this->uri->segment(1)=='thong-ke')?' class="selected" ':''; ?>><?php echo anchor('thong-ke',lang('View Report'))?></li>
				<li
					<?php echo ($this->uri->segment(1)=='quan-ly' || $this->uri->segment(1)=='tai-khoan')?' class="selected" ':''; ?>><?php echo anchor('quan-ly/thiet-bi',lang('Account Management'))?></li>
			</ul>
		</div>
		<div class="account">
			<ul>
				<li><?php echo '<span>'.lang('Wellcome').': </span>'.anchor('tai-khoan',$this->acountLogined->fullname) ;?></li>
				<li><?php echo anchor('http://viettracker.vn/giai-dap/huong-dan-tracking-xe-may.html',$this->lang->line('User Guide'),' target="_blank" ')?></li>
				<li><?php echo anchor('dang-xuat',$this->lang->line('Logout'))?></li>
			</ul>
		</div>
	</div>

<?php if( isset($this->msgq) ):?>
 <div id="mini-notification">
		<p><?php echo $this->msgq ;?></p>
	</div>

<?php endif;?>
<div class="wrappage"><?php  if ($content) echo "<div class='clearfix wrappage container' >".clearnSpace($content)."</div>"; ?></div>
	<div class="ajax-modal"></div>
</body>
</html>
