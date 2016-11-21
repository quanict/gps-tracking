<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en">
<?php

if(class_exists('CI_Controller')){
	$CI =& get_instance();

$template = $CI->template->regions;
$_scripts = $_styles = '';

if( isset($template['_scripts']) ){
	//foreach($template['_scripts'] AS $f){
	//$_scripts.=$CI->template->_build_assets('scripts',$template['_scripts']);;
	//}
}
if( isset($template['_styles']) ){
	//foreach($template['_scripts'] AS $f){
	$_styles.=$CI->template->_build_assets('css',$template['_styles']);;
	//}
}
// print_r($template);
$title = 'page not found';
// echo 'FCPATH='.FCPATH.APPPATH; exit;
?>

<head><?php include_once APPPATH.'views/template/head.php'; ?></head>
<body>
<div class="header wrappage" >
	<div class="logo" ></div>
	<div class="menu" >
		<ul>
			<li><?php echo anchor('ban-do','Bản Đồ')?></li>
			<li><?php echo anchor('theo-doi','Vị Trí')?></li>
			
			<li><?php echo anchor('lich-su',$CI->lang->line('View History'))?></li>
			<li><?php echo anchor('thong-ke',$CI->lang->line('View Report'))?></li>
			<li><?php echo anchor('quan-ly/thiet-bi',$CI->lang->line('Account Management'))?></li>
		</ul>
	</div>
	<div class="account" >
		<ul>
			<li><?php echo $CI->lang->line('Wellcome').': '.anchor('tai-khoan',$CI->acountLogined->fullname) ;?></li>
			<li><?php echo anchor('#',$CI->lang->line('User Guide'))?></li>
			<li><?php echo anchor('dang-xuat',$CI->lang->line('Logout'))?></li>
		</ul>
	</div>
</div>
<div class="wrappage page404" >
	<div class="page404-content">
		<h1><?php echo $heading; ?></h1>
		<div class="page404-function"><?php echo $message; ?></div>
	</div>
</div>



<?php 
} else { ?>

<head>
<title>404 Page Not Found</title>
<style type="text/css">

::selection{ background-color: #E13300; color: white; }
::moz-selection{ background-color: #E13300; color: white; }
::webkit-selection{ background-color: #E13300; color: white; }

body {
	background-color: #fff;
	margin: 40px;
	font: 13px/20px normal Helvetica, Arial, sans-serif;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #444;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 19px;
	font-weight: normal;
	margin: 0 0 14px 0;
	padding: 14px 15px 10px 15px;
}

code {
	font-family: Consolas, Monaco, Courier New, Courier, monospace;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}

#container {
	margin: 10px;
	border: 1px solid #D0D0D0;
	-webkit-box-shadow: 0 0 8px #D0D0D0;
}

p {
	margin: 12px 15px 12px 15px;
}
</style>
</head>
<body>
	<div id="container">
		<h1><?php echo $heading; ?></h1>
		<?php echo $message; ?>
	</div>
</body>
<?php 
}
?>

</html>

	
