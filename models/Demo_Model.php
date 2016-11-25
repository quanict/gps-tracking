<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Demo_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->db = $this->load->database('nodedemo',true);
	}

	function report($vid){
	    $vid = abs($vid);


	    $this->db->select('*')->from("motor$vid");

	    $this->db->where('id <',1119);
	    $this->db->order_by('id DESC ');

	    $this->Report_Model->vehicle = $this->Vehicle_Model->getInfo($vid);
	    return $this->Report_Model->report_nodes($this->db->get()->result(),'day', FALSE );
	}
}