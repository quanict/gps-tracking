<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Vehicle extends MX_Controller {
	function Vehicle(){

		parent::__construct();
		$this->mapgps->checkLogin();
		$this->vstr = $this->uri->segment(2);
		$this->vid = mortorID($this->vstr,true);
		$this->color = config_item('color');

		$this->load->module('layouts');
		$this->template->set_theme('viettracker')->set_layout('vietracker');
		$this->motors = $this->Vehicle_Model->loadVehicles($this->session->userdata('uid'));
		$this->smarty->assign('motors',$this->motors);

		if( $this->session->userdata('uid') ){
		    $this->acountLogined = $this->mapgps->userInfo();
		}
		$this->smarty->assign('username',$this->acountLogined->fullname);


	}

	public function geocoding(){
		$headScript =''
			.'vmap.ini();'
			.'vmap.geocoding_search();'
		;
		$this->template->add_js_ready($headScript);
		$html='<div class="gps-tool clearfix" ></div>'
			.'<div class="gmap-area clearfix" >'
				.'<div id="gmap" style="" ></div>'
				.'<div id="gmap-status"></div>'
				.'<div id="gmap-address-search">'
					.'<div class="results"></div>'
					.'<div class="form" >'
						.'<form action="post" id="search-address">'
						.'<input type="text"  name="address" value="" >'
						.'<button type="submit" id="gbqfb"  name="search" ><span class="gbqfi"></span></button>'
						.'</form>'
			.'</div></div></div>';
		$this->template->write('content',$html);
		$this->template->render();

	}

	public function report(){

		if( $this->vstr == '' ){
			$motors = $this->Vehicle_Model->getTracks($this->session->userdata('uid'));
			if( isset($motors[0]->id) ){
				redirect('thong-ke/'.mortorID($motors[0]->id),'refresh');
			} else  show_404();
		} else if( $this->Vehicle_Model->checkDatabaseGPS($this->vid) ===false ){
			show_404();
		}
		$action = $this->uri->segment(3);
		if( $action && $this->url_suffix == 'json'){
			switch($action){
				case 'bieu-do':
					$data = $this->Vehicle_Model->report_all($this->vid,$this->input->get('day'),$this->input->get('month'),$this->input->get('year'));
					jsonData($data); exit;
					break;
				case 'diem-dung':
					$sWhere = array();
					$sWhere['vehicleID'] = $this->vid;
					if($this->input->get('day'))
						$sWhere['DAY(TIMESERVER) '] = $this->input->get('day');
					if($this->input->get('month'))
						$sWhere['MONTH(TIMESERVER) '] = $this->input->get('month');
					if($this->input->get('year'))
						$sWhere['YEAR(TIMESERVER) '] = $this->input->get('year');

					return $this->mapgps->dataTableAjax(array(),'Vehicle_Model','node_stop',$sWhere); break;
				case 'bieu-do-xang':
					$data = $this->Vehicle_Model->report_fuel($this->vid,$this->input->get('day'),$this->input->get('month'),$this->input->get('year'));
					jsonData($data); exit;
					break;
				case 'diem-do-xang':
					$sWhere = array();
					$sWhere['vehicleID'] = $this->vid;
					if($this->input->get('day'))
						$sWhere['DAY(TIMESERVER) '] = $this->input->get('day');
					if($this->input->get('month'))
						$sWhere['MONTH(TIMESERVER) '] = $this->input->get('month');
					if($this->input->get('year'))
						$sWhere['YEAR(TIMESERVER) '] = $this->input->get('year');

					return $this->mapgps->dataTableAjax(array(),'Vehicle_Model','node_stop_fuel',$sWhere); break;
				default:show_404();break;

			}
		} else if ($this->url_suffix == 'xls'){
			return self::exportExcel();
		} else {
			$data['repo'] = $this->Vehicle_Model->getVehicle($this->vid);
			$this->template->add_js('highcharts/highcharts.js');
			$this->template->write('content', self::status('report',false));
			$this->template->write_view('content', 'page/statistic',$data);

			if( $this->input->get('time') ){
				$script = 'repo.load("'.$this->input->get('time').'");';
			} else {
				$script = 'repo.load();';
			}
			$script.=" $('#vehicle-info').css({'height':'auto','padding-top':0}); ";
			$this->template->add_js_ready($script);
			$this->template->render();
		}

	}

	public function history(){
		/*
		 * http://www.geocodezip.com/
		* http://www.geocodezip.com/v3_animate_marker_xml.html
		* http://www.geocodezip.com/v3_GoogleEx_places-searchboxC.html
		http://www.geocodezip.com/v3_GoogleEx_directions-draggable2Xml.html
		http://www.geocodezip.com/v3_animate_marker_directions.html
		http://www.geocodezip.com/geoxml3_test/v3_geoxml3_kmltest_linkto.html?filename=cta.xml
		http://www.geocodezip.com/geoxml3_test/v3_geoxml3_cta_test.html
		http://www.geocodezip.com/geoxml3_test/v3_geoxml3_kmltest_categories_linktoA.html?filename=http://www.geocodezip.com/geoxml3_test/nzhistory_net_nzmap_locations_kml.xml
		http://www.geocodezip.com/geoxml3_test/www_cicloviaslx_comA.html
		http://www.geocodezip.com/geoxml3_test/tanagerproductions_testmapA.html
		http://www.geocodezip.com/samicbc_simple4d.asp

		http://demo.tutorialzine.com/2013/01/charts-jquery-ajax/
		*/
		if($this->url_suffix == 'json' && $this->vid ){

			$data = array('nodes'=>'');
			//$vid = $this->input->get('vehicle');
			$motor = $this->Vehicle_Model->getVehicle($this->vid);
			$data['name'] = $motor->name;
			$begin = $this->input->get('time');
			$end = $this->input->get('end');
			if($begin && $end){

				$nodes = $this->Vehicle_Model->loadNodeByTime($this->vid, $begin, $end );

			} else if($begin) {
				$nodes = $this->Vehicle_Model->loadNodeByTime( $this->vid ,$begin );
			}
// 			bug($nodes);exit;
// 			exit('bug data history');
			if(isset($nodes) && $nodes){
				$end = ($end)?$end:null;
				$stop = $this->Vehicle_Model->getStopNodeByTime($this->vid,$begin,$end,FALSE);
				foreach($nodes AS $index=>$node){
					$nodeStop = reset($stop); // First Element's Value
					$nodeStop_key = key($stop); // First Element's Key
					if($nodeStop && $node->id > $nodeStop->id){
						$data['nodes'][] = array(
								//'id'=>$nodeStop->id,
								'la'=>$this->mapgps->shortDegrees($nodeStop->la),
								'lo'=>$this->mapgps->shortDegrees($nodeStop->lo),
								't'=>strtotime($nodeStop->t),
								'speed'=>0,
								//'gps'=>$nodeStop->gps,
								//'gsm'=>$nodeStop->gsm,
								'moved'=>''
						);
						unset( $stop[$nodeStop_key] );
					}
					if( isset($nodes[$index-1]) ){
						$moved = (isset($nodes[$index-1]))?$this->mapgps->distance($nodes[$index-1]->la,$nodes[$index-1]->lo,$node->la,$node->lo):0;
						if($moved=='NAN'){
							$moved = 0;
						}
					} else {
						$moved = 0;
					}
					$moved = number_format($moved,4,'.','');
					$data['nodes'][] = array(
							//'id'=>$node->id,
							'la'=>number_format($node->la,6,'.',''),
							'lo'=>number_format($node->lo,6,'.',''),
							//'time'=>date("d/m/Y H:i:s", strtotime($node->t)),
							't'=>strtotime($node->t),
							'speed'=>$node->speed,
							//'gps'=>$node->gps,
							//'gsm'=>$node->gsm,
							'moved'=>$moved
					);
				}
			} if( !$data['nodes'] ){
				$data['nodes'][] = $this->Vehicle_Model->getLastNode($this->vid,array('La <'=> 90,'La >'=>-90,'Lon <'=> 180,'Lon >'=> -180));
			}
			$data['timestamp'] = strtotime('now')*1000;
			return jsonData($data);
		} else {
			$headScript =''
			//.'vmap.playBackLink = "'.site_url().'du-lieu/lich-su.kml";'
			.'vmap.ini();'
			//.'$("body").append(vmap.mapGuide);'
			//.'$(".node-guide").css({"margin-top":0});'
			.'playback.create();'
			;
			$this->template->add_js_ready($headScript);
			$this->template->write('content', self::status('history',false));
			$this->template->write('content', '<div class="gmap-area clearfix" ><div id="gmap" style="" ></div></div>');
			$this->template->render();
		}

	}


	public function trackall(){
		$motorIDs= $this->input->get('vehicle');
		if($this->url_suffix == 'json' OR $this->uri->extension =='json'){
				$jsons = array();
				$this->session->set_userdata(array('traking'=>$motorIDs));

				if( isset($motorIDs) &&  is_array($motorIDs) && count($motorIDs)>0){

					foreach($motorIDs AS $vstr){
						$vid = mortorID($vstr,true);
						if($vid !='*' && $this->Vehicle_Model->checkOwnerGPS($vid) ){
						    $jsons[] = $this->Vehicle_Model->getLastNode($vid);
						}

					}
				}
				$jsons['timestamp'] = strtotime('now')*1000;
				return jsonData($jsons);
		}

		$script = '';


		foreach($this->motors AS $k=>$it){
			$script .="tracking.polyline[".$it->id."] =  new google.maps.Polyline({ strokeColor: '#".$this->color[$k]."'}); $('#vehicle-info').css({'height':22,'padding-top':5});";
		}
		$this->template

		->build('pages/trackall',array('js_ready'=>"vmap.ini(); $script tracking.ini(); vmap.autoLoad('tracking.track');"));
// 		$this->template->add_js_ready(' vmap.ini(); '.$script.' tracking.ini(); vmap.autoLoad("tracking.track"); ');
// 		$this->template->write('content', self::status('tracking',true));
// 		$this->template->write('content', '<div class="gmap-area clearfix" ><div id="gmap" style="" ></div><div id="gmap-status"></div><div id="gmap-address" lo="" la="" ></div></div>');
// 		$this->template->render();

	}

	public function trackone(){
		if( !$this->vid || $this->Vehicle_Model->checkDatabaseGPS($this->vid) === false ){
			show_404();
		} else if ($this->url_suffix == 'json'){
// 			if( $this->vehicleType =='car' ){
// 				$data = $this->Vehicle_Model->getLastNodeCar($this->vid);
// 			} else {
				$data = $this->Vehicle_Model->getLastNode($this->vid);
// 			}

			$data->timestamp = strtotime('now')*1000;
			return jsonData($data);
		}
		$headScript =''
		.'vmap.trackingLink = "'.site_url().'theo-doi/'.$this->vstr.'.json";'
		//.'vmap.playBackLink = "'.site_url().'du-lieu/lich-su.kml?vehicle='.$motorID.'";'
		.'vmap.ini();'
		."tracking.polyline[".$this->vid."] =  new google.maps.Polyline({ strokeColor: '#".$this->color[0]."'});"
		.'tracking.trackingOneIni();'
		//.'$("#gps-vehicles").val('.$this->vid.');'
		.'$(".gmap-area").css({"margin-top":"10px"});'
		//."$('#vehicle-info').css({'height':22,'padding-top':5});"
		//.'vmap.tracking();'
		//.'window.setInterval(function() { vmap.tracking();}, 5000);'
		;
		$this->template->add_js_ready($headScript);
		$this->template->write('content', self::status('tracking',false));
		$data['vehicle'] = $this->Vehicle_Model->getVehicle($this->vid);
		$this->template->write_view('content', 'page/tracking_one',$data);
		$this->template->render();
	}

	public function nodeRow(){
		if($this->url_suffix == 'json' && $this->vid ){
			$time=$this->uri->segment(3);
			if( $time) {
				$node =$this->Vehicle_Model->getNode($time,$this->vid);
				$data = array(
						'la'=>number_format($node->la,6,'.',''),
						'lo'=>number_format($node->lo,6,'.',''),
						't'=>strtotime($node->t),
						'speed'=>$node->speed,
						'gps'=>$node->gps,
						'gsm'=>$node->gsm,
				);
			} else {
				$data = array();
			}

			return jsonData($data);
		}
		show_404();
	}

	private function status($type='',$multiple=false){
		$out='';
		if($type == 'report'){
			$out.= '<div id="vehicle-info">';
			$out.='<div class="cenal" >'
				.'<input type="button" value="<< ThÃ¡ng TrÆ°á»›c" title="Previous" name="time-pre" >'
				.'<span style="display: inline-block; margin: 0 5px;">'.lang('Day').'</span>'
				.'<select name="rday"><option value="0" >'.lang('All Day').'</option>';
			for($i=1; $i <= 31 ;$i++){
				$out.='<option value="'.$i.'" >'.$i.'</option>';
			}
			$out.='</select>'
				.'<input type="button" value="ThÃ¡ng Sau >>" title="Next" name="time-next" >'
				.'<span style="display: inline-block; margin: 0 5px 0 15px; font-weight: bold; color: #2779AA;" name="statistic_title" ></span>'
				.'<input type="hidden" name="rmonth" value="0">'
				.'<input type="hidden" name="ryear" value="0">';
			if( $this->vstr != null ) {
				$out.='<input type="hidden" name="vid" value="'.$this->vstr.'">';
			}
			$out.='</div>';

			$out.='</div>';
		} else if ( $type == 'history' ) {
			$item = $this->Vehicle_Model->getInfo($this->vid);
			$beginSet = ( $this->input->get('begin') && ( $this->input->get('begin') <= strtotime('now') ) )? date('d-m-Y',$this->input->get('begin')):'';
			$out.='<div class="clfloat" >';
			if( $item && isset($item->type) && $item->type == -1 ){
				$out.='<div class="clfloat" style=" padding-left: 55px;"><span class="lable"></span><input type="text" name="gps-time-begin" class="ui-date" value="'.$beginSet.'" /></div>'
						.'<div class="clfloat" style="padding-left: 5px;">'
						.'Ä�áº¿n NgÃ y<input type="text" name="gps-date-end" class="ui-date" value="'.$beginSet.'" />'
						.'</div>'
						.'<button class="gps-button" style="margin-left: 5px;  display: none;" name="gps-date-option" ></button>'
				;
			} else {
				$out.='<div class="clfloat" style=" padding-left: 55px;">'
					.lang('At Day').'<input type="text" name="gps-date" class="ui-date" value="'.$beginSet.'" min="" readonly="true" />'
					.'</div>'
					.'<div class="clfloat" style="padding-left: 10px;">'
					.lang('Time Begin').'<input type="text" name="gps-time-begin" class="ui-time" value="" readonly="true" />'
					.'</div>'
					.'<div class="clfloat" style="padding-left: 10px;">'
					.lang('Time End').'<input type="text" name="gps-time-end" class="ui-time" value="" readonly="true" />'
					.'</div>';
			}
			$out.='</div>';
		} else if ( $type =='tracking' ) {
			$out.= '<div id="vehicle-info"></div>';
		}

		/*
		 * bein select box
		 */
		$vehiclesFload = '';
		switch($type){
			case 'history':
				$vehiclesFload = 'clfloat'; break;
			case 'report':
			default :
				$vehiclesFload = 'vehicles crfloat'; break;
		}
		$out.='<div class="'.$vehiclesFload.' ">'
			.'<span style="display: inline-block; padding: 0 5px;">Chá»�n PhÆ°Æ¡ng Tiá»‡n</span>'
			.'<select id="gps-vehicles" '.( ($multiple == true )?' multiple="multiple"':'' ).' >';
		if($multiple == true ){
			$out.='<option value="*">(Táº¥t Cáº£ Thiáº¿t Bá»‹)</option>';
			$this->template->add_js_ready(' $("#gps-vehicles").dropdownchecklist({ firstItemChecksAll: true, icon: {}, width: 250}); ');

		}
//
		$all = ($this->session->userdata('traking'))?TRUE:FALSE;

		$vehicles = $this->Vehicle_Model->getTracks($this->session->userdata('uid'));
		if($vehicles && count($vehicles) > 0){
			foreach($vehicles AS $item){
				if( $multiple ){
					//$selected = (!$all || ( $all && in_array($item->id, $this->session->userdata('traking')) ) )?' selected ':'';
					if( ! $this->session->userdata('traking') ){
						$selected = ' selected="selected" ';
					} else {
						$selected = ( in_array( mortorID($item->id) , $this->session->userdata('traking')) )?' selected="selected" ':'';
					}


				} else {
					$selected = ( $this->vid == $item->id )?' selected="selected" ':'';
				}
// 				bug('item ID ='.($item->id).'vid='.$this->vid.' $selected='.$selected);
// 				$selected = ( !isset($multiple) || !$multiple )?'':$selected;
				$out.='<option value="'.mortorID($item->id).'" '.$selected.' >'.$item->name.'</option>';
			}
// 			bug($this->session->userdata('traking'));exit;
		}
		$out.='</select></div>';

		/*
		 * end select box
		*/
		if($type == 'history'){
			$out.='<div class="clfloat" style="margin-top:2px;">'
					.'<button name="gps-history-submit" class="submit ui-button" >Xem Dá»¯ Liá»‡u</button>'
					.'<button name="gps-history-play" class="ui-button" >Báº¯t Ä�áº§u</button>'
				.'</div>'
				.'<div class="clfloat" style="width: 200px; margin-top:7px; margin-left:15px;">'
					.'<div class="ui-slider background-green" name="playback-speed" ></div>'
				.'</div>';
		}

		$out = '<div class="gps-tool clearfix" >'
			//.'<div id="vehicle-info">'
			.$out
			//.'</div>'
			.'</div>';
		return $out;
	}

	protected function exportExcel(){

		$time = basename($this->uri->segment(3), ".xls");
		$date = explode('-', $time);
// 		bug($time);
// 		bug($date);
// 		bug(count($date));
// 		exit(count($date));
		if( count($date) != 3 || $date[2] < 2012 ||  $date[1]>12 || $date[1] <1 || $date[0] > 31 ){
			show_404();exit;
		}

		$this->load->library('PHPExcel');
		$this->load->library('PHPExcel/IOFactory');
// 		bug(BASEPATH);exit;
		$objReader = IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load(BASEPATH."export/nodes-stop.xls");
// 		$objPHPExcel = new PHPExcel();
//
		$objPHPExcel->getProperties()
					->setCreator("VietTracker.vn")
					->setLastModifiedBy("VietTracker.vn")
					->setTitle("Du Lieu Thong Ke Cac Diem Dung")
					->setSubject("Du Lieu ngay $time")
					->setDescription("Du Lieu Thong Ke Diem Dung ngay $time (thong ke boi VietTracker.vn) ")
					->setKeywords("export nodes stop by viettracker ")
					->setCategory("Data export");


		$sWhere['vehicleID'] = $this->vid;
		if( $date[0] != 0 ){
			$sWhere['DAY(TIMESERVER) '] = $date[0];
			$filename="du-lieu-thong-ke-diem-dung-ngay-$time";
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7", 'Dá»¯ Liá»‡u Thá»‘ng KÃª CÃ¡c Ä�iá»ƒm Dá»«ng NgÃ y '.$time);
		} else {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7", 'Dá»¯ Liá»‡u Thá»‘ng KÃª CÃ¡c Ä�iá»ƒm Dá»«ng ThÃ¡ng '.$date[1].'-'.$date[2]);
			$filename="du-lieu-thong-ke-diem-dung-thang-$date[1]-$date[2]";
		}
		$sWhere['MONTH(TIMESERVER) '] = $date[1];
		$sWhere['YEAR(TIMESERVER) '] = $date[2];
		$data = $this->Vehicle_Model->node_stop(0,null,null,$sWhere);

		if( $data && isset( $data['data'] ) && count($data['data']) > 0 ){
			foreach( $data['data'] AS $k => $node ){
				$index = 9+$k;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A$index", $node[0])
					->setCellValue("b$index", $node[1])
					->setCellValue("C$index", $node[2])
					->setCellValue("D$index", $node[3])
					->setCellValue("E$index", $node[4])
					->setCellValue("F$index", strip_tags($node[5]) );
			}
		} else {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A9", 'KhÃ´ng CÃ³ Ä�iá»ƒm Dá»«ng NÃ o');
		}


		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('CÃ¡c Ä�iá»ƒm Dá»«ng');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		// Redirect output to a clientâ€™s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');

	}

}