<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Report extends MX_Controller {
	function Report(){

	    parent::__construct();
	    $this->mapgps->checkLogin();
	    $this->load->model('Report_Model');

	    $this->load->module('layouts');
	    $this->template->set_theme('viettracker')->set_layout('vietracker');

	    $this->vstr = $this->uri->segment(3);
	    $this->vid = mortorID($this->vstr,true);
	}

	function index(){
	    $motors = $this->Vehicle_Model->getTracks($this->session->userdata('uid'));
	    if( isset($motors[0]->id) ){
	        redirect('report/item/'.mortorID($motors[0]->id));
	    }
	}

	public function item(){
	    if( $this->vstr == '' ){
	        $motors = $this->Vehicle_Model->getTracks($this->session->userdata('uid'));
	        if( isset($motors[0]->id) ){
	            redirect('thong-ke/'.mortorID($motors[0]->id),'refresh');
	        } else
	            show_404();
	    } else if( $this->Vehicle_Model->checkDatabaseGPS($this->vid) ===false ){
	        show_404();
	    }

	    $action = $this->uri->segment(4);
	    if( $action && $this->uri->extension =='json'){
            return $this->report_data_json($action);
	    } else if ($this->url_suffix == 'xls'){
	        return self::exportExcel();
	    } else {
	        $data['repo'] = $this->Vehicle_Model->getVehicle($this->vid);
	        $data['vstr'] = $this->vstr;


// 	        $this->template->add_js('highcharts/highcharts.js');
// 	        $this->template->write('content', self::status('report',false));
// 	        $this->template->write_view('content', 'page/statistic',$data);

	        if( $this->input->get('time') ){
	            $script = 'repo.load("'.$this->input->get('time').'");';
	        } else {
	            $script = 'repo.load();';
	        }
	        $script.=" $('#vehicle-info').css({'height':'auto','padding-top':0}); ";
// 	        $this->template->add_js_ready($script);
// 	        $this->template->render();
            add_js_ready($script);
//             add_js('../highcharts/highcharts.js');
            $data['header_ctr'] = 'blocks/report';
	        $this->template->build('pages/statistic',$data);

	    }
	}

	function report_data_json($action=NULL){
	    $db = $this->Vehicle_Model->checkDatabaseGPS($this->vid);
	    switch($action){
	        case 'bieu-do':
                if( $db=='demo' ){
                    $data = $this->Demo_Model->report($this->vid);
                } else {
                    $data = $this->Vehicle_Model->report_all($this->vid,$this->input->get('day'),$this->input->get('month'),$this->input->get('year'));
                }

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

	            if( $db=='demo' ){
	                return $this->mapgps->dataTableAjax(array(),'Demo_Model','node_stop',$sWhere);
	            } else {
	                return $this->mapgps->dataTableAjax(array(),'Vehicle_Model','node_stop',$sWhere);
	            }
	            break;
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
	}
}