<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class History extends MX_Controller {
    function __construct(){
        parent::__construct();
        $this->mapgps->checkLogin();
        $this->vstr = $this->uri->segment(2);
        $this->vid = mortorID($this->vstr,true);
        $this->load->model("History_Model");
    }

    function item(){
        $this->template->set_theme('viettracker')->set_layout('vietracker');
        add_js('infobubble.js');
        add_js('playback.js');
        $headScript =''
        .'vmap.ini();'

        .'playback.create();'
            ;
        add_js_ready($headScript);
        $data['header_ctr'] = 'blocks/history';
        $this->template->build('pages/history',$data);

    }

    function data(){
        if($this->uri->extension != 'json'){
            return NULL;
        }

        $data = array('nodes'=>'');

        $motor = $this->Vehicle_Model->getVehicle($this->vid);
        $data['name'] = $motor->name;
        $begin = $this->input->get('time');
        $end = $this->input->get('end');

        if($begin && $end){
            $nodes = $this->History_Model->loadNodeByTime($this->vid, $begin, $end );
        } else if($begin) {
            $nodes = $this->History_Model->loadNodeByTime( $this->vid ,$begin );
        }

        if(isset($nodes) && $nodes){
            $end = ($end)?$end:null;
            $stop = $this->Vehicle_Model->getStopNodeByTime($this->vid,$begin,$end,FALSE);
            foreach($nodes AS $index=>$node){
                $nodeStop = reset($stop); // First Element's Value
                $nodeStop_key = key($stop); // First Element's Key
                if($nodeStop && $node->id > $nodeStop->id){
                    $data['nodes'][] = array(
                        //'id'=>$nodeStop->id,
                        'la'=>$this->mapgps->shortDegrees($nodeStop->la),
                        'lo'=>$this->mapgps->shortDegrees($nodeStop->lo),
                        't'=>strtotime($nodeStop->t),
                        'speed'=>0,
                        'moved'=>''
                    );
                    unset( $stop[$nodeStop_key] );
                }
                if( isset($nodes[$index-1]) ){
                    $moved = (isset($nodes[$index-1]))?$this->mapgps->distance($nodes[$index-1]->la,$nodes[$index-1]->lo,$node->la,$node->lo):0;
                    if($moved=='NAN'){
                        $moved = 0;
                    }
                } else {
                    $moved = 0;
                }
                $moved = number_format($moved,4,'.','');
                $data['nodes'][] = array(
                    'la'=>number_format($node->la,6,'.',''),
                    'lo'=>number_format($node->lo,6,'.',''),
                    't'=>strtotime($node->t),
                    'speed'=>$node->speed,
                    'moved'=>$moved
                );
            }
//             bug($data['nodes']);die;

        }

            if( !$data['nodes'] ){
                $data['nodes'][] = $this->Vehicle_Model->getLastNode($this->vid,array('latitude <'=> 90,'latitude >'=>-90,'longitude <'=> 180,'longitude >'=> -180));
            }
//             bug($data['nodes']);die;
            $data['timestamp'] = strtotime('now')*1000;
            return jsonData($data);

    }
}