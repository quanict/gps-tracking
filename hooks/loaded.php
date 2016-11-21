<?php
function loagMotor(){
	$CI =& get_instance();

	$CI->head_script.='var assets_url = "'.subdomain('assets_url').'";'
					.'var site_url = "'.site_url().'";'
					.'var token ="'.$CI->form->protection().'";'
					;

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
			."vmap.token = {'name':'".config('csrf_token_name')."','val':'".$CI->security->get_csrf_hash()."'};"

	;
	$CI->template->add_js_ready($script);
	die('call me');
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

// 	bug($scriptFirst);


}

function fileTypeOut(){
	$CI =& get_instance();
// 	echo str_replace('?'.$_SERVER['QUERY_STRING'],null,$_SERVER['REQUEST_URI']) ;
	$fileType =  pathinfo( str_replace('?'.$_SERVER['QUERY_STRING'],null,$_SERVER['REQUEST_URI']),PATHINFO_EXTENSION );
	switch ($fileType){
		case 'json':
			$CI->url_suffix = 'json';
			if($CI->agent->browser() != 'Internet Explorer'){
				header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
				header('Content-type: text/json');
				header('Content-type: application/json');
				header('Content-Disposition: attachment; filename="downloaded.json"');
			}
			header('Content-Transfer-Encoding: binary');
			break;
		case 'kml':
		case 'xls': $CI->url_suffix = $fileType; break;
		default: $CI->url_suffix = 'html'; break;
	}

}