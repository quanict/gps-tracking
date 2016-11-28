<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Tracking_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->dv = $this->load->database('mapgps',true);
		$this->node = $this->load->database('node',true);
		$this->demo = $this->load->database('nodedemo',true);
		$this->car = $this->load->database('car',true);
	}

	public function getLastNode_demo($vid,$where=null){
	    $selectField = 'latitude AS la, longitude AS lo, TIMESERVER AS t, SPEED AS speed, GPSLEVEL AS gps, GsmLEVEL AS gsm, VAQ AS vaq, COURSE AS rotate, id';
	    $table = 'demo';

	    if( $table === FALSE )
	        return null;
	    $vid = abs($vid);

	    $vehicle = $this->Vehicle_Model->getVehicle($vid);

	    $this->$table->select($selectField)->from("motor".abs($vid));

	    if($where){
	        $this->$table->where($where);
	    } else {
	        /*
	         * only for demo
	         */

	        $this->$table->where('HOUR(TIMESERVER)',8 );
	        $this->$table->where('MINUTE(TIMESERVER)',date("i") );
	        $this->$table->where('SECOND(TIMESERVER)',date("s") );
	    }




	    $this->$table->order_by('id DESC')->limit(1);

	    $query = $this->$table->get();

	    if( !is_object($query) ){
            bug($this->$table->last_query());
            die;
	    }elseif( $query->num_rows() < 1 ){
	        return $this->getLastNode_demo($vid,array('HOUR(TIMESERVER)'=>8));
	    } else {
	        $data = $query->row();
	    }

	    if($data){
	        $data->vid = mortorID($vid);
	        $data->name = $vehicle->name;
	        $data->t = time();

	        if($data->la >= 90 || $data->la <=-90 || $data->lo >= 180 || $data->lo <= -180){
	            $data->correct = self::getLastNode($vid,array('La <'=> 90,'La >'=>-90,'Lon <'=> 180,'Lon >'=> -180) );
	            //$data->t = $data->correct->t;
	        }

	        $data->la = $this->mapgps->shortDegrees($data->la);
	        $data->lo = $this->mapgps->shortDegrees($data->lo);

	        $node_pre = $this->$table->where('id <',$data->id)->get("motor".abs($vid))->row();
	        $data->speed = distance($node_pre->latitude, $node_pre->longitude, $data->la, $data->lo,false)*3600;
	        $data->speed = round($data->speed,2);
	    } else {
	        $data = null;
	    }

	    // 		$data->t = strtotime('2013-6-8 17:00:09');
	    // 		$data->gsm = '98%';
	    //if($data){
	    // 	$data->speed = 54;
	    // 	$data->rotate = 85;
	    // 	$data->la = 21.032589;
	    // 	$data->lo = 105.772732;
	    //}
	    //$data->t = strtotime('2013-05-12 5:00:09');
	    return $data;
	}

}