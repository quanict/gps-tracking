<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class System_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->sys = $this->load->database('mapgps',true);
	}
	public function getConfig(){
		$this->sys->select('*')->from('config');
		$data = $this->sys->get()->result();
		$return = array();
		if($data){
			foreach($data AS $v){
				$return[$v->alias]=$v->value;
			}
		}
		return (object) $return;
	}
	
	public function send_contact($data){
		$data['created']=date("Y-m-d H:i:s");
		$query['run']  = $this->sys->insert('contact', $data);
		return $this->sys->insert_id();
	}
	public function order($data){
		return true;
		$data['created']=date("Y-m-d H:i:s");
// 		bug($data);exit;
		$query['run']  = $this->sys->insert('order', $data);
		return $this->sys->insert_id();
	}
}