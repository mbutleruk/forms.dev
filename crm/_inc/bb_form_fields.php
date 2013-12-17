<?php
//----------------------------------------------------------------------------------------------------------------

	class bb_form_field {

//----------------------------------------------------------------------------------------------------------------

		public $name;
		public $type;
		public $admin;
		public $usermode;
		public $label;
		public $hint;
		public $default;
		public $options;
		public $value;

//----------------------------------------------------------------------------------------------------------------

		public function bb_form_field($name_arg='field', $type_arg='input', $usermode_arg='edit', $label_arg='', $hint_arg='', $default_arg='', $options_arg=array(), $value_arg='') {

			$this->admin	= false;
			$this->mode 	= "edit";
			$this->name    	= $name_arg;
			$this->type    	= $type_arg;
			$this->usermode	= $usermode_arg;
			$this->label   	= $label_arg;
			$this->hint   	= $hint_arg;
			$this->default 	= $default_arg;
			$this->options 	= $options_arg;
			$this->value   	= $value_arg;

		}

//----------------------------------------------------------------------------------------------------------------

		public function get_html() {

			$html = '';

			if($this->value=='') {
				$value = $this->default;
			}
			else {
				$value = $this->value;
			}

			if(!$this->admin) {
				if($this->mode=="edit" && $this->usermode=="view") $this->mode = "view";
				if($this->mode=="edit" && $this->usermode=="none") $this->mode = "none";
				if($this->mode=="view" && $this->usermode=="none") $this->mode = "none";
			}

			if($this->type=="text" || $this->type=="subheading") {

				if($this->type=="subheading") {
					$html.= "<p class=\"active\">" . $this->value . "</p>";
				} else {
					$html.= "<p>" . $this->value . "</p>";
				}

			} else {

				$html.="<div class=\"question\">";
				if($this->mode=="edit" || $this->mode=="view") {
					$html.= "<div class=\"prompts\">";
					$html.="<label for=\"$this->name\">$this->label</label>";
					if($this->hint!="") $html.="<label class=\"hint\">$this->hint</label>";
					$html.= "</div>\n";
				}
				$html.="<div class=\"inputs\">";

				switch($this->type) {

					case 'input':
						if($this->mode=='edit') $html.= "<input class=\"rounded\" type=\"text\" name=\"$this->name\" value=\"$value\" />\n";
						if($this->mode=='view') $html.="<span class=\"uneditable-input rounded\">$value</span>\n";
						break;

					case 'textarea':
						if($this->mode=='edit') $html.= "<textarea rows=\"5\" name=\"$this->name\" >$value</textarea>\n";
						if($this->mode=='view') $html.="<span class=\"uneditable-input\" >" . nl2br($value) . "</span>\n";
						break;

					case 'radio':
						foreach($this->options as $option) {
							if($this->mode=='edit') {
								$html.= "<label class=\"radio\"><input type=\"radio\" name=\"$this->name\" value=\"$option\"";
								if($option==$value) $html.=" checked=\"checked\"";
								$html.= "/>$option</label>\n";
							}
							else {
								if($option==$value) $html.="<span class=\"uneditable-input\">$option</span>";
							}
						}
						break;

					case 'checkbox':
						if($this->mode=='edit') {
							foreach($this->options as $option) {
								$html.= "<label class=\"checkbox\"><input type=\"checkbox\" name=\"$this->name[]\" value=\"$option\"";
								if($this->value!='') if(in_array($option, explode('|', $value))) $html.=" checked=\"checked\"";
								$html.= "/>$option</label>\n";
							}
						}
						else {
							$write_seperator = false;
							foreach($this->options as $option) {
								if($this->value!='') if(in_array($option, explode('|', $value))) {
									if($write_seperator) $html.=",&nbsp;";
									$html.="<span class=\"uneditable-input\">".$option."</span>";
									$write_seperator = true;
								}
							}
						}
						break;

					case 'select':
						if($this->mode=='edit') $html.="<select name=\"$this->name\">\n";
						if($this->mode=='view') $html.="<span class=\"uneditable-input\">\n";
						foreach($this->options as $option) {
							if($this->mode=='edit') {
								$html.= "<option value=\"$option\"";
								if($option==$value) $html.=" selected=\"selected\" ";
								$html.= ">$option</option>\n";
							}
							else {
								if($option==$value) $html.=$option;
							}
						}
						if($this->mode=='edit') $html.="</select>\n";
						if($this->mode=='view') $html.="</span>\n";

						break;

				}

				$html.="</div>";
				$html.="<div class=\"cleafix\"></div>";
				$html.="</div>";

			}

			return $html;
		}

//----------------------------------------------------------------------------------------------------------------

		public function get_xml() {

			$xml = "\t\t<field ";

			if($this->name!='') 	$xml.= "name=\"$this->name\" ";
			if($this->type!='' && $this->type!='input') $xml.= "type=\"$this->type\" ";
			if($this->usermode!='' && $this->usermode!='edit') $xml.= "usermode=\"$this->usermode\" ";
			if($this->label!='') 	$xml.= "label=\"$this->label\" ";
			if($this->hint!='') 	$xml.= "hint=\"$this->hint\" ";
			if($this->default!='') 	$xml.= "default=\"$this->default\" ";
			if(sizeof($this->options)>1) {
				$options = "";
				foreach($this->options as $option) {
					if($options!="") $options.= "|";
					$options.= $option;
				}
				$xml.= "options=\"$options\" ";
			}
			$xml.= ">";
			if($this->value!='') $xml.= $this->value;
			$xml.= "</field>\n";

			return $xml;
		}

//----------------------------------------------------------------------------------------------------------------

		public function load_xml($obj) {

			if(isset($obj["name"]))    	$this->name  	= $obj["name"];
			if(isset($obj["type"]))    	$this->type  	= $obj["type"];
			if(isset($obj["usermode"]))	$this->usermode	= $obj["usermode"];
			if(isset($obj["label"]))   	$this->label 	= $obj["label"];
			if(isset($obj["hint"]))   	$this->hint 	= $obj["hint"];
			if(isset($obj["options"])) 	$this->options  = explode("|", $obj["options"]);
			if(isset($obj["default"])) 	$this->default  = $obj["default"];

			$this->value = $obj;

		}

//----------------------------------------------------------------------------------------------------------------

	}

//----------------------------------------------------------------------------------------------------------------
?>