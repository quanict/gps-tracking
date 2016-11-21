<?php defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'mapgps';
// $query_builder = TRUE;
$db['mapgps'] = array(
		'hostname'=>'localhost',
		'database'=>'giaiphapict_gps',
		'username'=>'root',
		'password'=>'',
		'dbdriver'=>'mysqli',
		'dbprefix'=>'mapgps_',
		'cache_on'=>true
);

$db['account'] = array(
    'hostname'=>'localhost',
    'database'=>'giaiphapict_gps',
    'username'=>'root',
    'password'=>'',
    'dbdriver'=>'mysqli',
    'dbprefix'=>'mapgps_account_',
    'cache_on'=>true
);
$db['node'] = array(
    'hostname'=>'localhost',
    'database'=>'giaiphapict_gps',
    'username'=>'root',
    'password'=>'',
    'dbdriver'=>'mysqli',
    'dbprefix'=>'',
    'cache_on'=>true
);
$db['nodedemo'] = array(
    'hostname'=>'localhost',
    'database'=>'giaiphapict_gps',
    'username'=>'root',
    'password'=>'',
    'dbdriver'=>'mysqli',
    'dbprefix'=>'',
    'cache_on'=>true
);

$db['car'] = array(
    'hostname'=>'localhost',
    'database'=>'giaiphapict_gps',
    'username'=>'root',
    'password'=>'',
    'dbdriver'=>'mysqli',
    'dbprefix'=>'',
    'cache_on'=>true
);

