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
	add_js_header($script);
// 	$CI->template->add_js_ready($script);
// 	die('call me');
	$expired = $CI->Vehicle_Model->getExpiredRow();

	if ( isset($CI->acountLogined) && !$CI->acountLogined->phone ){
		$CI->msgq = 'Vui lÃ²ng nháº­p sá»‘ Ä‘iá»‡n thoáº¡i liÃªn há»‡, Ä‘á»ƒ chÃºng tÃ´i phá»¥c vá»¥ báº¡n tá»‘t hÆ¡n. <a href="tai-khoan/sua-thong-tin.html" target="_blank">Sá»­a thÃ´ng tin tÃ i khoáº£n</a>.';
	} else if( $expired ){
		$CI->msgq = 'Thiáº¿t bá»‹ <strong><?php echo $this->expired->name?></strong> sáº½ háº¿t háº¡n sá»­ dá»¥ng vÃ o ngÃ y <?php echo $this->expired->expiry?>. <a href="http://viettracker.vn/lien-he.html" target="_blank"> LiÃªn láº¡c vá»›i chÃºng tÃ´i Ä‘á»ƒ gia háº¡n thÃªm</a>.';
	}

	if( isset($CI->msgq) ){
		$CI->template->add_js('miniNotification.js');
		$CI->template->add_js_ready("$('#mini-notification').miniNotification({closeButton: true, closeButtonText: '[Ä�Ã³ng]',time: 50000});");
	}

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
