<?php

class topology_template {
	private $_structure = array();
	public function get(){ return $this->_structure;
	}
	public function node_templates($nt = null) {
		if (is_array($nt)) {
			//if (!array_key_exists('node_templates',$this->_structure)) $this->_structure['node_templates'] = null;
			foreach($nt as $name => $node) {
				$this->_structure['node_templates'][$name] = $node;
			}
		}
		return $this->_structure['node_templates'];
	}
}

class node_template {
	private $_structure = array(
			'type' => null );		
	public function get(){ return $this->_structure;
	}
	public function type($typename = null, $def = null) {
		if (isset($typename) and isset($def)) {
			if(array_key_exists($typename, $def)) {
				$this->_structure['type'] = $typename;
			} else {
				return null;
			}
		}
		return $this->_structure['type'];
	}
	public function attributes($attr = null, $def = null) {
		if (is_array($attr) and isset($def)) {
			foreach($attr as $attr_name => $attr_value) {
				if ($this->check_name($attr_name, 'attributes', $def)) {
					$this->_structure['attributes'][$attr_name] = $attr_value;
				}
			}
		}
		return $this->_structure['attributes'];
	}
	public function properties($attr = null, $def = null) {
		if (is_array($attr) and isset($def)) {
			foreach($attr as $attr_name => $attr_value) {
				if ($this->check_name($attr_name, 'properties', $def)) {
					$this->_structure['properties'][$attr_name] = $attr_value;
				}
			}
		}
		return $this->_structure['properties'];
	}
	public function requirements($rq = null) {
		if (is_array($rq)) {
			foreach($rq as $name => $requirement) {
				$this->_structure['requirements'][][$name] = $requirement;
			}
		}
		return $this->_structure['requirements'];
	}
	public function capabilities($cp = null, $def = null) {
		if (is_array($cp) and isset($def)) {
			foreach($cp as $name => $value) {
				if ($this->check_name($name, 'capabilities', $def)) {
					$this->_structure['capabilities'][$name] = $value;
				}
			}
		}
		return $this->_structure['capabilities'];
	}
	public function interfaces($if = null, $def = null) {
		if (is_array($if) and isset($def)) {
			foreach($if as $name => $value) {
				if ($this->check_name($name, 'interfaces', $def)) {
					$this->_structure['interfaces'][$name] = $value;
				}
			}
		}
		return $this->_structure['interfaces'];
	}
	private function check_name($attr_name, $attr_value, $def, $type_to_check = null) {
		// check for attribute type
		$check = false;
		$derived_from_type = null;
		if ($type_to_check == null) $type_to_check = $this->type();
		foreach($def as $ty_name => $ty_def) {
			if ($type_to_check == $ty_name) {
			// "type found";
				if(array_key_exists('derived_from', $ty_def)) $derived_from_type = $ty_def['derived_from'];
				if(array_key_exists($attr_value, $ty_def)) {
					foreach($ty_def[$attr_value] as $at_name => $at_def) {
						if( $attr_name == $at_name ) {
							$check = true;
							//echo "Found! Break internal loop\n";
							break;
						}
					}
				}
			}
			if ($check) {
				//echo "Found! Break external loop\n";
				break;
			}
		}
		if (!$check) {
			if(isset($derived_from_type)) {
			// type is derived; check recursively for source type
			//echo "derived from ".$derived_from_type."\n";
				$check = $this->check_name($attr_name, $attr_value, $def, $derived_from_type);
			}
		}
		return $check;
	}
}

class requirement {
	public static function keys($keys = null) {
		$_structure = array();
		if(isset($keys) and is_array($keys)) {
			foreach($keys as $key_name => $key_value) {
				if ($key_name == 'node' or $key_name == 'relationship' or $key_name == 'capability' or $key_name == 'node_filter') {
					$_structure[$key_name] = $key_value;
				}
			}
		}
		return $_structure;
	}
}

class capability {
	public static function properties($prop) {
		$_structure = array();
		if (isset($prop) and is_array($prop)) {
			foreach($prop as $name => $value) {
				$_structure['properties'][$name] = $value;
			}
		}
		return $_structure;
	}
	public static function attributes($prop) {
		$_structure = array();
		if (isset($prop) and is_array($prop)) {
			foreach($prop as $name => $value) {
				$_structure['attributes'][$name] = $value;
			}
		}
		return $_structure;
	}
}

