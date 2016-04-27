<?php
class tosca_root {
	protected $_structure = array();	// internal structure for tosca entities
	protected function error_out($e) {
		echo $e."\n\n";
	}
	protected function sequenced_list(&$seq_list, $attr_name, $attr_value) {
		if (!isset($seq_list)) {
			//echo "lista non esistente  \n";
			$seq_list[][$attr_name] = $attr_value;
		}
		else { 
			//echo "lista esistente  \n";
			$found = false;
			foreach ($seq_list as $pos => $req) {
				if (array_key_exists($attr_name, $req)) {
					$seq_list[$pos][$attr_name] = $attr_value;
					$found = true;
					//echo "Trovato!  pos: ".$pos."\n";
					break;
				}
			}
			if (!$found) $seq_list[][$attr_name] = $attr_value; 
		}
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

	public static function import_definitions($file) {
		if (!isset(self::$_def)) {
		self::$_def = array( 	'string' => null,
								'integer' => null,
								'float' => null,
								'boolean' => null,
								'timestamp' => null,
								'range' => null,
								'list' => null,
								'map' => null,
								'scalar-unit.size' => null,
								'scalar-unit.time' => null,);
		}
		$parsed = yaml_parse_file($file);
		self::$_def = array_merge(self::$_def, $parsed);
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
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type': 
						$this->set_type($value, true);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'requirements':
						foreach ($value as $req) {
							$this->requirements($req);
						}
						break;
					case 'artifacts':
						$this->artifacts($value);
						break;
					case 'interfaces':
						$this->interfaces($value);
						break;
					case 'properties':
						$this->properties($value);
						break;
					case 'attributes':
						$this->attributes($value);
						break;
					case 'capabilities':
						$this->capabilities($value);
						break;
					case 'interfaces':
						$this->interfaces($value);
						break;
					case 'node_filter':
						$this->node_filter($value);
						break;
					case 'directives':
					case 'copy':
						break;
				}
			}
		}
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
							throw new Exception('Invalid requirement '.$attr_name);
						}
						$this->sequenced_list($this->_structure['requirements'], $attr_name, $attr_value);
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
	function __construct($type_name = null, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, false);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type':
						$this->set_type($value, false);
						break;
					case 'inputs':
						$this->inputs($value);
						break;
				}
			}
		}
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
	function __construct($type_name = null, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type':
						$this->set_type($value, true);
						break;
					case 'properties':
						$this->properties($value);
						break;
					case 'attributes':
						$this->attributes($value);
						break;
				}
			}
		}
	}
}
class tosca_node_filter extends  tosca_root {
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'properties':
						$this->properties($value);
						break;
					case 'capabilities':
						$this->capabilities($value);
						break;
				}
			}
		}
	}
	
	
	public function capabilities($cp = null) {
		try {
			if(isset($cp)) {
				if (!is_array($cp)) {
					throw new Exception('Invalid argument');
				}
				foreach($cp as $capability) {
					$this->sequenced_list($this->_structure['capabilities'], $capability, null);
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
						$this->sequenced_list($this->_structure['properties'], $name, $property);
					}
				}
				else {
					$found = false;
					if (isset($this->_structure['capabilities'])) {
					foreach($this->_structure['capabilities'] as $pos => $capability) {
						if ( array_key_exists($cp, $capability)) {
							$found = true;
							foreach($pr as $name => $property) {
								$this->sequenced_list($this->_structure['capabilities'][$pos][$cp], $name, $property);
							}
						}
					}
					}
					if (!$found) {
						$this->_structure['capabilities'][][$cp] = null;
						end($this->_structure['capabilities']);
						$pos = key($this->_structure['capabilities']);
						foreach($pr as $name => $property) {
							$this->sequenced_list($this->_structure['capabilities'][$pos][$cp], $name, $property);
						}
					}
					return;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['properties'];
	}
}
class tosca_requirement extends tosca_root {
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			$this->keys($struct);
		}
		else if (is_string($struct)) {
			$this->keys(array('node' => $struct));
		}
	}

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
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				if ($key == 'type') $this->set_type($value, true);
				else $this->keys(array($key => $value));
			}
		}
		else if (is_string($struct)) {
			$this->set_type('tosca.artifacts.File', true);
			$this->keys(array('file' => $struct));
		}
		else { // error
		}
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
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
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
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description':
						$this->description($value);
						break;
					case 'inputs':
						$this->inputs($value);
						break;
					case 'node_templates':
						$this->node_templates($value);
						break;
					case 'relationship_templates':
						//$this->relationship_templates($value);
						break;
					case 'groups':
						$this->groups($value);
						break;
					case 'policies':
						//$this->policies($value);
						break;
					case 'outputs':
						$this->outputs($value);
						break;
					case 'substitution_mappings':
						$this->substitution_mappings($value);
						break;
				}
			}
		}
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
	public function substitution_mappings($sm = null) {
		try {
			if (isset($sm)) {
				if(!is_array($sm)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['substitution_mappings'] = $sm;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['substitution_mappings'];
	}
}
class tosca_parameter extends tosca_typified {
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type':
						$this->set_type($value, true);
						break;
					case 'value':
					case 'required':
					case 'default':
					case 'status':
					case 'entry_schema':
						$this->keys($value);
						break;
					case 'constraints':
						foreach ($value as $constr) {
							$this->keys(array('constraints' => $constr));
						}
						break;
				}
			}
		}
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
							//$this->_structure[$key_name][] = $key_value;
							$this->sequenced_list($this->_structure['constraints'], $key_value, null );
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
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, true);
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'type': 
						$this->set_type($value, true);
						break;
					case 'description':
						$this->description($value);
						break;
					case 'interfaces':
						$this->interfaces($value);
						break;
					case 'properties':
						$this->properties($value);
						break;
					case 'targets':
						$this->targets($value);
						break;
				}
			}
		}
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
class tosca_substitution_mapping extends tosca_typified {
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name, false);
			$this->_structure['node_type'] = $type_name;
		}
		else {
			$this->set($struct);
		}
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'node_type': 
						$this->set_type($value, false);
						$this->_structure['node_type'] = $value;
						break;
					case 'description':
						$this->description($value);
						break;
					case 'capabilities':
						$this->capabilities($value);
						break;
					case 'requirements':
						$this->requirements($value);
						break;
				}
			}
		}
	}
	
	public function capabilities($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					$this->_structure['capabilities'][$attr_name] = $attr_value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['capabilities'];
	}
	public function requirements($attr = null) {
		try {
			if (isset($attr) ) {
				if(!is_array($attr)) {
					throw new Exception('Invalid argument');
				}
				foreach($attr as $attr_name => $attr_value) {
					$this->_structure['requirements'][$attr_name] = $attr_value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['requirements'];
	}
}
class tosca_service_template extends tosca_root {
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description':
						$this->description($value);
						break;
					case 'tosca_definitions_version':
						$this->tosca_definitions_version($value);
						break;
					case 'metadata':
						$this->metadata($value);
						break;
					case 'topology_template':
						$this->topology_template($value);
						break;
					case 'dsl_defintions':
					case 'imports':
						break;
				}
			}
		}
	}

	public function tosca_definitions_version($profile) {
		try {
			if (isset($profile) ) {
				if (!is_string($profile)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['tosca_definitions_version'] = $profile;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['tosca_definitions_version'];
	}
	public function metadata($mds = null) {
		try {
			if(isset($mds)) {
				if(!is_array($mds)) {
					throw new Exception('Invalid argument');
				}
				foreach($mds as $key_name => $key_value) {
					$this->_structure['metadata'][$key_name] = $key_value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['metadata'];
	}
	public function topology_template($tt) {
		try {
			if (isset($tt)) {
				if(!is_array($tt)) {
					throw new Exception('Invalid argument');
				}
				$this->_structure['topology_template'] = $tt;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['topology_template'];
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
	public static function map_of($node, $value) {
		$_structure = array($node, $value);
		return $_structure;
	}
}

?>