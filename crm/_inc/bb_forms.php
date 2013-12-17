<?php
//----------------------------------------------------------------------------------------------------------------

	class bb_form {

//----------------------------------------------------------------------------------------------------------------

		public $mode;
		public $admin;
		public $legend;
		public $fieldsets;
		public $signature;
		public $signed;
		public $created;
		public $modified;
		public $modifiedby;

//----------------------------------------------------------------------------------------------------------------

		public function bb_form($legend_arg = '', $fieldsets_arg=array()) {

			$this->mode 	 = "view";
			$this->admin 	 = false;
			$this->legend    = $legend_arg;
			$this->fieldsets = $fieldsets_arg;
			$this->signature = "";
			$this->signed = "01/01/2000 00:00:00";
			$this->created = "01/01/2000 00:00:00";
			$this->modified = "01/01/2000 00:00:00";
			$this->modifiedby = "";

		}

//----------------------------------------------------------------------------------------------------------------

		public function get_html() {

			$html ="<form class=\"bb-form\" method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "\">\n";

			$html.="<input type=\"hidden\" name=\"form_path\" value=\"".$_SESSION['path']."\" />";
			$html.="<input type=\"hidden\" name=\"form_signature\" value=\"$this->signature\" />";
			$html.="<input type=\"hidden\" name=\"form_modified\" value=\"$this->modified\" />";

			if($this->legend!='') $html.= "<h2>$this->legend</h2>";

			foreach($this->fieldsets as $fieldset) {
				$fieldset->mode  = $this->mode;
				$fieldset->admin = $this->admin;
				$html.= $fieldset->get_html();
			}

			if($this->mode=="edit") {
				$html.="<input type=\"hidden\" name=\"form_submitted\" />";
			}

			$html.="</form>\n";

			return $html;
		}

//----------------------------------------------------------------------------------------------------------------

		public function get_xml() {

			$xml = "<form ";
			if($this->legend!='') $xml.= "legend=\"$this->legend\" ";
			if($this->signature!='') $xml.= "signature=\"$this->signature\" ";
			if($this->signed!='') $xml.= "signed=\"$this->signed\" ";
			if($this->created!='') $xml.= "created=\"$this->created\" ";
			if($this->modified!='') $xml.= "modified=\"$this->modified\" ";
			if($this->modifiedby!='') $xml.= "modifiedby=\"$this->modifiedby\" ";
			$xml.= ">\n";

			foreach($this->fieldsets as $fieldset) {
				$xml.= $fieldset->get_xml();
			}

			$xml.="</form>\n";

			return $xml;
		}

//----------------------------------------------------------------------------------------------------------------

		public function load_from_xml($obj) {

			if(isset($obj["legend"])) $this->legend = $obj["legend"];
			if(isset($obj["signature"])) $this->signature = $obj["signature"];
			if(isset($obj["signed"])) $this->signed = $obj["signed"];
			if(isset($obj["created"])) $this->created = $obj["created"];
			if(isset($obj["modified"])) $this->modified = $obj["modified"];
			if(isset($obj["modifiedby"])) $this->modifiedby = $obj["modifiedby"];
			foreach($obj->children() as $child)
			{
				$fieldset = new bb_form_fieldset();
				$fieldset->load_xml($child);
				$this->fieldsets[] = $fieldset;
			}

		}

//----------------------------------------------------------------------------------------------------------------

		public function load_from_form() {

			foreach($this->fieldsets as $fieldset) {
				foreach($fieldset->fields as $field) {
					$name = (string)$field->name;
					if($field->type=='checkbox') {
						if(isset($_POST[$name])) {
							$value = '';
							foreach($_POST[$name] as $option)
							{
								if($value!='') $value.='|';
								$value.=$option;
							}
							$field->value = $value;
						}
					}
					else {
						if(isset($_POST[$name])) {
							$field->value = $_POST[$name];
						}
					}
				}
			}

		}

//----------------------------------------------------------------------------------------------------------------

	}

//----------------------------------------------------------------------------------------------------------------
?>