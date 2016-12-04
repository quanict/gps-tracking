<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Tracking extends MX_Controller {
	function __construct(){
	    parent::__construct();
	    $this->mapgps->checkLogin();
	    $this->load->model('Tracking_Model');
	    $this->vstr = $this->uri->segment(2);
	    $this->vid = mortorID($this->vstr,true);
	    $this->color = config_item('color');





	    $this->motors = $this->Vehicle_Model->loadVehicles($this->session->userdata('uid'));

	    $this->template->set_theme('viettracker')
	    ->set_layout('vietracker');
	}

	public function items(){

	    if( $this->uri->extension =='json'){
            return $this->get_nodes($this->input->get('vehicle'));
	    }

	    $script = '';
	    foreach($this->motors AS $k=>$it){
	        $script .="tracking.polyline[".$it->id."] =  new google.maps.Polyline({ strokeColor: '#".$this->color[$k]."'}); $('#vehicle-info').css({'height':22,'padding-top':5});";
	    }
	    add_js_ready("vmap.ini(); $script tracking.ini(); vmap.autoLoad('tracking.track');");


// 	    add_js('map/tracking.js');
	    $this->template->build('pages/trackall');

	}

	public function trackone(){
	    if( !$this->vid || $this->Vehicle_Model->checkDatabaseGPS($this->vid) === false ){
	        show_404();
	    } else if ($this->uri->extension =='json'){
	        $data = $this->Vehicle_Model->getLastNode($this->vid);
	        $data->timestamp = strtotime('now')*1000;
	        return jsonData($data);
	    }
	    $headScript =''
	        .'vmap.trackingLink = "'.base_url().'theo-doi/'.$this->vstr.'.json";'
	            //.'vmap.playBackLink = "'.site_url().'du-lieu/lich-su.kml?vehicle='.$motorID.'";'
	    .'vmap.ini();'
	        ."tracking.polyline[".$this->vid."] =  new google.maps.Polyline({ strokeColor: '#".$this->color[0]."'});"
	            .'tracking.trackingOneIni();'
	                //.'$("#gps-vehicles").val('.$this->vid.');'
	    .'$(".gmap-area").css({"margin-top":"10px"});'
	        //."$('#vehicle-info').css({'height':22,'padding-top':5});"
	    //.'vmap.tracking();'
	    //.'window.setInterval(function() { vmap.tracking();}, 5000);'
	    ;
	    add_js_ready($headScript);
// 	    $this->template->write('content', self::status('tracking',false));
	    $data['vehicle'] = $this->Vehicle_Model->getVehicle($this->vid);
// 	    $this->template->write_view('content', 'page/tracking_one',$data);
// 	    $this->template->render();
	    $this->template->build('pages/tracking_one',$data);
	}


	private function get_nodes($motorIDs=array()){
	    $this->session->set_userdata(array('traking'=>$motorIDs));

	    $jsons = array();
	    if( isset($motorIDs) &&  is_array($motorIDs) && count($motorIDs)>0){
	        foreach($motorIDs AS $vstr){
	            $vid = mortorID($vstr,true);
	            $db = $this->Vehicle_Model->checkDatabaseGPS($vid);

	            if( $db=='demo' ){
	                $jsons[] = $this->Tracking_Model->getLastNode_demo($vid);
	            } elseif($vid !='*' && $this->Vehicle_Model->checkOwnerGPS($vid) ){
	                $jsons[] = $this->Vehicle_Model->getLastNode($vid);
	            }
	        }
	    }

	    $jsons['timestamp'] = strtotime('now')*1000;
	    return jsonData($jsons);
	}
}