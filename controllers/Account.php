<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Account extends MX_Controller {
	function Account(){
		parent::__construct();

		$this->load->module('layouts');
// 		$this->load->library('layouts/template');
		$this->template->set_theme('apricot')
		->set_layout('bootstrap');

		$this->userSession = array(
				'uid'=> 0,
				'logged_in'=>false,
		);
		$this->load->model('Statistic_Model');
	}
	public function index(){
		$form['fields'] = self::userFields();
		$userinfo = $this->Account_Model->userInfo($this->session->userdata('uid'));
		foreach($form['fields'] AS $key=>$val){
			if(isset($userinfo->$key)){
				$form['fields']->$key->value = $userinfo->$key;
			}
			$form['fields']->$key->disabled = true;
		}
		$css = 'form fieldset > label {padding:0;}'
		.'form fieldset > div.clearfix > span  {line-height: 15px;} '
		.'form fieldset {padding: 0;} '
		;
		$this->template->add_css($css,'embed');
		$this->template->write('content', self::menu());
		$this->template->write_view('content', 'account/view',$form);
		$this->template->render();
	}

	public function changeInfo(){
		$this->mapgps->checkLogin();
		$fields = array(
				'fullname'=>'',
				'gender'=>array('type'=>'gender'),
				'phone'=>'',
				'phone_2'=>'',
				'phone_3'=>'',
				'phone_4'=>'',
				'email'=>array('type'=>'email'),
				'address'=>'',
				'vehicle_id'=>array('disabled'=>true),
				'vehicle_simcard'=>array('disabled'=>true)
		);
		$form['fields'] = (object)$this->form->bindFields($fields);
		if($this->input->post()) {
			foreach ($form['fields'] AS $key=>$value){
				$form['fields']->$key->value = $this->input->post($key);
			}
			$updateData = $this->form->formValue($form['fields']);
			$updateData['id']=$this->session->userdata('uid');
			$this->Account_Model->updateInfo($updateData);
			redirect('tai-khoan');
		}

		$userinfo = $this->Account_Model->userInfo($this->session->userdata('uid'));
		foreach($form['fields'] AS $key=>$val){
			if(isset($userinfo->$key)){
				$form['fields']->$key->value = $userinfo->$key;
			}
		}
		$form['buttons'] = array(
				array('title'=>'Save Data', 'type'=>'submit'),
				array('title'=>'Cancel', 'type'=>'cancel','attributes'=>' class="cancel ui-button" '),
		);
		$this->template->write('content', self::menu());
		$this->template->write_view('content', 'account/form',$form);
		$this->template->render();
	}

	public function changePassword(){
		$this->mapgps->checkLogin();
		$this->page_title[] = lang('Change Password');
// 		echo hexdec('344A62');exit;
		$fields = array(
			'password_old'=>array('type'=>'password'),
			'password'=>array('type'=>'password','title'=>lang('New Password')),
			'password_re'=>array('type'=>'password'),
			//'capcha'=>array('type'=>'capcha'),
		);
		$form['fields'] = (object)$this->form->bindFields($fields);
		if($this->input->post()) {
			//if($this->form->checkInputCapcha() != true){
			//	$this->msg[]= array('type'=>'error','content'=>'Nhập <b>Mã Bảo Vệ</b> không đúng');
			//} else {
				$userinfo = $this->Account_Model->userInfo($this->session->userdata('uid'));
				$user['username'] = $userinfo->username;
				$user['password'] = $this->input->post('password_old');

				$userData = $this->Account_Model->getLogin($user);
				if($userData){
					if($this->input->post('password')=='' || $this->input->post('password_re')==''){
						$this->msg[]= array('type'=>'error','content'=>'Bạn phải nhập vào mật khẩu hợp lệ');
					} else if($this->input->post('password') == $this->input->post('password_re')){
						$updateData['id']=$this->session->userdata('uid');
						$salt = $this->form->genRandomString(10);
						$updateData['password']= md5($this->input->post('password').":".$salt).':'.$salt;
						$this->Account_Model->updateInfo($updateData);
						$this->session->unset_userdata($this->userSession);
						redirect('dang-nhap', 'refresh');
						// 					redirect('tai-khoan');
					} else {
						$this->msg[]= array('type'=>'error','content'=>'Mật khẩu nhập lại không giống Mật khẩu mới');
					}
				} else {
					$this->msg[]= array('type'=>'error','content'=>'Mật khẩu cũ nhập không đúng');
				}
			//}
		}
		$form['buttons'] = array(
				array('title'=>'Change Password', 'type'=>'submit'),
				array('title'=>'Cancel', 'attributes'=>' class="cancel ui-button" ','type'=>'cancel'),
		);
		$this->template->write('content', self::menu());
		$this->template->write_view('content', 'account/form',$form);
		//$this->template->write('title', $this->lang->line('Change Password') );
		$this->template->write('title', $this->mapgps->pageTitle() );
		$this->template->render();
	}

	protected function userFields(){
		$fields = array(
				'username'=>array('request'=>true),
				'email'=>array('type'=>'email','request'=>true),
				'fullname'=>'',
				'gender'=>array('type'=>'gender'),
				'phone'=>'',
				'phone_2'=>'',
				'phone_3'=>'',
				'phone_4'=>'',
				'address'=>'',
				'register_date'=>'',
// 				'expiry'=>'',
// 				'vehicle_id'=>'',
// 				'vehicle_simcard'=>''
		);
		return (object)$this->form->bindFields($fields);
	}

	public function login(){
		if ($this->session->userdata('uid')){
			redirect('theo-doi','refresh');
		}
		if($this->input->post()) {
			return self::checkLogin();
		}



		$this->template
		->set_layout('login')
		->build('pages/login',array());

// 		$this->template->set_template('login');
// 		$this->template->add_css('gps-login.css');
// 		$this->title = lang('Login');
// // 		$this->template->write_view('leftcontent', 'modules/gps-solution');
// 		$form['fields'] = array( 'username'=>array('lable'=>lang('username lable') ),'password'=>array('type'=>'password','lable'=>lang('password lable') ) );
// 		$this->template->write_view('content', 'account/login-form2',$form);

// 		$this->template->render();
	}

	protected function checkLogin(){
		$user['username'] = strtolower($this->input->post('username'));
		$user['password'] = $this->input->post('password');

		$userData = $this->Account_Model->getLogin($user);
		if(($userData)){

			if($userData->status  != 1){
				$this->msg[]= array('type'=>'error','content'=>'Account not active. Please click '.anchor('account/active?email='.$userData['email'],'Active'));
			} else {
				$this->userSession =  array(
						'uid'=> $userData->uid,
						'logged_in' => TRUE
				);
				$this->session->set_userdata($this->userSession);

				$continue = ( $this->input->get('r')!='' )?$this->input->get('r'):'theo-doi';
// 				if($this->input->get('format')=='json'){
// 					//return returnJson(true);
// 				} else {
					redirect($continue);
// 				}
			}
		} else {
			unset($_POST);
			$this->msg[]= array('type'=>'error','content'=> $this->lang->line('Login False') );
			if($this->input->get('format')=='json'){
				//return returnJson(false);
			} else {
				return self::login();
			}

		}

	}

	public function logout(){
		$this->session->unset_userdata('traking');
		$this->session->unset_userdata($this->userSession);
		redirect('dang-nhap', 'refresh');
	}


	/*
	 * manager vehicle
	*/
	public function manager(){
		$this->template->write('content', self::menu());

		$subPage = $this->uri->segment(2);
		if($this->input->post()) {
			switch ($this->uri->segment(2)){
				case 'sua-thiet-bi':
					self::updateMotor();
					break;
				case 'mo-thiet-bi':
				case 'tat-thiet-bi':
					break;
				default: show_404(); break;
			}
		}
// 		if( $subPage == 'sua-thiet-bi' && $this->input->post()){
// 			self::updateMotor();
// 		} else if ( $subPage == 'tat-thiet-bi' && $this->input->post() ){
// 			self::shutdownVehicle();
// 		}
		else {
			$data['rows'] = $this->Vehicle_Model->getTracks($this->session->userdata('uid'));
			$this->template->write_view('content', 'account/vehicles',$data);
			$this->template->add_js_ready('manager.tableAction();');
		}
		$this->template->render();
	}

	protected function updateMotor(){
		$this->page_title[] = 'Sửa Dữ Liệu Thiết Bị';
		$vid = mortorID($this->input->post('imei') ,true);
// 		bug('vid='.$vid); exit;
		$this->form->formField = self::motorFields();

		$opt = array(
				'id'=>$vid,
				'item-name'=>'Motor',
				'model'=>'Vehicle_Model',
				'model-get'=>'getVehicle',
				'model-update'=>'updateVehicle',
				'uri-back'=>'quan-ly/thiet-bi'
		);
		if( !$this->input->post('id') && $this->Vehicle_Model->checkOwnerGPS($vid) !=false){
			$this->input->unset_post();
// 			bug($opt);exit;
			$this->form->viewForm($opt);
		} else {
// 			exit('submit data');
			$this->form->submitForm($opt);
		}
	}

	protected function shutdownVehicle(){
		$vid = mortorID($this->input->post('imei') ,true);
// 		bug($vid);
		$device = $this->Vehicle_Model->getVehicle($vid);
		if( $vid === null || !$device ){
			redirect('quan-ly/thiet-bi','refresh');
		}
// 		bug($device);exit;
		if($device)
			$this->page_title[] = 'Tắt Nguồn Thiết Bị <i>'.$device->name.'</i>';

		if($this->input->post()){
			$this->form->formField = self::shutdownFields();
			$opt = array(
					'id'=>$vid,
					'item-name'=>'Motor',
					'model'=>'Vehicle_Model',
					'model-get'=>'getVehicle',
					'model-update'=>'updateVehicle',
					'uri-back'=>'quan-ly/thiet-bi'
			);
			if($this->input->post('imei')){
				$this->input->unset_post();
				$this->form->viewForm($opt);
			} else {
// 				$device = $this->Vehicle_Model->getVehicle($this->input->post('id'));
// 				if($device)
// 					$this->page_title[] = 'Tắt Nguồn Thiết Bị <i>'.$device->name.'</i> (id: '.$device->id.')';
				if($this->form->checkInputCapcha() != true){
					$this->msg[]= array('type'=>'error','content'=>'Nhập <b>Mã Bảo Vệ</b> không đúng');
				} else {
					$userinfo = $this->Account_Model->userInfo($this->session->userdata('uid'));
					$user['username'] = $userinfo->username;
					$user['password'] = $this->input->post('password');
					$userData = $this->Account_Model->getLogin($user);
					if($userData){
						$this->Vehicle_Model->updateVehicle(array('id'=>$this->input->post('id'),'shutdown'=>1));
						redirect('quan-ly/thiet-bi','refresh');
					} else {
						$this->msg[]= array('type'=>'error','content'=>'Mật khẩu nhập không đúng');
					}
				}
				$this->template->write_view('content', 'pages/form-edit',array('form'=>$this->form->formField));
			}

			//$this->template->render();
		}else {
			redirect('quan-ly/thiet-bi','refresh');
		}
	}

	protected function shutdownFields(){
		$fields = array(
				'id'=>array('type'=>'hidden','value'=>0),
				'password'=>array('type'=>'password','t'=>'password'),
				'capcha'=>array('type'=>'capcha'),
				// 			'imei'=>array('type'=>'hidden','value'=>$this->input->post('imei')),
		);
		return (object)$this->form->bindFields($fields);
	}

	protected function motorFields(){
		$fields = array(
			'id'=>array('type'=>'hidden','value'=>0),
			'name'=>array('title'=>'Device Name'),
			'plate_number'=>array('title'=>'Plate Number'),

			'fuel'=>array('title'=>'Fuel Consumed','type'=>'unit','lable'=>lang('Petrol Unit/100km'),'unit'=>'number'),
			'fuel_price'=>array('type'=>'unit','lable'=>lang('VND/1Fuel'),'unit'=>'number'),
			//'owner'=>array('type'=>'owner'),
			'simcard'=>array('title'=>'Vehicle_simcard','disabled'=>true),
			'imei'=>array('title'=>'IMEI Number','disabled'=>true),
			'created'=>array('type'=>'date','disabled'=>true),
			'expiry'=>array('type'=>'date','disabled'=>true),


		);
		return (object)$this->form->bindFields($fields);
	}

	/*
	 * END manager Vehicle
	*/

	protected function menu(){
		$out = '<div class="block-left"><div class="content-box">'
			.'<div class="box-body">'
				.'<div class="box-header clear">'
				.'<h2 class="fl" >'.lang('Account Management').'</h2>'
				.'</div>'
			.'</div>'
			.'<div class="box-wrap clear" ><ul>'
				.'<li>'.anchor('quan-ly/thiet-bi', lang('Device Management') ).'</li>'
				.'<li>'.anchor('tai-khoan/doi-mat-khau',lang('Change Password') ).'</li>'
				.'<li>'.anchor('tai-khoan',lang('Account Info') ).'</li>'
			.'</ul></div>'
			.'</div></div>';
		return $out;
	}


}