<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Tracking extends MX_Controller {
	function __construct(){
	    parent::__construct();
	    $this->mapgps->checkLogin();
	    $this->load->model('Tracking_Model');
	    $this->vstr = $this->uri->segment(2);
	    $this->vid = mortorID($this->vstr,true);
	    $this->color = config_item('color');

	    $this->load->module('layouts');
	    $this->template->set_theme('viettracker')->set_layout('vietracker');


	    $this->motors = $this->Vehicle_Model->loadVehicles($this->session->userdata('uid'));
	}

	public function items(){


	    if($this->url_suffix == 'json' OR $this->uri->extension =='json'){
	        $motorIDs= $this->input->get('vehicle');

	        $jsons = array();
	        $this->session->set_userdata(array('traking'=>$motorIDs));
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

	    $script = '';
	    foreach($this->motors AS $k=>$it){
	        $script .="tracking.polyline[".$it->id."] =  new google.maps.Polyline({ strokeColor: '#".$this->color[$k]."'}); $('#vehicle-info').css({'height':22,'padding-top':5});";
	    }
	    add_js_ready("vmap.ini(); $script tracking.ini(); vmap.autoLoad('tracking.track');");

	    $this->template->build('pages/trackall');

	}
}