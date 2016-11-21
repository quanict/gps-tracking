<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Adv_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->adv = $this->load->database('mapgps',true);
	}
	public function get_advs($category_alias='',$limit=1){
		//$category = $this->Category_Model->getCategory(array('alias'=>$category_alias));
		//if($category){
			$this->adv->select('adv.title, adv.url, adv.content , adv.image')->from('advertising AS adv');
			$this->adv->join('category AS cate', 'cate.id = adv.adv_type', 'left');
			$this->adv->where(array('adv.publish'=>1));
			$this->adv->where(array('cate.alias'=>$category_alias));
			if($limit >0){
				$this->adv->limit($limit);
			}
			$this->adv->order_by('adv.ordering DESC ');
			return $this->adv->get()->result();
		//} else {
		////	return null;
		//}
	}
	
	public function getSlide(){
		$this->adv->select('id, title, url, content , image, ordering')->from('advertising');
		$this->adv->where(array('publish '=>1,'adv_type'=>0));
		$this->adv->order_by('ordering DESC ');
		return $this->adv->get()->result();
	}
}