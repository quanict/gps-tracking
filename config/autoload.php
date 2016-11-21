<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// $autoload['packages'] = array();
$autoload['libraries'] = array(
'Session/Session',
    //'form',
    'mapgps',
//'user_agent'

);

$autoload['helper'] = array('url','html','date','language','text','vtekgps','json');
$autoload['config'] = array('viettracker');
$autoload['language'] = array('gps');
$autoload['model'] = array('Account_Model','Vehicle_Model');
