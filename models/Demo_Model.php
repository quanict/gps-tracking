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

	public function node_stop($limitF=0,$limitTo=5,$order='',$where=''){

	    $vehicleID = 0;

	    $dataReturn=array();

	    if(!isset($where['vehicleID'])){
	        return $dataReturn;
	    } else {
	        $vehicleID = $where['vehicleID'];

            $nTable = $this->db->dbprefix("motor".ABS($vehicleID));
            unset($where['vehicleID']);
	    }


	    $nSelect = 'node1.*';
	    $nFrom = $nTable.' AS node1';
	    $nWhere = array(
	        'node1.longitude <'=>180,'node1.longitude >'=>-180,'node1.latitude <'=>90,'node1.latitude >'=>-90,'SPEED'=>0,
	        '( SELECT node2.`SPEED` FROM (`'.$nTable.'` AS node2) WHERE node2.`POINTS` =  (node1.`POINTS` - 1)  ) = '=>0,
	        '( SELECT node3.`SPEED` FROM (`'.$nTable.'` AS node3) WHERE node3.`POINTS` =  (node1.`POINTS` - 2)  ) = '=>0,
	        '( SELECT node4.`SPEED` FROM (`'.$nTable.'` AS node4) WHERE node4.`POINTS` =  (node1.`POINTS` - 3)  ) > '=>0
	    );
	    $order = 'POINTS ASC';
	    if(is_array($where)){
	        foreach($where AS $key=>$item)
	            $nWhere[$key]=$item;
	    }

	    $this->db->select($nSelect)->from($nFrom)->where($nWhere)->order_by($order);
	    if($limitTo > 0 ){
	        $query = $this->db->limit($limitTo,$limitF)->get();
	    } else {
	        $query = $this->db->get();
	    }
	    if( !is_object($query) ){
	        bug($this->db->last_query());die;
	    } else {
	        $data = $query->result();
	    }


	    $dataReturn['data']=array();

	    foreach($data AS $key=>$v){
	        $address = $this->mapgps->geocode($v->La.','.$v->Lon);
	        $stopTime = ($vehicleID)?self::calculaStopTime($vehicleID,$v->POINTS):0;
	        $dataReturn['data'][] = array(date("d/m/Y H:i:s", strtotime($v->TIMESERVER) ) , $v->GPSLEVEL,$v->GsmLEVEL,$v->VAQ,$stopTime,'<div class="node_stop" latlng="'.$this->mapgps->shortDegrees($v->La).','.$this->mapgps->shortDegrees($v->Lon).'" >'.$address.'</div>' );

	    }
	    $dataReturn['totalRecords']= $this->db->select($nSelect)->from($nFrom)->where($nWhere)->count_all_results();
	    return $dataReturn;
	}
}