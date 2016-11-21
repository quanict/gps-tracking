<?php

include APPPATH.'/config/config.php';

// bug($config);
// bug('call me'); exit;
$template['active_template'] = 'default';
$template['default']['template'] = 'template/default';
$template['default']['regions'] = array(
	'title','header','content','scripts','scripts_code',
	'_scripts'=>array(
		'http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js',
		'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js',
		//'http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js',
// 		'jquery-ui-1.9.2/js/jquery-1.8.3.js',
// 		'http://maps.google.com/maps/api/js?sensor=true&language=vi&key=AIzaSyBgL0Y10tcoftbhc4GK2DCBQcy3_oSNbRs',
		'http://maps.google.com/maps/api/js?sensor=true&language=vi',
// 		'mapgps.js',
// 		'jquery-ui-1.9.2/jquery-ui.js',
		'gps.js',
// 		'gps.2.0.js'
		'js/2.1/i18n.js',
		'js/2.1/mapgps.js',
		'js/2.1/gps.js',
		'js/2.1/playback.js',
		'js/2.1/report.js',
		'js/2.1/tracking.js',
// 		$config['base_url'].'/assets/js/gps.js',

	),
	'_styles'=>array(
			'gps.2.0.css'
// 		'terminator/css/datatable.css',
// 		'terminator/css/block.css',
// 		'jquery-ui-1.9.2/css/cupertino/jquery-ui-1.9.2.custom.css',
// 		'dropdown-check-list/ui.dropdownchecklist.themeroller.css',
// // 		$config['base_url'].'/assets/css/mapgps.css',
// 		'',
// 		'plugin/timepicker-addon/jquery-ui-timepicker-addon.css'
	),
);

// $template['column55']['template'] = 'template/column55';
// $template['column55']['regions'] = array_merge(
// 		$template['default']['regions'],
// 		array('rightcontent','leftcontent')
// );
$template['login']['template'] = 'template/login';
$template['login']['regions'] = $template['default']['regions'];

// bug($template);exit;
