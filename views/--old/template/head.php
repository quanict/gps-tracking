<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="INDEX,FOLLOW" />
<title><?php echo ( ( isset($this->title) && $this->title )?$this->title.' - '.config('site_title'):config('home_title') );?></title>
<link href="<?php echo img_resoure('satellite.ico')?>"
	rel="shortcut icon">
<meta name="description"
	content="<?php echo config('site_description')?>" />
<?php
if (config('keywords') != '') {
    echo meta('keywords', keywords(config('keywords')));
}
// bug($config['keywords']);
?>
<?php if( isset($this->head_script) ) echo '<script type="text/javascript">'.$this->head_script.'</script>';?>
<?php if(isset($_scripts)) echo ($_scripts)?>
<?php if(isset($_styles)) echo  $_styles?>
<?php if(isset($_style_code)) echo $_style_code;?>
<script type="text/javascript"><?php if(isset($scripts)) echo $scripts; if( isset($scripts_ready) ) echo $scripts_ready;?>;</script>
<!--[if lt IE 9]>
  <script type="text/javascript" src="http://as1.viettracker.vn/libraries/pie/PIE_IE678.js"></script>
<![endif]-->
<!--[if IE 9]>
  <script type="text/javascript" src="http://as1.viettracker.vn/libraries/pie/PIE_IE9.js"></script>
<![endif]-->

