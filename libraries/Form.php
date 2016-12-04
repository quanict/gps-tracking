<?php
class form {
	var $CI , $fields;
	static $protection=''; //mapgps
	static $randomProtect = 2;
	var $inputClass='field';
	///var $inputAttribute;
	public function __construct(){
		$this->CI =& get_instance();
	}
	public function notification($msg){
		$html='';
		if($msg && is_array($msg)){
			foreach($msg AS $note){
				if($note['type']=='error')
					$html.='<div class="ui-widget ui-msg"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>'.$this->CI->lang->line('Error').':</strong> '.$note['content'].'</p></div></div>';
				else if($note['type']=='message')
					$html.='<div class="ui-widget ui-msg"><div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-notice" style="float: left; margin-right: .3em;"></span><strong>'.$this->CI->lang->line('Message').':</strong> '.$note['content'].'</p></div></div>';
			}
		}
		return $html;
	}
	public function protection($key=null){
// 		$key = self::$protection.bin2hex(($key!=null)?$key:null);
		$key = self::$protection.(($key!=null)?$key:null);
		return ($key);
	}

	public function  genRandomString($length = 5){
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass = '';
		$stat = @stat(__FILE__);
		if (empty($stat) || !is_array($stat)) $stat = array(php_uname());
		mt_srand(crc32(microtime() . implode('|', $stat)));
		for ($i = 0; $i < $length; $i ++) {
			$makepass .= $salt[mt_rand(0, $len -1)];
		}
		return $makepass;
	}

	public function submitForm($opt=array()){

		foreach($this->CI->form->formField AS $key=>$field){
			if( isset($field->request) && $field->request && $this->CI->input->post($key)==''){
				$this->CI->msg[] =array('type'=>'error','text'=>$this->CI->lang->line('Request input '.$key) );
			} else
				$this->CI->form->formField->$key->value = $this->CI->input->post($key);
		}
		if(count($this->CI->msg) > 0){
			$this->CI->input->unset_post();
			self::viewForm($opt);
		} else {
			$dataValue = self::formValue($this->CI->form->formField);
			$action = $this->CI->$opt['model']->$opt['model-update']($dataValue);
			if(is_array($action) ){
				$this->CI->msg[] = $action;
				$this->CI->input->unset_post();
				self::form($opt);
			} else {
				redirect($opt['uri-back'], 'refresh');
			}

		}

	}
	public function viewForm($opt=array('item-name'=>'') ){
		if($this->CI->input->post())  {
			self::submitForm($opt);
			exit('call me');
		} else {
			if( isset($opt['id']) && $opt['id']!= null ){
				$title='Update '.$opt['item-name'];
				$item=$this->CI->$opt['model']->$opt['model-get']($opt['id']);
				foreach($this->CI->form->formField AS $key=>$val){
					if(isset($item->$key))
						$this->CI->form->formField->$key->value = $item->$key;
				}
			} else {
				$title=$opt['item-name'].' Add New '.$opt['item-name'];
			}

			if(count($this->CI->msg) > 0 && $this->CI->msg[0]['type'] == 'confirm-replace' ) {
				$this->CI->form->formField->confirm = (object)array('type'=>'hidden','value'=>1);
				$data['buttons'][] = array('title'=>'Replace','type'=>'submit');
				// 				bug($this->CI->backend->formField);exit;
			}
			$data['form']= $this->CI->form->formField;
			$data['form_title']=$opt['item-name'].' data';
			$this->CI->template->write_view('content',(isset($opt['view']))?$opt['view']:'pages/form-edit',$data);
			$this->CI->template->write('title',$title);
		}
	}

	public function build($fields=null,$button=''){

		$html='<form action="" method="post" >';
		if($fields){
			if(is_object($fields)){
				$this->CI->form->fields = $fields;
			} else if (is_array($fields)){
				$this->CI->form->fields = self::bindFields($fields);
			}
		}
		if($this->CI->form->fields){
			foreach($this->CI->form->fields AS $key=>$input){
				if($input->type=='hidden'){
					$html .= self::inputHidden($key,$input->value);
				} else {
					$html .= self::rowInput($key,$input);
				}

			}
		}
		if(is_array($button)){
			$html .='<div class="button_bar clearfix">';
			foreach($button AS $b){
				$attributes = ( isset($b['attributes']) )?$b['attributes']:null;
				$html .=self::button($b['title'],$b['type'],$attributes);
			}
			$html .='</div>';
		} else if ($button !='' ){
			$html .='<div class="button_bar clearfix">'.self::button($button,'submit').self::button('Cancel','button',' class="cancel ui-button" ').'</div>';
		}
		$html .=self::inputToken();
		$html .='</form>';
		$script = ' $("button.cancel").click(function(e){ window.history.back(-1); e.preventDefault();})';
		$this->CI->template->add_js_ready($script);
		return $html;

	}

	public function inputHidden($fieldKey,$fieldData){
		$name = ($fieldKey==$this->CI->security->get_csrf_token_name())?$fieldKey:self::protection($fieldKey);
		return $input = "<input name='$name' type='hidden' id='$fieldKey' value='$fieldData' >";
	}

	public function attributes($attributes=''){
		if (is_array($attributes)){
			$atts = '';
			foreach ($attributes as $key => $val) {
				$atts .= ' ' . $key . '="' . $val . '"';
			}
			$attributes = $atts;
		} elseif (is_string($attributes) AND strlen($attributes) > 0) {
			$attributes = ' '. $attributes;
		}
		return $attributes;
	}

