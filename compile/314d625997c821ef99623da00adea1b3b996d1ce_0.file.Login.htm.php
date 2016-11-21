<?php
/* Smarty version 3.1.28-dev/73, created on 2016-11-21 19:23:42
  from "D:\PHP-www\Quannh\gps.giaiphapict.com\views\pages\Login.htm" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.28-dev/73',
  'unifunc' => 'content_58333baead8471_71113229',
  'file_dependency' => 
  array (
    '314d625997c821ef99623da00adea1b3b996d1ce' => 
    array (
      0 => 'D:\\PHP-www\\Quannh\\gps.giaiphapict.com\\views\\pages\\Login.htm',
      1 => 1479752491,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_58333baead8471_71113229 ($_smarty_tpl) {
?>
<div class="" id="login-wrapper">
	<div class="row">
		<div class="col-md-4 col-md-offset-4">
			<div id="logo-login">
				<h1>
					GST Tracking <span>demo</span>
				</h1>
			</div>
		</div>

	</div>

	<div class="row">
		<div class="col-md-4 col-md-offset-4">
			<div class="account-box">

				<form autocomplete="off" method="post" action="" role="form" >
					<div class="form-group">
						<a href="#" class="pull-right label-forgot">Forgot email?</a> <label
							for="inputUsernameEmail">Username or email</label>
							<input
							type="text" id="inputUsernameEmail" class="form-control" name="username" >
					</div>
					<div class="form-group">
						<a href="#" class="pull-right label-forgot">Forgot password?</a> <label
							for="inputPassword">Password</label>
							<input type="password" id="inputPassword" class="form-control" name="password" >
					</div>
					<div class="checkbox pull-left">
						<label> <input type="checkbox">Remember me
						</label>
					</div>
					<?php echo apricot_ui::inputToken(array(),$_smarty_tpl);?>

					<button class="btn btn btn-primary pull-right" type="submit">
						Log In</button>
				</form>
				

				<div class="row-block">
					<div class="row">
						<div class="col-md-12 row-block">
							
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>





<?php }
}
