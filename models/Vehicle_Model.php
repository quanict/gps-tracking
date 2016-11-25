<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vehicle_Model extends CI_Model {
	var $CI;
	var $vehicle;
	function __construct(){
		parent::__construct();
		$this->dv = $this->load->database('mapgps',true);
		$this->node = $this->load->database('node',true);
		$this->demo = $this->load->database('nodedemo',true);
		$this->car = $this->load->database('car',true);
		$this->CI =& get_instance();
	}

	function getExpiredRow(){
		if( $this->session->userdata('uid') ){
			$query = "SELECT name, expiry FROM (`mapgps_motor`) WHERE `expiry` > NOW() AND `expiry` <= (NOW() + INTERVAL 1 MONTH)  AND `owner` = " .$this->session->userdata('uid')." ORDER BY rand() LIMIT 1";
			;

			$data = $this->dv->query($query)->row();
			if( $data ){
				return $data;
			}
		}

		return NULL;
	}

	public function checkDatabaseGPS($vid=0){

		if( $this->dv->select('id')->from('motor')->where(array('id'=>$vid))->limit(1)->get()->row() ){
		    $carSpace = config_item('carSpace');

			if( $vid < $carSpace && $vid > 0 && $this->node->table_exists( "data".$vid )){
				return 'node';
			} else if ( $vid < 0 && $this->demo->table_exists( "demo_motor".abs($vid) ) ){
				return 'demo';
			} else if( $vid >= $carSpace  && $this->car->table_exists("data".abs($vid-$carSpace)) ){
				$this->CI->vehicleType = 'car';
				return 'car';
			}
			return FALSE;
		}

		return FALSE;
	}

	function distance($lat1, $lng1, $lat2, $lng2, $miles = true) {
		$dlat = ($lat2-$lat1);
		$dlng = ($lng2-$lng1);
		if($dlat != 0  && $dlng !=0 ){
			if($dlat==0){
				bug('$dlat='.$dlat);
			}
			if($dlng==0){
				bug('$dlat='.$dlng);
			}
			$km = sqrt( $dlng*$dlng + $dlat*$dlat ) *110;
		}
		if( !isset($km) || $km == 'NAN'){
			$km = 0;
		}
		return ($miles ? ($km) : $km/1000);
	}

	public function checkOwnerGPS($vid=0){
		if( self::checkDatabaseGPS($vid) != FALSE ){

			if( $this->dv->select('*')->from('motor_tracking')->where( array('taget'=>$vid,'owner'=>$this->session->userdata('uid')) )->count_all_results() > 0 ){
				return TRUE;
			}
			return FALSE;
		}
		return FALSE;
	}

	public function loadVehicles($uid){
		$this->dv->select('*')->from('motor AS dv');
		$this->dv->where('dv.owner',$uid);
		$this->dv->where('dv.status',1);
		$this->dv->where('dv.type',1);
		$data =  $this->dv->get()->result();

		if($data){
			foreach($data AS $k=>$item){
				$data[$k]->name = ($item->name !='' )?$item->name:'viettracker-'.base64_encode($item->id);
				$data[$k]->sid = mortorID($item->id);
			}
		}
		return $data;
	}

	public function getInfo($vid=0){
		$data = null;
		if( $vid ){
			$this->dv->select('*')->from('motor');
			$this->dv->where('status',1);
			$this->dv->where('id',$vid);
			$this->dv->limit(1);
			$data =  $this->dv->get()->row();
		}
		return $data;
	}

	private function getNodeBy($vid=null,$get= null,$where=null){
		$table = self::checkDatabaseGPS($vid);
		if( $table === FALSE ) return null;
		$vid = abs($vid);
		if($table=='car'){
			$from = "data".intval(abs($vid)-config('carSpace'));
		} else {
			$from = "data$vid";
		}

		$this->$table->select($get)->from($from);
		if($where){
			$this->$table->where($where);
		}
		$data =  $this->$table->get()->row();

		if($data){
			return $data->$get;
		} else {
			return null;
		}
	}

	public function getTracks($uid){
		$this->dv->select('tr.id, m.*, m.id AS vehicle_id, tr.type AS permission')->from('motor_tracking AS tr');
		$this->dv->join('motor AS m', 'm.id = tr.taget', 'left');
		$this->dv->where('tr.owner',$uid);
		$this->dv->where('m.status',1);
// 		$this->dv->where('m.type',1);
		$this->dv->group_by("tr.taget");
		$data =  $this->dv->get()->result();
		if($data){
			foreach($data AS $k=>$item){
				$data[$k]->name = ($item->name !='' )?$item->name:'viettracker-'.base64_encode($item->id);
			}
		}
		return $data;
	}

	public function getLastVehicle($uid){
		$devices = self::loadVehicles($uid);
		if(!$devices || count($devices) <=0) return null;
		else {
			$item = $devices[0];
			if(self::checkData($item->id))
				return $item->id;
			else return null;
		}
	}
	public function getVehicle($id){
		$motor = self::getInfo($id);
		if(!$motor){
			return false;
		}
		if($motor->name == ''){
			$motor->name = 'viettracker-'.base64_encode($motor->id);
		}
		if($motor->imei == ''){
			$motor->imei = md5($motor->id);
		}
		$motor->created = $this->mapgps->printDate($motor->created);
		$motor->expiry = $this->mapgps->printDate($motor->expiry);
		$user = $this->Account_Model->userInfo($motor->owner);

		$motor->fullname = ($user)?$user->fullname:'';
		return $motor;
	}
	public function updateVehicle($data){
		if(isset($data['id']) && $data['id'] !=null ){
			$data['modified']=date("Y-m-d H:i:s");
			$data['modified_by']=$this->session->userdata('uid');
			$this->dv->where('id', $data['id']);
			$this->dv->update('motor', $data);
			return true;
		}
		return false;
	}

	public function getLastNode($vid,$where=null){
		$selectField = 'latitude AS la, longitude AS lo, TIMESERVER AS t, SPEED AS speed, GPSLEVEL AS gps, GsmLEVEL AS gsm, VAQ AS vaq, COURSE AS rotate';
		$table = self::checkDatabaseGPS($vid);

		if( $table === FALSE )
			return null;
		$vid = abs($vid);

		$vehicle = self::getVehicle($vid);
		if($table=='car'){

			if($vehicle->door){
				$selectField.=', DOOR AS door';
			}
			if($vehicle->fuel_current){
				$selectField.=', FUEL AS fuel';
			}
			if($vehicle->heat){
				$selectField.=', TEMP AS temp';
			}

			if($vehicle->conditioner){
				$selectField.=', COOLER AS cooler';
			}

			$this->$table->select($selectField)->from("data".intval(abs($vid)-config('carSpace')));
		} else {
			$this->$table->select($selectField)->from("demo_motor".abs($vid));
		}
// 		$this->$table->select($selectField)->from("data$vid");
		if($where){
			$this->$table->where($where);
		}
		$this->$table->order_by('id DESC')->limit(1);

		$query = $this->$table->get();//->row();
        if( !is_object($query) ){
//             bug($this->$table->last_query());
        } else {
            $data = $query->row();
        }
		if($data){
			$data->vid = mortorID($vid);
			$data->name = $vehicle->name;
			$data->t = strtotime($data->t);

			if($data->la >= 90 || $data->la <=-90 || $data->lo >= 180 || $data->lo <= -180){
				$data->correct = self::getLastNode($vid,array('La <'=> 90,'La >'=>-90,'Lon <'=> 180,'Lon >'=> -180) );
				//$data->t = $data->correct->t;
			}

			$data->la = $this->mapgps->shortDegrees($data->la);
			$data->lo = $this->mapgps->shortDegrees($data->lo);
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

	public function getLastNodeCar($vid,$where=null){

		$table = self::checkDatabaseGPS($vid);
		if( $table === FALSE ) return null;

		$selectField = 'La AS la, Lon AS lo, TIMESERVER AS t, SPEED AS speed, GPSLEVEL AS gps, GsmLEVEL AS gsm, VAQ AS vaq, COURSE AS rotate';
		$table = self::checkDatabaseGPS($vid);

		$vehicle = self::getVehicle($vid);
		if($vehicle->door){
			$selectField.=', DOOR AS door';
		}
		if($vehicle->fuel_current){
			$selectField.=', FUEL AS fuel';
		}
		if($vehicle->heat){
			$selectField.=', TEMP AS temp';
		}

		if($vehicle->conditioner){
			$selectField.=', COOLER AS cooler';
		}

		$carID = intval(abs($vid)-config('carSpace'));
		$this->$table->select($selectField)->from("data$carID");


		if($where){
			$this->$table->where($where);
		}
		$this->$table->order_by('POINTS  DESC')->limit(1);

		$data = $this->$table->get()->row();
		if($data){
			$data->vid = mortorID($vid);
			$vehicle = self::getVehicle($vid);
			$data->name = $vehicle->name;
			$data->t = strtotime($data->t);
			if($data->la >= 90 || $data->la <=-90 || $data->lo >= 180 || $data->lo <= -180){
				$data->correct = self::getLastNodeCar($vid,array('La <'=> 90,'La >'=>-90,'Lon <'=> 180,'Lon >'=> -180) );
			}
			$data->la = $this->mapgps->shortDegrees($data->la);
			$data->lo = $this->mapgps->shortDegrees($data->lo);
		} else {
			$data = null;
		}
		return $data;
	}

	public function loadNodeByTime($vehicleID,$time, $end=null){
// 		exit('get data history');
		$table = self::checkDatabaseGPS($vehicleID);
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

	public function getNode($time=null,$vehicleID){
		$table = self::checkDatabaseGPS($vehicleID);
		if($table=='car'){
			$from = "data".intval(abs($vehicleID)-config('carSpace'))." AS node";
		} else {
			$from = 'data'.abs($vehicleID).' AS node';
		}

		$this->$table->select('node.La AS la, node.Lon AS lo, node.TIMESERVER AS t, node.SPEED AS speed, node.GPSLEVEL AS gps, node.GsmLEVEL AS gsm, node.VAQ AS vaq, node.COURSE AS rotate')->from($from);
		$this->$table->where('node.TIMESERVER',date("Y-m-d H:i:s", $time) );
		$data = $this->$table->get()->row();
		//bug($this->node->last_query()); exit;
		return $data;

	}

	public function getStopNodeByTime($vehicleID,$time,$end=null,$calculaStopTime=TRUE){
		$table = self::checkDatabaseGPS($vehicleID);

		$selectField = 'POINTS AS id, node1.La AS la, node1.Lon AS lo, node1.TIMESERVER AS t, node1.SPEED AS speed, node1.GPSLEVEL AS gps, node1.GsmLEVEL AS gsm';
		if($table=='car'){
			$tagetID = intval(abs($vehicleID)-config('carSpace'));

		} else {
			$tagetID = abs($vehicleID);
		}

		$this->$table->select($selectField)->from("data$tagetID AS node1");
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
// 		$this->node->where('YEAR(node1.TIMESERVER)',date("Y", $time) );
// 		$this->node->where('MONTH(node1.TIMESERVER)',date("m", $time) );
// 		$this->node->where('DAY(node1.TIMESERVER)',date("d", $time) );
		$this->$table->where('node1.lon <',180);
		$this->$table->where('node1.lon >',-180);
		$this->$table->where('node1.la <',90);
		$this->$table->where('node1.la >',-90);
		$this->$table->where('node1.speed',0);
		$this->$table->where('( SELECT node2.`SPEED` FROM (`data'.$tagetID.'` AS node2) WHERE node2.`POINTS` =  (node1.POINTS - 1)  ) = ','0');
		$this->$table->where('( SELECT node3.`SPEED` FROM (`data'.$tagetID.'` AS node3) WHERE node3.`POINTS` =  (node1.POINTS - 2)  ) = ','0');
		$this->$table->where('( SELECT node4.`SPEED` FROM (`data'.$tagetID.'` AS node4) WHERE node4.`POINTS` =  (node1.POINTS - 3)  ) > ','0');
		$this->$table->order_by('node1.TIMESERVER ASC ');
		//$this->node->limit(12);
		$data = $this->$table->get()->result();
		//bug($this->node->last_query());exit;
		if($data && $calculaStopTime){
			foreach($data AS $index=>$node){
				$data[$index]->t = self::calculaStopTime($vehicleID,$node->id);
			}
		}
		//$query = $this->node->get();

		return $data;
	}

	public function calculaStopTime($vehicleID,$stopID){
		$table = self::checkDatabaseGPS($vehicleID);
		if(!$stopID) return 0;

		if($table=='car'){
			$tagetID = intval(abs($vehicleID)-config('carSpace'));

		} else {
			$tagetID = abs($vehicleID);
		}

		$this->$table->select('TIMESERVER AS t, POINTS AS id')->from("data$tagetID");
		$this->$table->where('POINTS',$stopID-2);
		$begin = $this->$table->get()->row();

		$this->$table->select('TIMESERVER AS t, POINTS AS id, SPEED')->from("data$tagetID");
		$this->$table->where('POINTS >',$stopID);
		$this->$table->where('SPEED >',0);
		$this->$table->order_by('TIMESERVER ASC ');
		$end = $this->$table->get()->row();
		//bug($this->$table->last_query());

		if($begin && $end){
			return time_diff($begin->t,$end->t);
		} else {
			return null;
		}
		//$begin =
	}



	public function getVehicleReport($vid=0){
		$vid = abs($vid);
		if($this->node === false){
			return null;
		}
		$motor = $this->Vehicle_Model->getVehicle($vid);
		$data['name'] = $motor->name;
		$data['plate_number'] = $motor->plate_number;
		$data['length_road'] = 0;
		$data['max_speed'] = 0;
		$data['moving_time'] = 0;
		return $data;

	}

	public function lengthRoad($vid,$day='',$month='',$year=''){
		$length = 0;
		// 		bug($this->node);
		// 		exit;
		$table = checkDatabaseGPS($vid);
		// 		bug($this->node);
		// 				exit;
		// 		bug($table);
		// 		bug(   $this->load->database('node',true)  );
		// 		exit('$vid='.$vid);
		$table->select('*')->from("data$vid");
		$table->where('SPEED >',0);
		if($year){
			$table->where('YEAR(TIMESERVER)',(int)$year);
		}
		if($month){
			$table->where('MONTH(TIMESERVER)',(int)$month);
		}
		if($day){
			$table->where('DAY(TIMESERVER)',(int)$day);
		}

		//$this->node->where('( SELECT node2.`SPEED` FROM (`'.$table.'` AS node2) WHERE node2.`POINTS` =  (node1.`POINTS` + 1)  ) > ','0');
		$table->order_by('POINTS DESC');
		// 		$data = $table->get();
		// 		bug( $table->last_query() );
		// 		bug( $this->node);
		// 		 exit;
		$data = $table->get()->result();
		foreach($data AS $index=>$v){
			if($index > 0 ){
				$length += distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon);
			}
		}
		// 		bug($this->node->last_query()); exit;
		$km = round($length/1000);
		$met = $length - $km*1000;
		$string = (($km > 0 )?$km.' km ':'').(($met > 0 )?round($met).' m':'');
		return ($string!='')?$string:'0 m';
		//return $length;
	}

	public function maxSpeed($vehicleID,$day='',$month='',$year=''){
		$data['max'] = 0;
		$this->node->select('MAX(SPEED) AS max')->from("data$vehicleID");
		if($year){
			$this->node->where('YEAR(TIMESERVER)',(int)$year);
		}
		if($month){
			$this->node->where('MONTH(TIMESERVER)',(int)$month);
		}
		if($day){
			$this->node->where('DAY(TIMESERVER)',(int)$day);
		}
		$node = $this->node->get()->row();
		// 		bug($this->node->last_query()); exit;
		if($node && $node->max){
			$data['max'] = round($node->max,2);
			$this->node->stop_cache();
			$this->node->select('TIMESERVER AS time_max')->from("data$vehicleID");
			$this->node->where('SPEED',$node->max);
			$this->node->order_by('POINTS DESC');
			$node = $this->node->get()->row();
			//$data['time'] = $node->time_max;
			$data['time'] = date("d/m/Y H:m:s", strtotime($node->time_max) );
		} else {
			$data['max'] = '0';
		}
		return $data;
	}

	public function movingTime($vehicleID,$day='',$month='',$year=''){
		$time = 0; // secon
		$this->node->select('*')->from("data$vehicleID");
		$this->node->where('SPEED >',0);
		if($year){
			$this->node->where('YEAR(TIMESERVER)',(int)$year);
		}
		if($month){
			$this->node->where('MONTH(TIMESERVER)',(int)$month);
		}
		if($day){
			$this->node->where('DAY(TIMESERVER)',(int)$day);
		}
		$this->node->order_by('POINTS ASC');
		$data = $this->node->get()->result();
		if($data){
			foreach($data AS $key=>$node){
				if(isset($data[$key-1]) && ($node->POINTS = $data[$key-1]->POINTS) ){
					$datetime1 = new DateTime($node->TIMESERVER);
					$datetime2 = new DateTime($data[$key-1]->TIMESERVER);
					$inter = $datetime2->diff($datetime1);
					// 					$time += $inter->s + ($inter->i*60)+ ($inter->h*60*60) + ($inter->d*60*60*24);
					$time += $inter->s;
				}
			}
		}
		// 		bug($this->node->last_query());
		// 		bug($time);exit;
		$hour = round($time/3600,0,PHP_ROUND_HALF_DOWN);
		$minute = $time/60 - $hour*3600;
		$string = (($hour > 0 )?$hour.' giÆ¡Ì€ ':'').((round($minute) > 0 )?round($minute).' phuÌ�t':'');
		return ($string!='')?$string:'0 phuÌ�t';
		//return ($time > 0)? ($time/60):0;


	}


	public function report_all($vid,$day='',$month='',$year=''){

		$table = self::checkDatabaseGPS($vid);
		$vid = abs($vid);
		$this->vehicle = self::getInfo($vid);


		if($table === false){
			return null;
		}
		if($table=='car'){
			if( !isset($this->vehicle->fuel_type) || !$this->vehicle->fuel_type  ){
				$this->vehicle->fuel_type = 'ron-95';
			}
			$this->$table->select('*')->from("data".intval(abs($vid)-config('carSpace')));

		} else {
			$this->vehicle->fuel_type = 'ron-92';
			$this->$table->select('*')->from("motor$vid");
		}

		$this->$table->where( array('longitude <'=>180,'longitude >'=>-180,'latitude <'=>90,'latitude >'=> -90));
		if( (int)$day > 0 && (int)$month >0 && (int)$year > 0 ){
			$this->$table->where('DAY(TIMESERVER)',(int)$day);
			$this->$table->where('MONTH(TIMESERVER)',(int)$month);
			$this->$table->where('YEAR(TIMESERVER)',(int)$year);
			$this->$table->order_by('id DESC ');
			$return = self::report_nodes($this->$table->get()->result(),'day', ($table=='car')?TRUE:FALSE );
		} else if ( (int)$month >0 && (int)$year > 0 ){
			$this->$table->where('MONTH(TIMESERVER)',(int)$month);
			$this->$table->where('YEAR(TIMESERVER)',(int)$year);
			$this->$table->order_by('id DESC ');
			$query = $this->$table->get();
			if( is_object($query) ){
			    $return = self::report_nodes($query->result(),'month',($table=='car')?TRUE:FALSE,$day,$month,$year);
			} else {

			}
		} else if ((int)$year > 0){ // never using
			$this->$table->where('YEAR(TIMESERVER)',(int)$year);
			$this->$table->order_by('POINTS DESC ');
			$data = $this->$table->get()->result();
			$return = self::report_nodes($data,'year',($table=='car')?TRUE:FALSE,null,null,$year);
			$return['type'] = 'year';

		} else {
		    $return = NULL;
		}
		return $return;
	}

	private function report_nodes($data=null,$type='day',$car_Fuel=FALSE,$day=1,$month=1,$year=1970){
		$movingSecons = 0;
		$out = array(
			'node'=>null,
			'type'=>$type,
			'length_road'=>0,
			'max_speed' => array('max'=>0,'time'=>''),
			'fuel'=>array(),
			'fuel_price'=>0,
			'fuel_price_vnd'=>'',
			'fuel_price_time'=>array()
		);

		$fuel_price = '';
		$fuel_price_next = '';

		if( $type=='day' ){
			for($i=0; $i <= 24 ;$i++){
				$out['node'][$i] = 0;
				$out['fuel'][$i] = 0;
			}

			foreach($data AS $index=>$v){ if($index > 0 ){
					$index = (int)$index;
					$timeKey = (int)date("H", strtotime($v->TIMESERVER) );
					$distance = self::distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon);

					if(isset($out['node'][$timeKey])){
						$out['node'][$timeKey] += $distance;
					}
					$out['length_road'] +=$distance;
					if($v->SPEED > 0){
						$datetime1 = new DateTime($v->TIMESERVER);
						$datetime2 = new DateTime($data[$index-1]->TIMESERVER);
						$inter = $datetime2->diff($datetime1);
						$movingSecons += $inter->s + ($inter->i*60)+ ($inter->h*60*60) + ($inter->d*60*60*24);
						if($out['max_speed']['max'] < $v->SPEED){
							$out['max_speed']['max'] = $v->SPEED;
							$out['max_speed']['time'] = date("d/m/Y H:i:s", strtotime($v->TIMESERVER) );
							$out['max_speed']['latlng'] = $this->mapgps->shortDegrees($v->La).','.$this->mapgps->shortDegrees($v->Lon);
						}
					}

					if( !$fuel_price
						||  $fuel_price->time > strtotime($v->TIMESERVER)
						|| ($fuel_price_next && $fuel_price_next->time < strtotime($v->TIMESERVER) )
					) {
						$fuel_price = self::get_fuel($v->TIMESERVER,' < ');
						$fuel_price->price = json_decode($fuel_price->price,TRUE);
						$out['fuel_price_time'][] = date("d/m/Y H:i:s", ($fuel_price->time) );
						$fuel_price_next = self::get_fuel($v->TIMESERVER,' >= ');
					}

					if( $car_Fuel === TRUE && isset($out['fuel'][$timeKey])) {
						if( abs($data[$index-1]->FUEL - $data[$index]->FUEL) < config('fuel-add') ){
							$fuel_count = (($data[$index]->FUEL - $data[$index-1]->FUEL)*$this->vehicle->fuel)/100;
							$out['fuel'][$timeKey] += $fuel_count;
							//,$this->vehicle->fuel_type
							$out['fuel_price']+= $fuel_count*$fuel_price->price[$this->vehicle->fuel_type];
						}
					} else {
						$fuel_count = ($distance/100)*$this->vehicle->fuel;
						$out['fuel'][$timeKey] += $fuel_count;
						$out['fuel_price']+= $fuel_count*$fuel_price->price[$this->vehicle->fuel_type];
					}
			}}
			// end foreach data by day
		} else if( $type == 'month' ) {
			for($i=1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year) ;$i++){
				$out['node'][$i] = 0;
				$out['fuel'][$i] = 0;
			}
			foreach($data AS $index=>$v){ if($index > 0 ){
				$timeKey = (int)date("d", strtotime($v->TIMESERVER));
				$distance = $this->mapgps->distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon);
				$out['node'][$timeKey] += $distance;
				$out['length_road'] +=$distance;

				if($v->SPEED > 0){
					$datetime1 = new DateTime($v->TIMESERVER);
					$datetime2 = new DateTime($data[$index-1]->TIMESERVER);
					$inter = $datetime2->diff($datetime1);
					$movingSecons += $inter->s + ($inter->i*60)+ ($inter->h*60*60) + ($inter->d*60*60*24);
					if($out['max_speed']['max'] < $v->SPEED){
						$out['max_speed']['max'] = $v->SPEED;
						$out['max_speed']['time'] = date("d/m/Y H:i:s", strtotime($v->TIMESERVER) );
						$out['max_speed']['latlng'] = $this->mapgps->shortDegrees($v->La).','.$this->mapgps->shortDegrees($v->Lon);
					}
				}

				if( !$fuel_price
						||  $fuel_price->time > strtotime($v->TIMESERVER)
						|| ($fuel_price_next && $fuel_price_next->time < strtotime($v->TIMESERVER) )
				) {
					$fuel_price = self::get_fuel($v->TIMESERVER,' < ');
					$fuel_price->price = json_decode($fuel_price->price,TRUE);
					$out['fuel_price_time'][] = date("d/m/Y H:i:s", ($fuel_price->time) );
					$fuel_price_next = self::get_fuel($v->TIMESERVER,' >= ');
				}

				if( $car_Fuel === TRUE && isset($out['fuel'][$timeKey+1])) {
					if( abs($data[$index-1]->FUEL - $data[$index]->FUEL) < config('fuel-add') ){
						$fuel_count = (($data[$index]->FUEL - $data[$index-1]->FUEL)*$this->vehicle->fuel)/100;
						$out['fuel'][$timeKey] += $fuel_count;
						$out['fuel_price']+= $fuel_count*$fuel_price->price[$this->vehicle->fuel_type];
					}
				} else {
					$fuel_count = ($distance/100)*$this->vehicle->fuel;
					$out['fuel'][$timeKey] += $fuel_count;
					$out['fuel_price']+= $fuel_count*$fuel_price->price[$this->vehicle->fuel_type];
				}

			}}
			// end foreach data by month
		} else if ( $type == 'year' ) {
			for($i=1; $i <= 12 ;$i++){
				if($i <10) $i='0'.$i;
				$out['node'][$i] = 0;
			}
			foreach($data AS $index=>$v){
				if($index > 0 ){
					$out['node'][date("m", strtotime($v->TIMESERVER)) ] += $this->mapgps->distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon,true);
					if($v->SPEED > 0 && isset($data[$index-1])){
						$datetime1 = new DateTime($v->TIMESERVER);
						$datetime2 = new DateTime($data[$index-1]->TIMESERVER);
						$inter = $datetime2->diff($datetime1);
						$movingSecons += $inter->s + ($inter->i*60)+ ($inter->h*60*60) + ($inter->d*60*60*24);
					}
				}
			}
		}

		$out['length_road'] = ($out['length_road'] > 0)?round($out['length_road'],2).' Km':'0 m';

		$hMove = floor($movingSecons/3600);
		$mMove = round( ($movingSecons - $hMove*3600)/60 );
		$out['moving_time'] = (($hMove>0)?($hMove).lang('seconds'):'').($mMove.' '.lang('minutes')) ;

		foreach ($out['fuel'] AS $index=>$fuelVal){
			$out['fuel'][$index] = abs($out['fuel'][$index]);
			if(isset($out['fuel'][$index-1])){
				$out['fuel'][$index] +=$out['fuel'][$index-1];
			}
		}
		if($out['fuel_price'] && $out['fuel_price'] > 0 ){
			$out['fuel_price_vnd'] = VndTextRound($out['fuel_price']);
		}
		return $out;

	}

	private function report_car($vid=0,$day='',$month='',$year=''){

		/*
		$motor = self::getVehicle($vid);

		if($motor && isset($motor->fuel) && $motor->fuel> 0 ){
			//  on ly get for motor
			foreach ($return['node'] AS $index=>$km){
				if( $table =='car' ){
					$motor->fuel = (isset($motor->fuel) && $motor->fuel)?$motor->fuel:100;
					$return['fuel'][$index] = round( ($km/100)*$motor->fuel ,2);
				} else {
					$return['fuel'][$index] = round( ($km/100)*$motor->fuel ,2);
					if(isset($return['fuel'][$index-1])){
						$return['fuel'][$index]+=$return['fuel'][$index-1];
					}
				}

				// 				if($motor->fuel_price > 0){ }
			}
		}


		$return['fuel_price'] = ( isset($motor->fuel_price) )?$motor->fuel_price:0;

		*/
	}

	public function get_fuel($time='',$type_time = ' < '){
		$data = null;
		$this->dv->select('*')->from('fuel')->where(array("time $type_time"=>$time,'status'=>1));
		$data =  $this->dv->order_by('time DESC ')->limit(1)->get()->row();
		if($data){
			$data->time = strtotime($data->time);
		}
		return $data;


	}

	public function report_fuel($vid,$day='',$month='',$year=''){
		$table = self::checkDatabaseGPS($vid);
		if($table === false || $table != 'car'){
			return null;
		}

		$return['node'] = array();

		$this->$table->select('*')->from( "data".intval( abs($vid)-config('carSpace') ) );
// 		$this->$table->where('Lon <',180);
// 		$this->$table->where('Lon >',-180);
// 		$this->$table->where('La <',90);
// 		$this->$table->where('La >',-90);
		if( (int)$day > 0 && (int)$month >0 && (int)$year > 0 ){
			$this->$table->where('DAY(TIMESERVER)',(int)$day);
			$this->$table->where('MONTH(TIMESERVER)',(int)$month);
			$this->$table->where('YEAR(TIMESERVER)',(int)$year);

			$return['type'] = 'day';
		} else if ( (int)$month >0 && (int)$year > 0 ){
			$this->$table->where('MONTH(TIMESERVER)',(int)$month);
			$this->$table->where('YEAR(TIMESERVER)',(int)$year);

			$return['type'] = 'month';
		}

		$this->$table->order_by('POINTS ASC ');
		$data = $this->$table->get()->result();
		if($data){
			foreach($data AS $index=>$v){
				$return['node'][] = array(
						't'=>strtotime($v->TIMESERVER),
						'val'=>$v->FUEL
				);
			}
		}
		return $return;

	}

	public function node_stop($limitF=0,$limitTo=5,$order='',$where=''){

		$vehicleID = 0;

		$dataReturn=array();

		if(!isset($where['vehicleID'])){
			return $dataReturn;
		} else {
			$table = self::checkDatabaseGPS($where['vehicleID']);

			$vehicleID = $where['vehicleID'];

			if($table=='car'){
				$nTable = "data".intval( abs($where['vehicleID'])-config('carSpace') );
				unset($where['vehicleID']);
			} else {
				$nTable = "data".$vehicleID;
				unset($where['vehicleID']);
			}
		}


		$nSelect = 'node1.*';
		$nFrom = $nTable.' AS node1';
		$nWhere = array(
				'node1.lon <'=>180,'node1.lon >'=>-180,'node1.la <'=>90,'node1.la >'=>-90,'SPEED'=>0,
				'( SELECT node2.`SPEED` FROM (`'.$nTable.'` AS node2) WHERE node2.`POINTS` =  (node1.`POINTS` - 1)  ) = '=>0,
				'( SELECT node3.`SPEED` FROM (`'.$nTable.'` AS node3) WHERE node3.`POINTS` =  (node1.`POINTS` - 2)  ) = '=>0,
				'( SELECT node4.`SPEED` FROM (`'.$nTable.'` AS node4) WHERE node4.`POINTS` =  (node1.`POINTS` - 3)  ) > '=>0
		);
		$order = 'POINTS ASC';
		if(is_array($where)){
			foreach($where AS $key=>$item)
				$nWhere[$key]=$item;
		}

		$this->$table->select($nSelect)->from($nFrom)->where($nWhere)->order_by($order);
// 		bug($limitF);
		if($limitTo > 0 ){
			$data = $this->$table->limit($limitTo,$limitF)->get()->result();
		} else {
			$data = $this->$table->get()->result();
		}

// 		bug($data);
// 		bug($this->$table->last_query()); exit;

		$dataReturn['data']=array();

		foreach($data AS $key=>$v){
			$address = $this->mapgps->geocode($v->La.','.$v->Lon);
			$stopTime = ($vehicleID)?self::calculaStopTime($vehicleID,$v->POINTS):0;
			$dataReturn['data'][] = array(date("d/m/Y H:i:s", strtotime($v->TIMESERVER) ) , $v->GPSLEVEL,$v->GsmLEVEL,$v->VAQ,$stopTime,'<div class="node_stop" latlng="'.$this->mapgps->shortDegrees($v->La).','.$this->mapgps->shortDegrees($v->Lon).'" >'.$address.'</div>' );

		}
		$dataReturn['totalRecords']= $this->$table->select($nSelect)->from($nFrom)->where($nWhere)->count_all_results();
		return $dataReturn;
	}

	public function node_stop_fuel($limitF=0,$limitTo=5,$order='',$where=''){

		$vehicleID = 0;
		$vehicle  = self::getVehicle($where['vehicleID']);
		$dataReturn=array();
		if(!isset($where['vehicleID'])){
			return $dataReturn;
		} else {
			$table = self::checkDatabaseGPS($where['vehicleID']);

			$vehicleID = $where['vehicleID'];

			if($table=='car'){
				$nTable = "data".intval( abs($where['vehicleID'])-config('carSpace') );
				unset($where['vehicleID']);
			} else {
				$nTable = "data".$vehicleID;
				unset($where['vehicleID']);
			}
		}

		$nSelect = 'node1.*';
		$nFrom = $nTable.' AS node1';
		$nWhere = array(
				//'node1.lon <'=>180,'node1.lon >'=>-180,'node1.la <'=>90,'node1.la >'=>-90,
				'( SELECT node2.`FUEL` FROM (`'.$nTable.'` AS node2) WHERE node2.`POINTS` =  (node1.`POINTS` + 1)  ) - node1.`FUEL` >'=>config('fuel-add'),
		);
		$order = 'POINTS ASC';
		if(is_array($where)){
			foreach($where AS $key=>$item)
				$nWhere[$key]=$item;
		}
		$data = $this->$table->select($nSelect)->from($nFrom)->where($nWhere)->order_by($order)->limit($limitTo,$limitF)->get()->result();
// 		bug($this->$table->last_query()); exit;
		$dataReturn['totalRecords']= $this->$table->select($nSelect)->from($nFrom)->where($nWhere)->count_all_results();

		$dataReturn['data']=array();


		foreach($data AS $key=>$v){
			$address = $this->mapgps->geocode($v->La.','.$v->Lon);
			$dataReturn['data'][] = array(
					date("d/m/Y H:i:s", strtotime($v->TIMESERVER) ) ,
					$v->GPSLEVEL,$v->GsmLEVEL,
					$v->VAQ,
					((self::getNodeBy($vehicleID,'FUEL',array('POINTS '=>$v->POINTS+1))-$v->FUEL) * $vehicle->fuel/100).'<span class="unit">(lÃ­t)</span>',
					'<div class="node_stop" latlng="'.$this->mapgps->shortDegrees($v->La).','.$this->mapgps->shortDegrees($v->Lon).'" >'.$address.'</div>' );

		}
		return $dataReturn;
	}
}
