<?php
/* Smarty version 3.1.28-dev/73, created on 2016-11-22 00:26:45
  from "D:\PHP-www\Quannh\gps.giaiphapict.com\views\layouts\Vietracker.htm" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.28-dev/73',
  'unifunc' => 'content_583382b5efa3d3_09343102',
  'file_dependency' => 
  array (
    'e56e8de7ce39005eef8e7d9948a18082779f9231' => 
    array (
      0 => 'D:\\PHP-www\\Quannh\\gps.giaiphapict.com\\views\\layouts\\Vietracker.htm',
      1 => 1479770801,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_583382b5efa3d3_09343102 ($_smarty_tpl) {
if (!is_callable('smarty_function_site_url')) require_once 'D:\\PHP-www\\Quannh\\CodeIgniter-3.0.6\\system\\third_party\\Smarty_3\\ci\\function.site_url.php';
if (!is_callable('smarty_function_assets')) require_once 'D:\\PHP-www\\Quannh\\CodeIgniter-3.0.6\\system\\third_party\\Smarty_3\\ci\\function.assets.php';
if (!is_callable('smarty_function_config')) require_once 'D:\\PHP-www\\Quannh\\CodeIgniter-3.0.6\\system\\third_party\\Smarty_3\\ci\\function.config.php';
if (!is_callable('smarty_function_anchor')) require_once 'D:\\PHP-www\\Quannh\\CodeIgniter-3.0.6\\system\\third_party\\Smarty_3\\ci\\function.anchor.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title></title>
<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php echo '<script'; ?>
 type="text/javascript">

	var assets_url = "http://as1.viettracker.vn";
	var site_url = "<?php echo smarty_function_site_url(array(),$_smarty_tpl);?>
";
	var token = "bWFwZ3Bz";
    <?php echo '</script'; ?>
>
	<?php echo smarty_function_assets(array('type'=>'css'),$_smarty_tpl);?>

	<?php echo smarty_function_assets(array('type'=>'js'),$_smarty_tpl);?>

	<?php echo '<script'; ?>
 type="text/javascript">
		vmap.zoom = <?php echo smarty_function_config(array('item'=>'gps_zoom'),$_smarty_tpl);?>
;
		vmap.refresh = '<?php echo smarty_function_config(array('item'=>"refresh-time"),$_smarty_tpl);?>
';
		vmap.timestamp = '<?php echo strtotime("now")*1000;?>
';
		
		tracking.center = true;
		vmap.markerInfo = new google.maps.InfoWindow();
		$(function() {
		    $("#gps-vehicles").dropdownchecklist({ firstItemChecksAll: true, icon: {}, width: 250});
		});

	<?php echo '</script'; ?>
>
</head>
<body>
	<div class="header wrappage">
		

		<a href="#" class="logo" target="_blank">GPS Tracking<span>demo</span></a>
		<div class="menu">
			<ul>

				<li><?php echo smarty_function_anchor(array('uri'=>'ban-do','txt'=>'Map'),$_smarty_tpl);?>
</li>
				<li><?php echo smarty_function_anchor(array('uri'=>'theo-doi','txt'=>'Position'),$_smarty_tpl);?>
</li>
				<li><?php echo smarty_function_anchor(array('uri'=>'lich-su','txt'=>'History'),$_smarty_tpl);?>
</li>
				<li><?php echo smarty_function_anchor(array('uri'=>'thong-ke','txt'=>'Report'),$_smarty_tpl);?>
</li>
				<li><?php echo smarty_function_anchor(array('uri'=>'thiet-bi','txt'=>'Manager'),$_smarty_tpl);?>
</li>
			</ul>
		</div>
		<div class="account">
			<ul>
				<li><span>Xin Chào: </span><?php echo smarty_function_anchor(array('uri'=>'tai-khoan','txt'=>$_smarty_tpl->tpl_vars['username']->value),$_smarty_tpl);?>
</li>
				<li><?php echo smarty_function_anchor(array('uri'=>'','txt'=>'User Guide'),$_smarty_tpl);?>
</li>
				<li><?php echo smarty_function_anchor(array('uri'=>'dang-xuat','txt'=>'Logout'),$_smarty_tpl);?>
</li>
			</ul>
		</div>
	</div>

	<?php if (isset($_smarty_tpl->tpl_vars['msgq']->value)) {?>
	<div id="mini-notification">
		<p><?php echo $_smarty_tpl->tpl_vars['msgq']->value;?>
</p>
	</div>
	<?php }?>

	<div class="wrappage">
		<div class='clearfix wrappage container'>
			<div class="gps-tool clearfix">
				<div id="vehicle-info"></div>
				<div class="vehicles crfloat ">
					<span style="display: inline-block; padding: 0 5px;">Chọn Phương Tiện</span>
					<select id="gps-vehicles"  multiple="multiple" >
						<option value="*">(Tất Cả Thiết Bị)</option>
						<?php if (isset($_smarty_tpl->tpl_vars['motors']->value) && count($_smarty_tpl->tpl_vars['motors']->value) > 0) {
$_from = $_smarty_tpl->tpl_vars['motors']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$__foreach_m_0_saved_item = isset($_smarty_tpl->tpl_vars['m']) ? $_smarty_tpl->tpl_vars['m'] : false;
$_smarty_tpl->tpl_vars['m'] = new Smarty_Variable();
$__foreach_m_0_total = $_smarty_tpl->smarty->ext->_foreach->count($_from);
if ($__foreach_m_0_total) {
foreach ($_from as $_smarty_tpl->tpl_vars['m']->value) {
$__foreach_m_0_saved_local_item = $_smarty_tpl->tpl_vars['m'];
?>
						<option value="<?php echo $_smarty_tpl->tpl_vars['m']->value->sid;?>
" ><?php echo $_smarty_tpl->tpl_vars['m']->value->name;?>
</option>
						<?php
$_smarty_tpl->tpl_vars['m'] = $__foreach_m_0_saved_local_item;
}
}
if ($__foreach_m_0_saved_item) {
$_smarty_tpl->tpl_vars['m'] = $__foreach_m_0_saved_item;
}
}?>

					</select>


				</div>
			</div>
			<?php echo $_smarty_tpl->tpl_vars['_body']->value;?>


		</div>
	</div>
	<div class="ajax-modal"></div>
</body>
</html><?php }
}
