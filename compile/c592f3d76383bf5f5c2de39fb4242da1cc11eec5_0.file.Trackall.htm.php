<?php
/* Smarty version 3.1.28-dev/73, created on 2016-11-21 21:18:21
  from "D:\PHP-www\Quannh\gps.giaiphapict.com\views\pages\Trackall.htm" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.28-dev/73',
  'unifunc' => 'content_5833568d3d8797_40165734',
  'file_dependency' => 
  array (
    'c592f3d76383bf5f5c2de39fb4242da1cc11eec5' => 
    array (
      0 => 'D:\\PHP-www\\Quannh\\gps.giaiphapict.com\\views\\pages\\Trackall.htm',
      1 => 1479759498,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5833568d3d8797_40165734 ($_smarty_tpl) {
?>
<div class="gmap-area clearfix">
	<div id="gmap" style=""></div>
	<div id="gmap-status"></div>
	<div id="gmap-address" lo="" la=""></div>
</div>
<?php echo '<script'; ?>
>
$( document ).ready(function() {
    <?php echo $_smarty_tpl->tpl_vars['js_ready']->value;?>

});
<?php echo '</script'; ?>
><?php }
}
