<?php
class Mapgps {
	function __construct(){
// 		exit('call me');
		$this->CI =& get_instance();
	}
	function checkLogin(){
		if (!$this->CI->session->userdata('uid')){
			//if($this->CI->uri->uri_string()!='dang-nhap'){
				redirect('dang-nhap','refresh',null,'r='.urlencode(current_url()) );
			//}
		}
		if(!$this->CI->session->userdata('uid') && $this->CI->uri_string()!='dang-nhap'){
			redirect('dang-nhap','refresh');
		}
	}

	function checkPermission(){

	}

	public function userInfo(){
		$user = $this->CI->Account_Model->userInfo($this->CI->session->userdata('uid'));
		if(!$user){
			if($this->CI->uri->uri_string() !='dang-xuat' &&  $this->CI->uri->uri_string()!='dang-nhap')
				redirect('dang-xuat','refresh');
		}
		if( !isset($user->fullname) ||  !$user->fullname){
			$user->fullname = lang('unnamed');
		}
		return $user;
	}

	public function dataTableAjax($aColumns,$model,$getDataFunction,$sWhere=null){
		$aColumns = array('id','a.image','a.company','a.create','a.click');
		$sStart = $sLength =0;
		$sStart = $this->CI->input->get('iDisplayStart');
		$sLength = $this->CI->input->get('iDisplayLength');
		$sOrder = array();
		if ( isset( $_GET['iSortCol_0'] ) ){
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ){
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ){
					$sOrder[]=array($aColumns[ intval( $_GET['iSortCol_'.$i] )],$_GET['sSortDir_'.$i]);
				}
			}
		}
		$data = $this->CI->$model->$getDataFunction($sStart,$sLength,$sOrder,$sWhere);
		$dataReturn =  array(
				"sEcho" => $this->CI->input->get('sEcho'),
				"iTotalRecords" => $data['totalRecords'],
				"iTotalDisplayRecords" => $data['totalRecords'],
				"aaData" => $data['data']
		);

		return jsonData($dataReturn);
	}

	public function geocode($q=''){
		$address = 'address detail';
		if($q){
			$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.$q.'&sensor=false&language=vi';
			$data = @file_get_contents($url);
			$jsondata = json_decode($data,true);
			//bug($jsondata);exit;
			if( $jsondata['status'] == 'OK' ){
				if ( isset($jsondata['results'][0]['formatted_address']) ){
					$address =  $jsondata['results'][0]['formatted_address'];
				} else {
					$address = 'KhÃ´ng XÃ¡c Ä�á»‹nh....';
				}

			}
		}
		return $address;

	}

	public function shortDegrees($float){
		$float = number_format($float,6,'.','');
		return (($float - intval($float)) > 0)?$float: intval($float);
	}

	public function printDate($date){
		return (is_date($date))?date("d/m/Y",strtotime($date) ):'<span class="value-incorrect" >'.$this->CI->lang->line('value incorrect').'</span>';
	}

	public function pageTitle(){
		$title = 'Page title';
		if( !isset($this->CI->page_title) ){
			return $title;
		}
		if(is_array($this->CI->page_title) && count($this->CI->page_title) > 0 ){
			$title= $this->CI->page_title[count($this->CI->page_title)-1];
		} else {
			$title = $this->CI->page_title;
		}
		return $title;
	}

	public function distance($lat1, $lng1, $lat2, $lng2, $miles = true) {
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
}