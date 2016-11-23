<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$route['default_controller'] = "account/login";
$route['404_override'] = '';

$route+=array(
	'dang-nhap'=>'account/login',
	'dang-xuat'=>'account/logout',
	'tai-khoan'=>'account',
	'tai-khoan/sua-thong-tin'=>'account/changeInfo',
	'tai-khoan/doi-mat-khau'=>'account/changePassword',

	'thiet-bi'=>'account/manager',
	'quan-ly/(:any)'=>'account/manager',
);
// $route['dang-nhap'] = 'account/login';
// $route['dang-xuat'] = 'account/logout';

// $route['tai-khoan'] = "account";
// $route['tai-khoan/sua-thong-tin'] = "account/changeInfo";
// $route['tai-khoan/doi-mat-khau'] = "account/changePassword";


// $route['quan-ly/thiet-bi'] = "manager/vehicle";
// $route['quan-ly/sua-thiet-bi'] = "manager/updatevehicle";
// $route['quan-ly/tat-thiet-bi'] = "manager/shutdownVehicle";
// $route['quan-ly/mo-thiet-bi'] = "manager/turnOnVehicle";
// $route['tracking'] = "track/tracking";
// $route['lich-su']='track/history';
// $route['lich-su/toa-do']='track/node';

//$route['du-lieu/lich-su/(:num)/(:any)'] = 'resouce/playback/$1/$2';
// $route['du-lieu/lich-su'] = 'resouce/playback';
$route+=array(
	'ban-do'=>'vehicle/geocoding',
	'toa-do/(:any)'=>'vehicle/nodeRow',

	'theo-doi'=>'vehicle/trackall',
	'theo-doi/(:any)'=>'vehicle/trackone',

	'lich-su'=>'vehicle/history',
	'lich-su/(:any)'=>'vehicle/history',

	'thong-ke'=>'Report',
	'thong-ke/(:any)'=>'Report',
    'thong-ke/(:any)/(:any)'=>'Report/report_data_json/$2',




);
// $route['ban-do'] = 'track/geocoding';
