<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->account = $this->load->database('account',true);
		$this->motor = $this->load->database('mapgps',true);
	}

	public function getLogin($user){
		$this->account->select("u.password, u.username, u.id AS uid, u.email, u.status ,u.expiry, u.test")->from('user AS u')->where(array('u.status' => 1));
		$this->account->where('u.username', ($user['username']));
		$query = $this->account->get();

		$data = $query->row();

		if($data){
			if($data->test == 1) return $data;
			$salt = explode(':',$data->password);
			if( isset($salt[1]) && $data->password == (md5($user['password'].':'.$salt[1]).':'.$salt[1]) ){
				$this->account->where('id', $data->uid);
				$this->account->update('user', array('lastvisitDate'=>date("Y-m-d H:i:s")));
				return $data;
			} else return false;
		}
		return false;
	}

	public function getVehicle($uid){
		$this->motor->select('m.id, m.imegps, m.plate_number, m.simcard')->from('motor AS m')->where('m.owner',$uid);

		return $this->motor->get()->row_array();

		//SELECT MIN(posx), MAX(posx), MIN(posy), MAX(posy)
		//FROM yourtable
	}
	public function userInfo($id){
		$this->account->select("*")->from('user')->where(array('status' => 1,'id'=>$id));
		$data = $this->account->get()->row();
		return $data;

	}
	public function updateInfo($data){
// 		bug('update data=');
// 		bug($data);exit;
		$this->account->where('id', $data['id']);
		$this->account->update('user', $data);
// 		echo $this->account->last_query();exit;
		return true;
	}
}