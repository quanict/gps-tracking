<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Statistic_Model extends CI_Model {
	var $table = 'data9';
	function __construct(){
		parent::__construct();
		$this->st = $this->load->database('node',true);
		
	}

	
	public function node_stop($limitF=0,$limitTo=5,$order='',$where=''){
		$vehicleID = 0;
		$dataReturn=array();
		if(!isset($where['vehicleID'])){
			return $dataReturn;
		} else {
			$table = "data".$where['vehicleID'];
			$vehicleID = $where['vehicleID'];
			unset($where['vehicleID']);
		}
		$this->st->select('node1.*')->from($table.' AS node1');
		if(is_array($where)){
			foreach($where AS $key=>$item)
				$this->st->where($key,$item);
		}
		$this->st->where('node1.lon <',180);
		$this->st->where('node1.lon >',-180);
		$this->st->where('node1.la <',90);
		$this->st->where('node1.la >',-90);
		$this->st->where('SPEED','0');
		$this->st->where('( SELECT node2.`SPEED` FROM (`'.$table.'` AS node2) WHERE node2.`POINTS` =  (node1.`POINTS` - 1)  ) = ','0');
		$this->st->where('( SELECT node3.`SPEED` FROM (`'.$table.'` AS node3) WHERE node3.`POINTS` =  (node1.`POINTS` - 2)  ) = ','0');
		$this->st->where('( SELECT node4.`SPEED` FROM (`'.$table.'` AS node4) WHERE node4.`POINTS` =  (node1.`POINTS` - 3)  ) > ','0');
		$this->st->order_by('POINTS ASC');
		
		$this->st->flush_cache();
		$this->st->limit($limitTo,$limitF);
		$data = $this->st->get()->result();
// 	bug($this->st->last_query());exit;
	
		$this->st->select('node1.*')->from($table.' AS node1');
		$this->st->where('node1.lon <',180);
		$this->st->where('node1.lon >',-180);
		$this->st->where('node1.la <',90);
		$this->st->where('node1.la >',-90);
		$this->st->where('SPEED','0');
		$this->st->where('( SELECT node2.`SPEED` FROM (`'.$table.'` AS node2) WHERE node2.`POINTS` =  (node1.`POINTS` - 1)  ) = ','0');
		$this->st->where('( SELECT node3.`SPEED` FROM (`'.$table.'` AS node3) WHERE node3.`POINTS` =  (node1.`POINTS` - 2)  ) = ','0');
		$this->st->where('( SELECT node4.`SPEED` FROM (`'.$table.'` AS node4) WHERE node4.`POINTS` =  (node1.`POINTS` - 3)  ) > ','0');
		$this->st->order_by('POINTS ASC');
		if(is_array($where)){
			foreach($where AS $key=>$item)
				$this->st->where($key,$item);
		}
	
		$this->st->stop_cache();
		$dataReturn['totalRecords']=$this->st->count_all_results();
			
		$dataReturn['data']=array();
	
		foreach($data AS $key=>$v){
//			$address = $v->La.','.$v->Lon;
			$address = $this->mapgps->geocode($v->La.','.$v->Lon);
			
			$stopTime = ($vehicleID)?$this->Vehicle_Model->calculaStopTime($vehicleID,$v->POINTS):0;
			$dataReturn['data'][] = array(date("d/m/Y H:i:s", strtotime($v->TIMESERVER) ) , $v->GPSLEVEL,$v->GsmLEVEL,$v->VAQ,$stopTime,'<div class="node_stop" latlng="'.$this->mapgps->shortDegrees($v->La).','.$this->mapgps->shortDegrees($v->Lon).'" >'.$address.'</div>' );
	
		}
		return $dataReturn;
	}
	
	function preNodeSpeed($id){
		//$this->st->select('SPEED')->from($this->table);
	}
	
	public function report_all($vehicleID,$day='',$month='',$year=''){
		if(!$vehicleID) return null;

		$movingSecons = 0;
		$maxSpeed = 0;
		$maxSpeedAt = '';
		$return = array('node'=>null);
		$lengthRoad = 0;
		$this->st->select('*')->from("data$vehicleID");
		$this->st->where('Lon <',180);
		$this->st->where('Lon >',-180);
		$this->st->where('La <',90);
		$this->st->where('La >',-90);
		
		if( (int)$day > 0 && (int)$month >0 && (int)$year > 0 ){
			$this->st->where('DAY(TIMESERVER)',(int)$day);
			$this->st->where('MONTH(TIMESERVER)',(int)$month);
			$this->st->where('YEAR(TIMESERVER)',(int)$year);
			$this->st->order_by('POINTS DESC ');
			$data = $this->st->get()->result();
//			bug($this->st->last_query());exit;
			for($i=1; $i <= 24 ;$i++){
				//if($i <10) $i='0'.$i;
				$return['node'][$i] = 0;
				//$return['fuel'][$i]=0;
			}
			foreach($data AS $index=>$v){
				if($index > 0 ){
					$index = (int)$index;
					$timeKey = (int)date("H", strtotime($v->TIMESERVER) );
					$distance = $this->mapgps->distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon);
					if(isset($return['node'][$timeKey])){
						$return['node'][$timeKey] += $distance;
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
			$this->st->where('MONTH(TIMESERVER)',(int)$month);
			$this->st->where('YEAR(TIMESERVER)',(int)$year);
			$this->st->order_by('POINTS DESC ');

			$data = $this->st->get()->result();
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
			
			$this->st->where('YEAR(TIMESERVER)',(int)$year);
			$this->st->order_by('POINTS DESC ');
			$data = $this->st->get()->result();
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
		
		$motor = $this->Vehicle_Model->getVehicle($vehicleID);
		
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
	
	public function getNodeByTime($time,$limit=5,$page=1){
		//$time = date("d/m/Y", strtotime($time) );
		$this->st->select('La AS la, Lon AS lo, TIMESERVER AS t, SPEED AS speed, GPSLEVEL AS gps, GsmLEVEL AS gsm')->from($this->table);
		$this->st->where('YEAR(TIMESERVER)',date("Y", strtotime($time) ));
		$this->st->where('MONTH(TIMESERVER)',date("m", strtotime($time) ));
		$this->st->where('DAY(TIMESERVER)',date("d", strtotime($time) ));
		$this->st->where('Lon <',180);
		$this->st->where('Lon >',-180);
		$this->st->where('La <',90);
		$this->st->where('La >',-90);
		$this->st->limit($limit,$page);
		$this->st->order_by('TIMESERVER DESC ');
		$data['nodes'] = $this->st->get()->result();
		//$data['count'] = count($data['nodes']);
// 		echo $this->st->last_qsuery();exit;
		
		
		$this->st->select('La AS la, Lon AS lo, TIMESERVER AS t')->from($this->table);
		$this->st->where('YEAR(TIMESERVER)',date("Y", strtotime($time) ));
		$this->st->where('MONTH(TIMESERVER)',date("m", strtotime($time) ));
		$this->st->where('DAY(TIMESERVER)',date("d", strtotime($time) ));
		$this->st->where('Lon <',180);
		$this->st->where('Lon >',-180);
		$this->st->stop_cache();
		$data['total'] = $this->st->count_all_results();
// 		$data['total'] = 26;
		$data['number'] = count($data['nodes']);
		return $data;
	}
	
	public function getVehicleReport($vehicleID){
		$motor = $this->Vehicle_Model->getVehicle($vehicleID);
		$data['name'] = $motor->name;
		$data['plate_number'] = $motor->plate_number;
		$data['length_road'] = self::lengthRoad($vehicleID);
		$data['max_speed'] = self::maxSpeed($vehicleID);
		$data['moving_time'] = self::movingTime($vehicleID);
		//bug($data);exit;
		return $data;
		
	}
	
	public function lengthRoad($vehicleID,$day='',$month='',$year=''){
		$length = 0;
		$this->st->select('node1.*')->from("data$vehicleID AS node1");
		$this->st->where('SPEED >',0);
		if($year){
			$this->st->where('YEAR(TIMESERVER)',(int)$year);
		}
		if($month){
			$this->st->where('MONTH(TIMESERVER)',(int)$month);
		}
		if($day){
			$this->st->where('DAY(TIMESERVER)',(int)$day);
		}
		
		//$this->st->where('( SELECT node2.`SPEED` FROM (`'.$table.'` AS node2) WHERE node2.`POINTS` =  (node1.`POINTS` + 1)  ) > ','0');
		$this->st->order_by('POINTS DESC ');
		
		$data = $this->st->get()->result();
		foreach($data AS $index=>$v){
			if($index > 0 ){
				$length += $this->mapgps->distance($data[$index-1]->La,$data[$index-1]->Lon,$v->La,$v->Lon);
			}
		}
// 		bug($this->st->last_query()); exit;
		$km = round($length/1000);
		$met = $length - $km*1000;
		$string = (($km > 0 )?$km.' km ':'').(($met > 0 )?round($met).' m':'');
		return ($string!='')?$string:'0 m';
		//return $length;
	}
	
	public function maxSpeed($vehicleID,$day='',$month='',$year=''){
		$data['max'] = 0;
		$this->st->select('MAX(SPEED) AS max')->from("data$vehicleID");
		if($year){
			$this->st->where('YEAR(TIMESERVER)',(int)$year);
		}
		if($month){
			$this->st->where('MONTH(TIMESERVER)',(int)$month);
		}
		if($day){
			$this->st->where('DAY(TIMESERVER)',(int)$day);
		}
		$node = $this->st->get()->row();
// 		bug($this->st->last_query()); exit;
		if($node && $node->max){
			$data['max'] = round($node->max,2);
			$this->st->stop_cache();
			$this->st->select('TIMESERVER AS time_max')->from("data$vehicleID");
			$this->st->where('SPEED',$node->max);
			$this->st->order_by('POINTS DESC');
			$node = $this->st->get()->row();
			//$data['time'] = $node->time_max;
			$data['time'] = date("d/m/Y H:m:s", strtotime($node->time_max) );
		} else {
			$data['max'] = '0';
		}
		return $data;
	}
	
	public function movingTime($vehicleID,$day='',$month='',$year=''){
		$time = 0; // secon
		$this->st->select('*')->from("data$vehicleID");
		$this->st->where('SPEED >',0);
		if($year){
			$this->st->where('YEAR(TIMESERVER)',(int)$year);
		}
		if($month){
			$this->st->where('MONTH(TIMESERVER)',(int)$month);
		}
		if($day){
			$this->st->where('DAY(TIMESERVER)',(int)$day);
		}
		$this->st->order_by('POINTS ASC');
		$data = $this->st->get()->result();
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
// 		bug($this->st->last_query());
// 		bug($time);exit;
		$hour = round($time/3600,0,PHP_ROUND_HALF_DOWN);
		$minute = $time/60 - $hour*3600;
		$string = (($hour > 0 )?$hour.' giờ ':'').((round($minute) > 0 )?round($minute).' phút':'');
		return ($string!='')?$string:'0 phút';
		//return ($time > 0)? ($time/60):0;
		

	}
}