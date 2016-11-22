<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Report_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->node = $this->load->database('node',true);
	}
	
	public function getVehicleReport($vid=0){
// 		$this->node = checkDatabaseGPS($vid);
		$vid = abs($vid);
		if($this->node === false){
			return null;
		}
// 		checkDatabaseGPS($this->vid) ===false
		
		$motor = $this->Vehicle_Model->getVehicle($vid);
		$data['name'] = $motor->name;
		$data['plate_number'] = $motor->plate_number;
		
// 		$data['length_road'] = self::lengthRoad($vid);
// 		$data['max_speed'] = self::maxSpeed($vid);
// 		$data['moving_time'] = self::movingTime($vid);
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
		$string = (($hour > 0 )?$hour.' giờ ':'').((round($minute) > 0 )?round($minute).' phút':'');
		return ($string!='')?$string:'0 phút';
		//return ($time > 0)? ($time/60):0;
	
	
	}
	
	
	public function report_all($vid,$day='',$month='',$year=''){
		$table = checkDatabaseGPS($vid);
		$vid = abs($vid);
		if($table === false){
			return null;
		}
	
		$movingSecons = 0;
		$maxSpeed = 0;
		$maxSpeedAt = '';
		
		$return = array('node'=>null);
		$lengthRoad = 0;
		$table->select('*')->from("data$vid");
		$table->where('Lon <',180);
		$table->where('Lon >',-180);
		$table->where('La <',90);
		$table->where('La >',-90);
	
		if( (int)$day > 0 && (int)$month >0 && (int)$year > 0 ){
			$table->where('DAY(TIMESERVER)',(int)$day);
			$table->where('MONTH(TIMESERVER)',(int)$month);
			$table->where('YEAR(TIMESERVER)',(int)$year);
			$table->order_by('POINTS DESC ');
			$data = $table->get()->result();
// 			bug($table->last_query());exit;
			for($i=1; $i <= 24 ;$i++){
				//if($i <10) $i='0'.$i;
				$return['node'][$i] = 0;
				//$return['fuel'][$i]=0;
			}
			foreach($data AS $index=>$v){
				if($index > 0 ){
					$index = (int)$index;
					$timeKey = (int)date("H", strtotime($v->TIMESERVER) );
// 					bug("timeKey=$timeKey");
					$distance = distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon);
					if(isset($return['node'][$timeKey+1])){
						$return['node'][$timeKey+1] += $distance;
					} //else {
					//	$return['node'][$timeKey] = 0;
					//}
						
					$lengthRoad +=$distance;
						
					if($v->SPEED > 0){
						$datetime1 = new DateTime($v->TIMESERVER);
						$datetime2 = new DateTime($data[$index-1]->TIMESERVER);
						$inter = $datetime2->diff($datetime1);
						$movingSecons += $inter->s + ($inter->i*60)+ ($inter->h*60*60) + ($inter->d*60*60*24);
						if($maxSpeed < $v->SPEED){
							$maxSpeed = $v->SPEED;
							$maxSpeedAt = date("d/m/Y H:i:s", strtotime($v->TIMESERVER) );
						}
					}
				}
			}
			$return['type'] = 'day';
		} else if ( (int)$month >0 && (int)$year > 0 ){
			$table->where('MONTH(TIMESERVER)',(int)$month);
			$table->where('YEAR(TIMESERVER)',(int)$year);
			$table->order_by('POINTS DESC ');
	
			$data = $table->get()->result();
			for($i=1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year) ;$i++){
				//if($i <10) $i='0'.$i;
				$return['node'][$i] = 0;
				//$return['fuel'][$i]=0;
			}
			foreach($data AS $index=>$v){
				if($index > 0 ){
					$timeKey = (int)date("d", strtotime($v->TIMESERVER));
					//$return['node'][date("d", strtotime($v->TIMESERVER))] += $this->mapgps->distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon,true);
					$distance = $this->mapgps->distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon);
					$return['node'][$timeKey] += $distance;
					$lengthRoad +=$distance;
					if($v->SPEED > 0){
						$datetime1 = new DateTime($v->TIMESERVER);
						$datetime2 = new DateTime($data[$index-1]->TIMESERVER);
						$inter = $datetime2->diff($datetime1);
						$movingSecons += $inter->s + ($inter->i*60)+ ($inter->h*60*60) + ($inter->d*60*60*24);
						if($maxSpeed < $v->SPEED){
							$maxSpeed = $v->SPEED;
							$maxSpeedAt = date("d/m/Y H:i:s", strtotime($v->TIMESERVER) );
						}
					}
				}
			}
			$return['type'] = 'month';
			// 		}
		} else if ((int)$year > 0){ // never using
				
			$this->node->where('YEAR(TIMESERVER)',(int)$year);
			$this->node->order_by('POINTS DESC ');
			$data = $this->node->get()->result();
			for($i=1; $i <= 12 ;$i++){
				if($i <10) $i='0'.$i;
				$return['node'][$i] = 0;
			}
			foreach($data AS $index=>$v){
				if($index > 0 ){
					$return['node'][date("m", strtotime($v->TIMESERVER)) ] += $this->mapgps->distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon,true);
					if($v->SPEED > 0 && isset($data[$index-1])){
						$datetime1 = new DateTime($v->TIMESERVER);
						$datetime2 = new DateTime($data[$index-1]->TIMESERVER);
						$inter = $datetime2->diff($datetime1);
						$movingSecons += $inter->s + ($inter->i*60)+ ($inter->h*60*60) + ($inter->d*60*60*24);
					}
				}
			}
			$return['type'] = 'year';
	
		}
	
		//		foreach($return['node'] AS $long){
		//			$lengthRoad +=$long;
		//		}
	
		$motor = $this->Vehicle_Model->getVehicle($vid);
	
		if($motor && isset($motor->fuel) && $motor->fuel>0 ){
			foreach ($return['node'] AS $index=>$km){
	
				//$return['fuel'][$index] = round($km/100*$motor->fuel,2);
				$return['fuel'][$index] = round( ($km/100)*$motor->fuel ,2);
				if(isset($return['fuel'][$index-1])){
					$return['fuel'][$index]+=$return['fuel'][$index-1];
				}
				if($motor->fuel_price > 0){
					//$return['money'][$index] = $return['fuel'][$index]*$motor->fuel_price;
				}
			}
		}
		$return['length_road'] = round($lengthRoad,2).' Km';
		if($return['length_road'] == ''){
			$return['length_road'] = '0 m';
		}
		$return['fuel_price'] = ( isset($motor->fuel_price) )?$motor->fuel_price:0;
		$return['max_speed'] = array('max'=>$maxSpeed,'time'=>$maxSpeedAt);
		$hMove = floor($movingSecons/3600);
		$mMove = round( ($movingSecons - $hMove*3600)/60 );
		$return['moving_time'] = (($hMove>0)?($hMove).' Giờ :':'').($mMove.' Phút') ;
		// 		bug($return);exit;
		return $return;
	
	}
	
	public function node_stop($limitF=0,$limitTo=5,$order='',$where=''){
		
		$vehicleID = 0;
		$dataReturn=array();
		if(!isset($where['vehicleID'])){
			return $dataReturn;
		} else {
			$table = checkDatabaseGPS($where['vehicleID']);
			$nTable = "data".$where['vehicleID'];
			$vehicleID = $where['vehicleID'];
			unset($where['vehicleID']);
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
		$data = $table->select($nSelect)->from($nFrom)->where($nWhere)->order_by($order)->limit($limitTo,$limitF)->get()->result();
		$dataReturn['totalRecords']=$table->select($nSelect)->from($nFrom)->where($nWhere)->count_all_results();
			
		$dataReturn['data']=array();
	
		foreach($data AS $key=>$v){
			$address = $this->mapgps->geocode($v->La.','.$v->Lon);
			$stopTime = ($vehicleID)?$this->Vehicle_Model->calculaStopTime($vehicleID,$v->POINTS):0;
			$dataReturn['data'][] = array(date("d/m/Y H:i:s", strtotime($v->TIMESERVER) ) , $v->GPSLEVEL,$v->GsmLEVEL,$v->VAQ,$stopTime,'<div class="node_stop" latlng="'.$this->mapgps->shortDegrees($v->La).','.$this->mapgps->shortDegrees($v->Lon).'" >'.$address.'</div>' );
	
		}
		return $dataReturn;
	}
}