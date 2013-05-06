<?php
//----------------------------------------------------------------------------------------------------------------

	class bb_form_fieldset {
	
//----------------------------------------------------------------------------------------------------------------

		public $usermode;
		public $legend;
		public $fields;
		
//----------------------------------------------------------------------------------------------------------------

		public function bb_form_fieldset($usermode_arg='none', $legend_arg = '', $fields_arg = array()) {
		
			$this->mode 	 = "view";
			$this->admin 	 = false;
			$this->usermode  = $usermode_arg;
			$this->legend    = $legend_arg;
			$this->fields    = $fields_arg;
		}
		
//----------------------------------------------------------------------------------------------------------------

		public function get_html() {
		
			$html = "";

			if(!$this->admin) {
				if($this->mode=="edit" && $this->usermode=="view") $this->mode = "view";
				if($this->mode=="edit" && $this->usermode=="none") $this->mode = "none";
				if($this->mode=="view" && $this->usermode=="none") $this->mode = "none";
			}		

			if($this->mode=="edit" || $this->mode=="view") {
				if($this->legend!='') $html.= "<legend>$this->legend</legend>";
				$html .= "<fieldset>\n";
	
				foreach($this->fields as $field) {
					$field->admin = $this->admin;
					$field->mode  = $this->mode;
					$html.= $field->get_html();
				}
				
				$html.= "</fieldset>\n";
			}

			return $html;

		}

//----------------------------------------------------------------------------------------------------------------

		public function get_xml() {
		
			$xml = "\t<fieldset ";
			if($this->legend!='') $xml.= "legend=\"$this->legend\" ";
			if($this->usermode!='') $xml.= "usermode=\"$this->usermode\" "; 
			$xml.= ">\n";
			
			foreach($this->fields as $field) {
				$xml.= $field->get_xml();
			}
			
			$xml.= "\t</fieldset>\n";
			
			return $xml;

		}

//----------------------------------------------------------------------------------------------------------------

		public function load_xml($obj) {
			if(isset($obj["legend"])) $this->legend = $obj["legend"];
			if(isset($obj["usermode"]))	$this->usermode	= $obj["usermode"];
			foreach($obj->children() as $child)
			{
				$field = new bb_form_field();
				$field->usermode = $this->usermode;
				$field->load_xml($child);
				$this->fields[] = $field;
			}
		}
		
//----------------------------------------------------------------------------------------------------------------

	}

//----------------------------------------------------------------------------------------------------------------
?>