<?php
/* Smarty version 3.1.28-dev/73, created on 2016-11-21 20:43:54
  from "D:\PHP-www\Quannh\gps.giaiphapict.com\views\layouts\Login.htm" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.28-dev/73',
  'unifunc' => 'content_58334e7ae9bd34_36209668',
  'file_dependency' => 
  array (
    '1797959409228f9f53684e375ede314ec148598e' => 
    array (
      0 => 'D:\\PHP-www\\Quannh\\gps.giaiphapict.com\\views\\layouts\\Login.htm',
      1 => 1479757433,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_58334e7ae9bd34_36209668 ($_smarty_tpl) {
if (!is_callable('smarty_function_assets')) require_once 'D:\\PHP-www\\Quannh\\CodeIgniter-3.0.6\\system\\third_party\\Smarty_3\\ci\\function.assets.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php echo smarty_function_assets(array('type'=>'css'),$_smarty_tpl);?>
 <?php echo smarty_function_assets(array('type'=>'js'),$_smarty_tpl);?>

</head>
<body>

	<div id="preloader">
		<div id="status">&nbsp;</div>
	</div>
	<div class="container">
		<?php echo $_smarty_tpl->tpl_vars['_body']->value;?>


		<div style="text-align: center; margin: 0 auto;">
			<h6 style="color: #fff;">GSP Tracking 1.0.0.1 Powered by © ICT
				2014</h6>
		</div>

	</div>
	<div id="test1" class="gmap3"></div>



	<!--  END OF PAPER WRAP -->





	<?php echo '<script'; ?>
 type="text/javascript">
	$(function() {

	    $("#test1")
		    .gmap3(
			    {
				marker : {
				    latLng : [ -7.782893, 110.402645 ],
				    options : {
					draggable : true
				    },
				    events : {
					dragend : function(marker) {
					    $(this)
						    .gmap3(
							    {
								getaddress : {
								    latLng : marker
									    .getPosition(),
								    callback : function(
									    results) {
									var map = $(
										this)
										.gmap3(
											"get"), infowindow = $(
										this)
										.gmap3(
											{
											    get : "infowindow"
											}), content = results
										&& results[1] ? results
										&& results[1].formatted_address
										: "no address";
									if (infowindow) {
									    infowindow
										    .open(
											    map,
											    marker);
									    infowindow
										    .setContent(content);
									} else {
									    $(
										    this)
										    .gmap3(
											    {
												infowindow : {
												    anchor : marker,
												    options : {
													content : content
												    }
												}
											    });
									}
								    }
								}
							    });
					}
				    }
				},
				map : {
				    options : {
					zoom : 15
				    }
				}
			    });

	});
    <?php echo '</script'; ?>
>
</body>
</html><?php }
}