	public function rowInput($key,$fieldData){
		//if($inputOnly === TRUE) return
		$styleRow='';
		$desc = '';
		$html = '<fieldset class="'.$styleRow.'" ><label>'.$fieldData->title.$desc.'</label><div class="clearfix">';

		$method = 'input'. ucfirst( strtolower($fieldData->type) );

		$html.=self::input($method,$key,$fieldData);

		if( isset($fieldData->required) && $fieldData->required ){
			$html.='<div class="required_tag tooltip hover left"></div>';
		}

		$html.='</div></fieldset>';
		return $html;
	}

	public function input($method,$key,$fieldData){

	    if( isset($fieldData->type) && ($fieldData->type == 'password') ){
			$html = self::inputText($key,$fieldData);
		} else if (method_exists($this,$method)){
			$html = self::$method($key,$fieldData);

		} else if (file_exists( APPPATH.'libraries'.DS.'form-field'.DS.$method.'.php' )){
			if (!class_exists($method)) {
				include_once ( APPPATH.'libraries'.DS.'form-field'.DS.$method.'.php' );
			}
			$methodName = $fieldData->type;
			$className = new $method;
			$html = $className->input($key,$fieldData);
		} else {
			$html = self::inputText($key,$fieldData);
		}
		return $html;
	}

	public function inputText($fieldKey,$fieldData){
		$readonly = (isset($fieldData->disabled))?$fieldData->disabled:false;
		$required = (isset($fieldData->request))?$fieldData->request:false;
		if(isset($fieldData->type) && $fieldData->type=='password'){
			$typeInput='password';
			$fieldData->value = null;
		} else {
			$typeInput='text';
		}
		$lable = preg_replace("/<.*?>/", "", $fieldData->lable);
		$attribute = '';
// 		echo 'a'.$this->CI->form->inputClass.'b';
		if($readonly){
			return '<span>'.$fieldData->value.'</span>';
		}else
			return "<input type=\"$typeInput\" name=\"".self::protection($fieldKey)."\" aria-label=\"".$lable."\"  placeholder=\"".$lable."\" value=\"$fieldData->value\" class=\"field ".$this->CI->form->inputClass."  \" $attribute id=\"".self::protection($fieldKey)."\" />";
	}

	public function inputToken(){
		return self::inputHidden($this->CI->security->get_csrf_token_name(),$this->CI->security->get_csrf_hash());
	}

	public function button($name,$type="button",$attributes=''){
		$attributes = self::attributes($attributes);
		switch($type){
			case 'submit':
				$html =	 '<input type="submit" value="'.lang($name).'">';break;
			case 'button':
			default:
				$html =	 '<button '.$attributes.' >'.lang($name).'</button>';break;
		}

		return $html;
	}


	public function bindFields($fields){
		$data = array();
// 		bug($fields); exit('bind form');
		foreach($fields AS $key=>$val){
// 			if($key=='id'){
// 				$input = array(
// 					'type'=>'hidden',
// 					'value'=>(!is_array($val))?$val:0
// 				);
// 			} else
			if(is_array($val)){
				$input = $val;
				$input['type'] = (isset($val['type']))?$val['type']:'text';
				$input['value'] = (isset($val['value']))?$val['value']:null;
				$input['title'] = (isset($val['title']))?$val['title']:ucfirst($key);
				$input['lable'] = (isset($val['lable']))?$val['lable']:null;
				$input['desc'] = (isset($val['desc']))?$val['desc']:null;
				$input['wysiwyg'] = (isset($val['wysiwyg']))?$val['wysiwyg']:false;
				$input['request'] = (isset($val['request']))?$val['request']:false;
				$input['disable'] = (isset($val['disable']))?$val['disable']:false;

				if(isset($val['dir'])) $input['dir'] = $val['dir'];


			} else {
				$input=array(
					'type'=>'text',
					'value'=>$val,
					'desc'=>'',
					'title'=>(isset($val['title']))?$val['title']:ucfirst($key),
					'lable'=>(isset($val['lable']))?$val['lable']:ucfirst($key)
				);
			}
// 			bug($input);
// 			bug('title='.$this->CI->lang->line($input['title']));
			if(isset($input['title']) && isset($this->CI->lang) && $this->CI->lang->line($input['title']) !=''){
				$input['title'] = $this->CI->lang->line($input['title']);
			}
// 			bug($input);
			$data[$key] = (object)$input;
		}
		return $data;
	}

	public function formValue($fields){
		$data = array();
		foreach($fields AS $key=>$val){
			if($val->value != '')
				$data[$key] = $val->value;
		}
		return $data;
	}

	public function hidden($fieldKey,$fieldData){
		$CI =& get_instance();
		$name = ($fieldKey==$CI->security->get_csrf_token_name())?$fieldKey:self::$protection.$fieldKey;
		return $input = "<input name='$name' type='hidden' id='$fieldKey' value='$fieldData' >";
	}

	public function checkInputCapcha(){
		$sql = "SELECT COUNT(*) AS count FROM captcha WHERE word = ? AND ip_address = ? AND captcha_time > ?";
		$this->CI->db->select('COUNT(*) AS count')->from('captcha');
		$this->CI->db->where('word',$this->CI->input->post('capcha'));
		$this->CI->db->where('ip_address',$this->CI->input->ip_address());
		$this->CI->db->where('captcha_time > ', time()-7200);
		$row = $this->CI->db->get()->row();

		if ($row->count > 0){
			return true;
		}
		return false;
	}

}