class tosca_interface {
	private $type = null;
	private $_structure = array();		
	function __construct($typename = null, $def = null) {
		if (isset($typename) and isset($def)) {
			if(array_key_exists($typename, $def)) {
				$this->type = $typename;
			}
		}
	}
	public function get(){ return $this->_structure;
	}
	public function type($typename = null, $def = null) {
		if (isset($typename) and isset($def)) {
			if(array_key_exists($typename, $def)) {
				$this->type = $typename;
			} else {
				return null;
			}
		}
		return $this->type;
	}
	public function operations($op = null, $def = null) {
		if (is_array($op)) {
			foreach($op as $name => $value) {
				$this->_structure[$name] = $value;
			}
		}
		$inputs = array('inputs' => 'hjgfj');
		return array_diff_key($this->_structure, $inputs);
	}
	public function inputs($in = null) {
		if (is_array($in)) {
			foreach($in as $name => $value) {
				$this->_structure['inputs'][$name] = $value;
			}
		}
		return $this->_structure['inputs'];
	}
}

class node_filter {
	private $_structure = array();
	public function get(){ return $this->_structure;
	}
	public function capabilities($cp = null) {
		if (is_array($cp)) {
			//if (!array_key_exists('capabilities',$this->_structure)) $this->_structure['capabilities'] = null;
			foreach($cp as $capability) {
				$this->_structure['capabilities'][] = $capability;
			}
		}
		return $this->_structure['capabilities'];
	}
	public function properties($pr = null, $cp = null) {
		if (is_array($pr)) {
			if(!isset($cp)) {
				foreach($pr as $name => $property) {
					$this->_structure['properties'][][$name] = $property;
				}
			}
			else {
				$this->_structure['capabilities'][][$cp] = null;
				end($this->_structure['capabilities']);
				$pos = key($this->_structure['capabilities']);
				foreach($pr as $name => $property) {
					$this->_structure['capabilities'][$pos][$cp]['properties'][][$name] = $property;
				}
			}
		}
		return $this->_structure['properties'];
	}
}

class operator {
	public static function in_range($lower, $upper) {
		$_structure = array();
		if(is_int($lower) and is_int($upper) and $upper >= $lower) {
			$_structure['in_range'][0] = $lower;
			$_structure['in_range'][1] = $upper;
		}
		return $_structure;
	}
	public static function equal($value) {
		$_structure = array('equal' => $value);
		return $_structure;
	}
	public static function get_input($name) {
		$_structure = array('get_input' => $name);
		return $_structure;
	}
}

// acquisisco le definizioni dei tipi


$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_definition_1_0.yml";
$tosca_def = yaml_parse_file($yaml);
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml";
$tosca_nfv_def = yaml_parse_file($yaml);

$definitions = array_merge($tosca_def, $tosca_nfv_def);
//print_r($definitions);

$tt = new topology_template();
$nt = new node_template();
if ($nt->type('tosca.nodes.Compute', $definitions) != null) {
	$nt->attributes(array('tosca_id' => 'ID.123.456.789', 'private_address' => '128.45.45.1'),$definitions);
	$nt->properties(array('user' => 'deVito'),$definitions);
	
	$nf = new node_filter();
	$nf->capabilities(array('tosca.capabilities.Container'));
	$nf->properties(array('architecture' => operator::in_range(1,4),'distribution' => operator::equal('ubuntu')), 'os');
	$rq = requirement::keys(array('node' => 'tosca.nodes.WebServer', 'node_filter' => $nf->get()));
		
	$nt->requirements(array('host'=>'nodejs'));
	$nt->requirements(array('database_connection' => $rq));
	
	$cp1 = capability::properties(array('disk_size' => '10 GB', 'mem_size' => '1 GB', 'num_cpus' => operator::get_input('cpus')));
	$cp2 = capability::properties(array('architecture' => 'x86_64', 'type' => 'linux', 'distribution' => 'fedora'));
	$nt->capabilities(array('host' => $cp1, 'os' => $cp2),$definitions);
	
	$if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard',$definitions);
	$if->operations(array('configure' => 'my_configuration_script.sh','start' => 'hjdgsjhasgd.sh'), $definitions);
	$if->inputs(array('my_input' => operator::get_input('input_value')));
	$nt->interfaces(array('Standard' => $if->get()), $definitions);
	print_r($if->operations());
	
	$tt->node_templates(array('VD1' => $nt->get(),'VD2' => $nt->get()));
	$tt->node_templates(array('VD3' => $nt->get()));

}
//print_r($nt->requirements());
print_r($nt);
print_r($tt);

$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/topology_template_generated.yml";
if( yaml_emit_file($yaml, $tt->get()) ) echo "TUTTO OK";

?>