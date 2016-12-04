<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Setting extends MX_Controller {
    function Setting(){
        parent::__construct();

        $this->template->set_theme('viettracker')
                ->set_layout('vietracker');
        $this->mapgps->checkLogin();

    }

    public function index(){
        // 		$this->template->write('content', self::menu());

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
            $data['vehicles'] = $this->Vehicle_Model->getTracks($this->session->userdata('uid'));
            add_js_ready('manager.tableAction();');
        }


        $this->smarty->assign('show_tool', false);

        $this->template->build('pages/vehicles',$data);
    }

    function vehicles(){
        $data['vehicles'] = $this->Vehicle_Model->getTracks($this->session->userdata('uid'));
        add_js_ready('manager.tableAction();');
        $this->smarty->assign('show_tool', false);
        $this->template->build('pages/vehicles',$data);
    }




    function shutdown(){
        $msg = NULL;
        $imei = $this->input->post('imei');
        $vid = mortorID($imei ,true);

        $device = $this->Vehicle_Model->getVehicle($vid);
        if( $vid === null || !$device ){
            redirect('setting/vehicles');
        }

        if($device)
            $data['page_title'] = lang('Tat nguoi thiet bi').' <i>'.$device->name.'</i>';

        $data['fields'] = array(
            'id'=>array('type'=>'hidden','value'=>$vid),
            'password'=>array('type'=>'password','t'=>'password'),
            'capcha'=>array('type'=>'capcha'),
            'imei'=>array('type'=>'hidden','value'=>$imei),
        );

        if($this->input->post()){
            $data['submit_title'] = lang('Shutdown Device');
            if( !isset($_POST['password'])){
                $this->template->build('pages/form',$data);
            } else {
//                 if($this->form->checkInputCapcha() != true){
//                     $this->msg[]= array('type'=>'error','content'=>'Nhập <b>Mã Bảo Vệ</b> không đúng');
//                 } else {
                    $userinfo = $this->Account_Model->userInfo($this->session->userdata('uid'));
                    $user['username'] = $userinfo->username;
                    $user['password'] = $this->input->post('password');
                    $userData = $this->Account_Model->getLogin($user);
                    if($userData){
                        $this->Vehicle_Model->updateVehicle(array('id'=>$this->input->post('id'),'shutdown'=>1));
                        redirect('setting/vehicles');
                    } else {
                        $msg= 'Incorrect current password';
                    }
//                 }
                    $data['error'] = $msg;
                    $this->template->build('pages/form',$data);
            }
        } else {
            redirect('setting/vehicles');
        }
    }

    function open(){
        $vid = mortorID($this->input->post('imei') ,true);

        $device = $this->Vehicle_Model->getVehicle($vid);
        if( $vid AND is_object($device) ){

            $this->Vehicle_Model->updateVehicle(array('id'=>$vid,'shutdown'=>0));

        }
        redirect('setting/vehicles');
    }

    function vehicle_info(){
        $msg = NULL;
        $textid = $this->input->post('textid');
        $vid = mortorID($textid ,true);

        $device = $this->Vehicle_Model->getVehicle($vid);

        if( $vid === null || !$device OR !$this->Vehicle_Model->checkOwnerGPS($vid)){
            redirect('setting/vehicles');
        }



        $data['fields'] = array(
			'id'=>array('type'=>'hidden','value'=>$vid),
            'textid'=>array('type'=>'hidden','value'=>$textid),
			'name'=>array('label'=>lang('Device Name'),'value'=>NULL),
			'plate_number'=>array('label'=>lang('Plate Number'),'value'=>NULL),

			'fuel'=>array('label'=>lang('Fuel Consumed'),'type'=>'unit','lable'=>lang('Petrol Unit/100km'),'unit'=>'number','value'=>NULL),
			'fuel_price'=>array('type'=>'unit','label'=>lang('VND/1Fuel'),'unit'=>'number','value'=>NULL),
			//'owner'=>array('type'=>'owner'),
			'simcard'=>array('label'=>lang('Vehicle Simcard'),'disabled'=>true,'value'=>$device->simcard),
			'imei'=>array('label'=>lang('IMEI Number'),'disabled'=>true,'value'=>NULL),
			'created'=>array('type'=>'date','disabled'=>true,'value'=>$device->created),
			'expiry'=>array('type'=>'date','disabled'=>true,'value'=>$device->expiry),


		);


        if( !$this->input->post('id') ){
            $data['page_title'] = lang('Update information vehicle');
            foreach ($data['fields'] AS $k=>$v){
                if( isset($device->$k)  ){
                    $data['fields'][$k]['value'] = $device->$k;
                }
            }
            $this->template->build('pages/form',$data);
        } else {
            $update = array();
            foreach ($data['fields'] AS $k=>$v){
                if( in_array($k, array('textid')) )
                    continue;
                if( !isset($v['disabled']) OR !$v['disabled'] ){
                    $update[$k] = $this->input->post($k);
                }
            }
            $this->Vehicle_Model->updateVehicle($update);
            redirect('setting/vehicles');
        }
    }

}