<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['base_url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$config['base_url'] .= "://".$_SERVER['HTTP_HOST'];



$config['cache_path'] = '';

$config['encryption_key'] = 'c248360f4273f2a2f2557fc04b7a6cf5'; //mapgps

//$config['sess_cookie_name']		= 'dfd608aa415c2abc5b521e3748181bce'; //mapgps-session
$config['sess_cookie_name']		= 'dfd608aa4'; //mapgps-session
$config['sess_expiration']		= 0;
$config['sess_expire_on_close']	= FALSE;
$config['sess_encrypt_cookie']	= TRUE;
$config['sess_use_database']	= TRUE;
$config['sess_table_name']		= 'mapgps_5ea6be9e3ff1bd4b43c340827449c607'; //mapgps-table
$config['sess_match_ip']		= FALSE;
$config['sess_match_useragent']	= FALSE;
$config['sess_time_to_update']	= 300;

$config['cookie_prefix']	= "";
$config['cookie_domain']	= "";
$config['cookie_path']		= "/";
$config['cookie_secure']	= FALSE;

$config['global_xss_filtering'] = FALSE;

$config['csrf_protection'] = FALSE;
$config['csrf_token_name'] = '616493c4bf1ea9348e6cdded4e88549a'; //mapgps-token
$config['csrf_cookie_name'] = 'c2c42055e931f811bdd794f5bf5542a5'; //mapgps-cookie
$config['csrf_expire'] = 7200;

$config['compress_output'] = FALSE;

$config['time_reference'] = 'gmt';

$config['rewrite_short_tags'] = FALSE;

$config['proxy_ips'] = '';
$config['language']	= 'vi';
$config['date_format'] = 'd-m-Y';
$config['enable_hooks'] = TRUE;

