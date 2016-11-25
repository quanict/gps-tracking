<?php
function loagMotor(){
	$CI =& get_instance();

// 	$CI->head_script.='var assets_url = "'.subdomain('assets_url').'";'
// 					.'var site_url = "'.site_url().'";'
// 					.'var token ="'.$CI->form->protection().'";'
// 					;

// 	exit('call me');
// 	$config = $CI->System_Model->getConfig();
// 	if($config){
// 		foreach ($config AS $k => $v){
// 			$CI->config->set_item($k, $v);
// 		}
// 	}

	if( $CI->session->userdata('uid') ){
		$CI->acountLogined = $CI->mapgps->userInfo();
	}

	$script = ''
			.'vmap.zoom = '.$CI->config->item('gps_zoom').';'
			.'tracking.center = true;'
			.'vmap.markerInfo = new google.maps.InfoWindow();'
			.'vmap.refresh = '.$CI->config->item('refresh-time').';'
			.'vmap.timestamp = '.(strtotime('now')*1000).';'
			."vmap.token = {'name':'".config_item('csrf_token_name')."','val':'".$CI->security->get_csrf_hash()."'};"

	;
// 	$script.= "site_url='".site_url()."';";
// 	$script.= "assets_url='".site_url()."';";

	add_js_header($script);


    $motors = $CI->Vehicle_Model->loadVehicles($CI->session->userdata('uid'));
    $CI->smarty->assign('motors', $motors);


}

function load_smartys(){
    $ci = get_instance();
    if( isset($ci->smarty) ){
        $smarty = $ci->smarty;
        $userinfo = $ci->Account_Model->userInfo($ci->session->userdata('uid'));
        if( is_object($userinfo) && isset($userinfo->username) ){
            $smarty->assign('username', $userinfo->username);
        }


        get_instance()->load->library('smarty_func');
        $lib_name = 'Smarty_func';
        foreach (get_class_methods($lib_name) AS $plugin){
	        if( $plugin !='__construct' && !isset($smarty->registered_plugins['function'][$plugin]) ){
	            $smarty->registerPlugin('function', $plugin, "$lib_name::".$plugin);
	        }

	    }


	    $script = ''
	        .'vmap.zoom = '.$ci->config->item('gps_zoom').';'
	            .'tracking.center = true;'
	                .'vmap.markerInfo = new google.maps.InfoWindow();'
	                    .'vmap.refresh = '.$ci->config->item('refresh-time').';'
	                        .'vmap.timestamp = '.(strtotime('now')*1000).';'
	                            ."vmap.token = {'name':'".config_item('csrf_token_name')."','val':'".$ci->security->get_csrf_hash()."'};"

	                                ;
	                                add_js_header($script);
    }
}
