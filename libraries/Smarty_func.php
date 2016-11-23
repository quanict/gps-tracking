<?php
class Smarty_func {
    function __construct(){
        if( !function_exists('alphabet_id') ){
            get_instance()->load->helper('string');
        }
    }

    static function mortorID($template=null){
        if( !isset($template['id']) )
            return NULL;

        $to_num = (isset($template['to_num'])) ? $template['to_num'] : FALSE;

        $int = $template['id'];

        $face = 20130524;
        if($to_num === TRUE ){
            return alphabet_id($int,TRUE) - $face;
        } else {
            return alphabet_id( $face + intval($int) );
        }
    }
}