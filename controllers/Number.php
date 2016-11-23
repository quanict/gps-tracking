<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Number extends MX_Controller {
	function __construct(){

	    parent::__construct();
	    $this->load->module('layouts');
	    $this->template->set_theme('bootstrap')->set_layout('bootstrap');

	    $this->load->library('crawler/simple_html_dom');

	    $this->db = $this->load->database('nodedemo',true);
	}

	function index(){
	    $numbers = array();
	    if( $this->input->post() ){
	        $number_str = $this->input->post('number');

	        if( !empty($number_str) AND count($number_str) > 0 ) foreach ($number_str AS $num_str){
//                 $number_check = explode('.', $num_str);
                $number_check = preg_split( '/(\s|&|,)/', $num_str );
                if( !empty($number_check) AND count($number_check) > 0 ) foreach ($number_check AS $num){
                    if( strlen($num) < 1) continue;
                    if( !array_key_exists($num, $numbers) ){
                        $numbers[$num] = 1;
                    } else {
                        $numbers[$num] ++;
                    }

                }
	        }
	        arsort($numbers);
	    }

	    $this->template->build('numbercount',array('numbers'=>$numbers));
	}
}