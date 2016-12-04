<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Account extends MX_Controller {
	function Account(){
		parent::__construct();
		$this->userSession = array(
				'uid'=> 0,
				'logged_in'=>false,
		);
		$this->load->model('Statistic_Model');
	}

	var $user_fields = array(
	    'username'=>array('request'=>true,'disabled'=>true),
	    'email'=>array('type'=>'email','request'=>true, 'disabled'=>true),
	    'fullname'=>'',
	    'gender'=>array('type'=>'gender'),
	    'phone'=>'',
// 	    'phone_2'=>'',
// 	    'phone_3'=>'',
// 	    'phone_4'=>'',
	    'address'=>'',
	    'register_date'=>array('type'=>'date','disabled'=>true),
	);

	public function index(){
        return $this->changeInfo();
	}

	public function changeInfo(){
	    $this->mapgps->checkLogin();
		if( $this->input->post() ) {
            $update = array();
            foreach ($this->user_fields AS $key=>$v){
                if( !isset($v['disabled']) OR $v['disabled']!=true ){
                    $update[$key] = $this->input->post($key);
                }
            }
            $update['id'] = $this->session->userdata('uid');
            $this->Account_Model->updateInfo($update);
            redirect('tai-khoan');
        }

		$userinfo = $this->Account_Model->userInfo($this->session->userdata('uid'));
		foreach($this->user_fields AS $key=>$val){
			if(isset($userinfo->$key)){
				$this->user_fields[$key]['value'] = $userinfo->$key;
			}
		}


		$this->template->set_theme('viettracker')
		->set_layout('vietracker');
		$this->smarty->assign('show_tool', false);
		$this->template->build('pages/form',array('fields'=>$this->user_fields));
	}

	public function changePassword(){

		$this->mapgps->checkLogin();
// 		$this->page_title[] = lang('Change Password');

		$fields = array(
			'password_old'=>array('type'=>'password'),
			'password'=>array('type'=>'password','label'=>lang('New Password')),
			'password_re'=>array('type'=>'password'),
			//'capcha'=>array('type'=>'capcha'),
		);
		$msg = NULL;
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
					    $msg = 'Must input password';
					} else if($this->input->post('password') == $this->input->post('password_re')){

					    $updateData['id']=$this->session->userdata('uid');

					    $salt = random_string('alpha',10);
						$updateData['password']= md5($this->input->post('password').":".$salt).':'.$salt;

						$this->Account_Model->updateInfo($updateData);
						$this->session->unset_userdata($this->userSession);
						redirect('dang-xuat');
					} else {
					    $msg = 'Password not same';
					}
				} else {
					$msg = 'Incorrect current password';
				}
			//}
		}

		$this->template->set_theme('viettracker')
		->set_layout('vietracker');
		$this->smarty->assign('show_tool', false);
		$this->smarty->assign('error', $msg);
		$this->template->build('pages/form',array('fields'=>$fields));
	}


	public function login(){
		if ($this->session->userdata('uid')){
			redirect('tracking','refresh');
		}
		if($this->input->post()) {
			return self::checkLogin();
		}


		$this->template->set_theme('apricot')
		;
		add_css('signin.css');
		$this->template
		->set_layout('login')
		->build('pages/login',array());

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
		$this->session->unset_userdata('uid');
		redirect('dang-nhap', 'refresh');
	}


	/*
	 * manager vehicle
	*/


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




}