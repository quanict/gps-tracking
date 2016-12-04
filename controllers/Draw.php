<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Draw extends MX_Controller {
	function __construct(){
		parent::__construct();
		$this->load->module('layouts');
		$this->template->set_theme('apricot')->set_layout('ApricotMain');

		$this->mapgps->checkLogin();
		$this->vstr = $this->uri->segment(3);
		$this->vid = mortorID($this->vstr,true);
		$this->vid = ABS($this->vid);
		$this->db = $this->load->database('nodedemo',true);



	}

	function index(){

	}

	function node($vstr=null,$id=0){
	    $vid = ABS(mortorID($vstr,true));
	    $data['node'] = $this->db->where('id',$id)->get("motor$vid")->row();
	    $timestamp = strtotime($data['node']->TIMESERVER);

	    if( $this->input->post('h') ){
	        $data['node']->h = $this->input->post('h');
	    } else {
	        $data['node']->h = date('H',$timestamp);
	    }

	    if( $this->input->post('i') ){
	        $data['node']->i = $this->input->post('i');
	    } else {
	        $data['node']->i = date('i',$timestamp);
	    }


	    $data['node']->s = date('s',$timestamp);
	    if( $data['node']->s ==0 ){
	        $data['node']->i++;
	    }
	    $data['node']->date = date('Y-m-d',$timestamp);

	    $data['node_pre'] = $this->db->where('id <',$id)->order_by('id DESC')->limit(2)->get("motor$vid")->result();
	    $data['node_next'] = $this->db->where('id >',$id)->order_by('id ASC')->limit(5)->get("motor$vid")->result();
        if( abs($data['node']->angle) <=0 AND count($data['node_pre']) > 0){
            $node_pre = $data['node_pre'][0];
            $data['node']->angle = angle($node_pre->latitude,$node_pre->longitude,$data['node']->latitude,$data['node']->longitude);
        }

// 	    bug($data['node']);die;

	    $data['vstr'] = $vstr;
	    echo json_encode($data);die;
	}

	function edit($vstr=null,$id=0){
	    $vid = ABS(mortorID($vstr,true));
	    $data['node'] = $this->db->where('id',$id)->get("motor$vid")->row();
	    $data['node_pre'] = $this->db->where('id <',$id)->order_by('id DESC')->limit(2)->get("motor$vid")->result();
	    $data['node_next'] = $this->db->where('id >',$id)->order_by('id ASC')->limit(2)->get("motor$vid")->result();
	    $data['vstr'] = $vstr;

	    $timestamp = strtotime($data['node']->TIMESERVER);
	    if( $this->input->get('h') ){
	        $data['node']->h = $this->input->get('h');
	    } else {
	        $data['node']->h = date('H',$timestamp);
	    }

        $data['node']->i = date('i',$timestamp);


	    $data['node']->s = date('s',$timestamp);
	    $data['node']->date = date('Y-m-d',$timestamp);

	    add_js('map/draw.js');
	    $data['left_block'] = 'blocks/Node_Edit';
	    $this->template
	    ->build('pages/Draw-Line',$data);
	}

	function update(){
	    $id = $this->input->post('id');
	    $vstr = $this->input->post('vstr');
	    $vid = ABS(mortorID($vstr,true));
// 	    $node_next = $this->db->where('id >',$id)->order_by('id ASC')->get("motor$vid")->row()->id;





	    $datetime = $this->input->post('date')." ".$this->input->post('h').":".$this->input->post('i').":".$this->input->post('s');
        $update = array(
            'latitude'=>$this->input->post('latitude'),
            'longitude'=>$this->input->post('longitude'),
            'TIMESERVER'=>$datetime
        );
        $this->db->where('id',$id)->update("motor$vid",$update);

        $node_next = $this->db->where('id >',$id)->order_by('id ASC')->get("motor$vid")->row();
        return $this->node($vstr,$node_next->id);
        //redirect("draw/edit/$vstr/".$node_next->id."?h=".$this->input->post('h'));
	}

	function delete($vstr=null,$id=0){
	    $vid = ABS(mortorID($vstr,true));
	     $node_next = $this->db->where('id >',$id)->order_by('id ASC')->get("motor$vid")->row();
	     $this->db->where('id',$id)->delete("motor$vid");
	     redirect("draw/edit/$vstr/".$node_next->id);
	}
}