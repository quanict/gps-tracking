<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Data extends MX_Controller {
	function Data(){

	    parent::__construct();
	    $this->mapgps->checkLogin();
	    $this->load->module('layouts');
	    $this->template->set_theme('viettracker')->set_layout('vietracker');

	    $this->load->library('crawler/simple_html_dom');

	    $this->db = $this->load->database('nodedemo',true);
	}

	function import(){
        $file = FCPATH.'Xe SH H.anh.kml';
	    $html = file_get_html($file);
	    foreach ($html->find('Placemark') AS $note){
	        $styleUrl = $note->find('styleUrl',0)->innertext;
	        if( $styleUrl == '#start' OR $styleUrl =='#end' ){
	            $time = strtotime($note->find('TimeStamp when',0)->innertext);
	            list($lon,$lat,$height) = explode(",", $note->find('coordinates',0)->innertext);
	            $data = array(
	                'latitude'=>$lat,
	                'longitude'=>$lon,
	                'height'=>ABS($height),
	                'TIMESERVER'=>date( 'Y-m-d H:i:s', $time )
	            );
	            $this->db->insert('motor1',$data);
	        } elseif( $styleUrl == '#track') {
                foreach ($note->find('gx:coord') AS $k=>$gn){
                    list($lon,$lat,$height) = explode(" ", $gn->innertext);
                    $time = strtotime($note->find('when',$k)->innertext);

                    $data = array(
                        'latitude'=>$lat,
                        'longitude'=>$lon,
                        'height'=>ABS($height),
                        'TIMESERVER'=>date( 'Y-m-d H:i:s', $time )
                    );
                    $this->db->insert('motor1',$data);
//                     bug($data);die;
                }
	        }


// 	        if( $styleUrl=='#track' ){

// 	        }


	    }
	    die('imiport');
	}
}