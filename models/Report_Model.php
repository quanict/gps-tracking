<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Report_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->dv = $this->load->database('mapgps',true);
		$this->node = $this->load->database('node',true);
		$this->demo = $this->load->database('nodedemo',true);
		$this->car = $this->load->database('car',true);
	}

	var $vehicle;

	function report_nodes($data=null,$type='day',$car_Fuel=FALSE,$day=1,$month=1,$year=1970){
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
	            $distance = distance($data[$index-1]->latitude,$data[$index-1]->longitude,$v->latitude,$v->longitude);

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
	                    $out['max_speed']['latlng'] = $this->mapgps->shortDegrees($v->latitude).','.$this->mapgps->shortDegrees($v->longitude);
	                }
	            }

	            if( !$fuel_price
	                ||  $fuel_price->time > strtotime($v->TIMESERVER)
	                || ($fuel_price_next && $fuel_price_next->time < strtotime($v->TIMESERVER) )
	            ) {
	                $fuel_price = $this->Vehicle_Model->get_fuel($v->TIMESERVER,' < ');
	                $fuel_price->price = json_decode($fuel_price->price,TRUE);
	                $out['fuel_price_time'][] = date("d/m/Y H:i:s", ($fuel_price->time) );
	                $fuel_price_next = $this->Vehicle_Model->get_fuel($v->TIMESERVER,' >= ');
	            }

	            if( strlen($this->vehicle->fuel_type) < 1){
	                $this->vehicle->fuel_type = 'ron-92';
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
}