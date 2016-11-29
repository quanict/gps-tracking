<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class History_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->dv = $this->load->database('mapgps',true);
		$this->node = $this->load->database('node',true);
		$this->demo = $this->load->database('nodedemo',true);
		$this->car = $this->load->database('car',true);
		$this->db = $this->load->database('nodedemo',true);
	}

	public function loadNodeByTime($vehicleID,$time, $end=null){
	    // 		exit('get data history');
	    $table = $this->Vehicle_Model->checkDatabaseGPS($vehicleID);
	    if( $table=='demo' ){
	        return $this->loadNodeByTime_demo($vehicleID, $time,$end);
	    }
	    if($table === FALSE ) return null;

	    $selectField = 'POINTS AS id, La AS la, Lon AS lo, TIMESERVER AS t, SPEED AS speed, GPSLEVEL AS gps, GsmLEVEL AS gsm';

	    if($table=='car'){
	        $this->$table->select($selectField)->from("data".intval(abs($vehicleID)-config('carSpace')));
	    } else {
	        $this->$table->select($selectField)->from("data$vehicleID");
	    }

	    if($end && $time ){
	        $this->$table->where('(TIMESERVER) >=',date("Y-m-d H:i:s", $time ));
	        $this->$table->where('(TIMESERVER) <=',date("Y-m-d H:i:s", $end ));
	        // 			$this->db->where('TIMESERVER BETWEEN "'. date("Y-m-d H:i:s", $time ). '" and "'. date("Y-m-d H:i:s", $end ).'"');
	    } else if ($time){
	        $this->$table->where('YEAR(TIMESERVER)',date("Y", $time) );
	        $this->$table->where('MONTH(TIMESERVER)',date("m", $time) );
	        $this->$table->where('DAY(TIMESERVER)',date("d", $time) );
	        //			$this->node->where('(TIMESERVER)',date("Y-m-d", $time) );
	    }

	    $this->$table->where('lon <',180);
	    $this->$table->where('lon >',-180);
	    $this->$table->where('la <',90);
	    $this->$table->where('la >',-90);
	    $this->$table->where('speed >',0);
	    $this->$table->order_by('id ASC ');
	    // 		$this->$table->limit(99999999);
	    $data = $this->$table->get()->result();
	    // 		if(!$data){
	    // 			$data = self::getLastNode($vehicleID,array('La <'=> 90,'La >'=>-90,'Lon <'=> 180,'Lon >'=> -180));
	    // 		}
	    return $data;

	}

	function loadNodeByTime_demo($vehicleID, $time,$end=null){
	    $selectField = 'id, latitude AS la,  longitude AS lo, TIMESERVER AS t, SPEED AS speed, GPSLEVEL AS gps, GsmLEVEL AS gsm';


        $this->db->select($selectField)->from( "motor".ABS($vehicleID) );

        $time = strtotime('2013-05-05 08:00:00');
        $end = strtotime('2013-05-05 08:21:22');
	    if($end && $time ){
	        $this->db->where('(TIMESERVER) >=',date("Y-m-d H:i:s", $time ));
	        $this->db->where('(TIMESERVER) <=',date("Y-m-d H:i:s", $end ));
	        // 			$this->db->where('TIMESERVER BETWEEN "'. date("Y-m-d H:i:s", $time ). '" and "'. date("Y-m-d H:i:s", $end ).'"');
	    } else if ($time){
	        $this->db->where('YEAR(TIMESERVER)',date("Y", $time) );
	        $this->db->where('MONTH(TIMESERVER)',date("m", $time) );
	        $this->db->where('DAY(TIMESERVER)',date("d", $time) );
	    }

	    $this->db->where('longitude <',180);
	    $this->db->where('longitude >',-180);
	    $this->db->where('latitude <',90);
	    $this->db->where('latitude >',-90);
// 	    $this->db->where('speed >',0);
	    $this->db->order_by('id ASC ');
	    $query = $this->db->get();
// 	    if( !is_object($query) ){
// 	        bug($this->db->last_query());
// 	        die;
// 	    }
	    return $query->result();
	}
}