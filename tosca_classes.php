<?php
class tosca_root {
	protected $_structure = array();	// internal structure for tosca entities
	protected function error_out($e) {
		echo $e."\n\n";
	}

	public function get(){ return $this->_structure;
	}
	public function description($ds = null) {
		try {
			if (isset($ds)) {
				if (!is_string($ds)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['description'] = $ds;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['description'];
	}
}
class tosca_definitions extends tosca_root {
	protected static  $_def = null;			// array for type defininitions
	
	public static function import_definitions() {
		$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_definition_1_0.yml";
		$tosca_def = yaml_parse_file($yaml);
		$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml";
		$tosca_nfv_def = yaml_parse_file($yaml);
		$property_types = array( 	'string' => null,
									'integer' => null,
									'float' => null,
									'boolean' => null,
									'timestamp' => null,
									'range' => null,
									'list' => null,
									'map' => null,
									'scalar-unit.size' => null,
									'scalar-unit.time' => null,);
		self::$_def = array_merge($property_types, $tosca_def, $tosca_nfv_def);
	}
	public static function definitions() {
		return self::$_def;
	}
}
class tosca_typified extends tosca_definitions {
	protected  $_type = null;			// string
	
	function __construct($type_name = null, $clear) {
		try {
			if(!isset($type_name)) {
				throw new Exception('Missing argument');
			}
			$this->set_type($type_name, $clear);
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function set_type($typename = null, $clear = null) {
		try {
			if (isset($typename) and isset($clear) and isset(self::$_def)) {
				if(!array_key_exists($typename, self::$_def)) {
					throw new Exception('Invalid typename '.$typename);
				} 
				$this->_type = $typename;
				if ($clear) $this->_structure['type'] = $typename;
			}
			else {
				throw new Exception('Invalid argument');
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function check_name($attr_name, $attr_value, $type_to_check = null) {
		// check for attribute type
		$check = false;
		$derived_from_type = null;
		if ($type_to_check == null) $type_to_check = $this->type();
		foreach(self::$_def as $ty_name => $ty_def) {
			if ($type_to_check == $ty_name) {
			//echo "type found ".$type_to_check." \n";
				if(array_key_exists('derived_from', $ty_def)) $derived_from_type = $ty_def['derived_from'];
				if(array_key_exists($attr_value, $ty_def)) {
					if ($attr_value != 'requirements') {
						foreach($ty_def[$attr_value] as $at_name => $at_def) {
							//echo "Confronto ".$attr_name." e ".$at_name."\n";
							if( $attr_name == $at_name ) {
								$check = true;
								//echo "Found! ".$attr_name." Break internal loop\n";
								break;
							}
						}
					}
					else {
						foreach($ty_def[$attr_value] as $req) {
							if (array_key_exists($attr_name,$req)) {
								$check = true;
								//echo "Found! ".$attr_name." Break internal loop\n";
								break;
							}
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
				$check = $this->check_name($attr_name, $attr_value, $derived_from_type);
			}
		}
		return $check;
	}
	
	public function type() {
		return $this->_type;
	}
	public function properties($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'properties')) {
							throw new Exception('Invalid property '.$attr_name);
						}
						$this->_structure['properties'][$attr_name] = $attr_value;
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['properties'];
	}
	public function attributes($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'attributes')) {
							throw new Exception('Invalid attribute '.$attr_name);
						}
						$this->_structure['attributes'][$attr_name] = $attr_value;
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['attributes'];
	}
	public function interfaces($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'interfaces')) {
							throw new Exception('Invalid attribute '.$attr_name);
						}
						$this->_structure['interfaces'][$attr_name] = $attr_value;
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['interfaces'];
	}
	
}
class tosca_node_template  extends  tosca_typified {
	function __construct($type_name = null) {
		parent::__construct($type_name, true);
	}

	public function capabilities($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'capabilities')) {
							throw new Exception('Invalid capability '.$attr_name);
						}
						$this->_structure['capabilities'][$attr_name] = $attr_value;
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['capabilities'];
	}
	public function artifacts($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					$this->_structure['artifacts'][$attr_name] = $attr_value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['artifacts'];
	}
	public function requirements($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					try {
						if (!$this->check_name($attr_name, 'requirements')) {
							throw new Exception('Invalid capability '.$attr_name);
						}
						$this->_structure['requirements'][][$attr_name] = $attr_value;
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['requirements'];
	}
	public function node_filter($nf = null) {
		try {
			if (isset($nf)) {
				if(!is_array($nf)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['node_filter'] = $nf;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['node_filter'];
	}
}
class tosca_interface  extends  tosca_typified {
	function __construct($type_name = null) {
		parent::__construct($type_name, false);
	}

	public function operations($op = null) {
		try {
			if (isset($op)) {
				if (!is_array($op)) {
					throw new Exception('Invalid argument');
				}
				foreach($op as $name => $value) {
					$this->_structure[$name] = $value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return array_diff_key($this->_structure, array('inputs' => 'hjgfj'));
	}
	public function inputs($in = null) {
		try {
			if(isset($in)) {
				if (!is_array($in)) {
					throw new Exception('Invalid argument');
				}
				foreach($in as $name => $value) {
					$this->_structure['inputs'][$name] = $value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['inputs'];
	}
}
class tosca_capability  extends  tosca_typified {
	function __construct($type_name = null) {
		parent::__construct($type_name, false);
	}
}
class tosca_node_filter extends  tosca_root {
	public function capabilities($cp = null) {
		try {
			if(isset($cp)) {
				if (!is_array($cp)) {
					throw new Exception('Invalid argument');
				}
				foreach($cp as $capability) {
					$this->_structure['capabilities'][] = $capability;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['capabilities'];
	}
	public function properties($pr = null, $cp = null) {
		try {
			if(isset($pr)) {
				if (!is_array($pr)) {
					throw new Exception('Invalid argument');
				}
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
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['properties'];
	}
}
class tosca_requirement extends tosca_root {
	public function keys($keys = null) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) {
					throw new Exception('Invalid argument');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'node' or $key_name == 'relationship' or 
							 $key_name == 'capability' or $key_name == 'node_filter') {
							$this->_structure[$key_name] = $key_value;
						}
						else {
							throw new Exception('Invalid key name '.$key_name);
						}
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}
}
class tosca_artifact extends tosca_typified {
	function __construct($type_name = null) {
		parent::__construct($type_name, true);
	}
	
	public function keys($keys = null) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) {
					throw new Exception('Invalid argument');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'file' or $key_name == 'repository' or 
							 $key_name == 'deploy_path' ) {
							$this->_structure[$key_name] = $key_value;
						}
						else {
							throw new Exception('Invalid key name '.$key_name);
						}
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}
}
class tosca_topology_template extends tosca_root{
	private function check_group($gr_def) {
		//print_r($this->node_templates());
		$check = true;
		foreach ($gr_def['targets'] as $gr_member) {
			if (!array_key_exists($gr_member, $this->node_templates())) {
				$check = false;
				break;
			}
		}
		return $check;
	}
	
	public function node_templates($nt = null) {
		try {
			if (isset($nt)) {
				if (!is_array($nt)) {
					throw new Exception('Invalid argument');
				}
				foreach($nt as $name => $node) {
					$this->_structure['node_templates'][$name] = $node;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['node_templates'];
	}
	public function inputs($par = null) {
		try {
			if (isset($par)) {
				if (!is_array($par)) {
					throw new Exception('Invalid argument');
				}
				foreach($par as $name => $def) {
					$this->_structure['inputs'][$name] = $def;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['inputs'];
	}
	public function outputs($par = null) {
		try {
			if (isset($par)) {
				if (!is_array($par)) {
					throw new Exception('Invalid argument');
				}
				foreach($par as $name => $def) {
					$this->_structure['outputs'][$name] = $def;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['outputs'];
	}
	public function groups($gr = null) {
		try {
			if (isset($gr)) {
				if (!is_array($gr)) {
					throw new Exception('Invalid argument');
				}
				foreach($gr as $name => $def) {
					try {
						if (!$this->check_group($def)) {
							throw new Exception('Invalid group '.$name);
						}
						$this->_structure['groups'][$name] = $def;
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['groups'];
	}
}
class tosca_parameter extends tosca_typified {
	function __construct($type_name = null) {
		parent::__construct($type_name, true);
	}

	public function keys($keys = null) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) {
					throw new Exception('Invalid argument');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'value' or $key_name == 'required' or
							 $key_name == 'default' or $key_name == 'status' or $key_name == 'entry_schema' ) {
							$this->_structure[$key_name] = $key_value;
						}
						else if ($key_name == 'constraints') {
							$this->_structure[$key_name][] = $key_value;
						}
						else {
							throw new Exception('Invalid key name '.$key_name);
						}
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure;
	}

}
class tosca_group extends tosca_typified {
	function __construct($type_name = null) {
		parent::__construct($type_name, true);
	}
	public function targets($tg = null) {
		try {
			if (isset($tg)) {
				if (!is_array($tg)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['targets'] = $tg;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['targets'];
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
	public static function occurrences($value) {
		$_structure = array('occurrences' => $value);
		return $_structure;
	}
}

/*  

tosca_definitions::import_definitions();
//print_r(tosca_definitions::definitions());

	$nt = new tosca_node_template('tosca.nodes.Database');
	$nt->properties(array('user' => 'deVito', 'password' => 'jsdghasjh', 'port' => '8080'));
	$nt->attributes(array('tosca_id' => 'ID.123.456.789'));
	
	$ar = new tosca_artifact('tosca.artifacts.File');
	$ar->keys(array('file' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/class_template_generated.yml", 'repository' => "MY_REPOSITORY_NAME"));
	$nt->artifacts(array('my_yaml_descriptor' => $ar->get(), 'my_env_file' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/env_file.sh"));

	$if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');
	$if->inputs(array('my_input' => operator::get_input('input_value')));
	$if->operations(array('configure' => 'my_configuration_script.sh','start' => 'hjdgsjhasgd.sh'));
	$nt->interfaces(array('Standard' => $if->get()));

	$nf = new tosca_node_filter();
	$nf->capabilities(array('tosca.capabilities.Container'));
	$nf->properties(array('architecture' => operator::in_range(1,4),'distribution' => operator::equal('ubuntu')), 'os');
	
	$rq = new tosca_requirement();
	$rq->keys(array('node' => 'tosca.nodes.WebServer', 'node_filter' => $nf->get()));
	$nt->requirements(array('host'=>$rq));
	//print_r($nf->get());
	$nt->node_filter($nf->get());

	$cp = new tosca_capability('tosca.capabilities.Endpoint.Database');
	$cp->properties(array('protocol' => 'tcp-ip', 'url_path' => 'www.my_url.it'));
	$cp->properties(array('port' => '8080', 'network_name' => 'my network'));
	$nt->capabilities(array('database_endpoint' => $cp->get()));

//print_r($nt);
	
	$tt = new tosca_topology_template();
	
	$ip1 = new tosca_parameter('integer');
	$ip1->description('Number of CPUs for the server');
	$ip1->keys(array('default' => 4, 'constraints' => operator::in_range(2,8)));
	$ip2 = new tosca_parameter('scalar-unit.size');
	$ip2->keys(array('default' => '100 GB'));
	$tt->inputs(array('cpus' => $ip1->get(), 'storage_size' => $ip2->get()));
	
	$tt->node_templates(array('VD1' => $nt->get(),'VD2' => $nt->get()));
	
	$gr1 = new tosca_group('tosca.groups.nfv.vnffg');
	$gr1->description('forwarding graph 1');
	$gr1->properties(array('vendor' => 'Pinco pallino SPA', 'number_of_endpoints' => 2, 'dependent_virtual_link' => array('VL1','VL2','VL4')));
	$gr1->targets(array('VD1','VD3','VD1'));

	$gr2 = new tosca_group('tosca.groups.nfv.vnffg');
	$gr2->description('forwarding graph 1');
	$gr2->properties(array('vendor' => 'Pinco pallino SPA', 'number_of_endpoints' => 2, 'dependent_virtual_link' => array('VL1','VL2','VL4')));
	$gr2->targets(array('VD1','VD2','VD1'));

	$tt->groups(array('VNFFG1' => $gr1->get(), 'VNFFG2' => $gr2->get()));
	


$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/class_template_generated.yml";
if( yaml_emit_file($yaml, $tt->get()) ) echo "TUTTO OK";
*/

?>