<?php
class tosca_definitions {					// design pattern : singleton
	private $_normative_pathname = 'normative_types/';
	private $_normative_filename = 'TOSCA_definition_1_0.yml';
	private static  $_instance = null;
	private static  $_singleton_def = null; // array for type definitions organized in family types
	
	public  static function get_definitions() {
		if( self::$_instance === null) {
			self::$_instance = new tosca_definitions;
			self::$_singleton_def = [ 
				'primitive_types' => [
					'string' => null,
					'integer' => null,
					'float' => null,
					'boolean' => null,
					'timestamp' => null,
					'range' => null,
					'list' => null,
					'map' => null,
					'scalar-unit.size' => null,
					'scalar-unit.time' => null],
				'node_types' => [],
				'group_types' => [],
				'relationship_types' => [],
				'capability_types' => [],
				'interface_types' => [],
				'data_types' => [],
				'artifact_types' => [],
				'policy_types' => []
			];
			$parsed = yaml_parse_file(self::$_instance->_normative_pathname.self::$_instance->_normative_filename);
			foreach($parsed as $key_name => $key_value) {
				switch ($key_name) {
				case 'artifact_types':
				case 'data_types':
				case 'capability_types':
				case 'interface_types':
				case 'relationship_types':
				case 'node_types':
				case 'group_types':
				case 'policy_types':
					self::$_singleton_def[$key_name] = array_merge(self::$_singleton_def[$key_name], $key_value);
				}
			}
		}
		return self::$_instance;
	}
	public function definitions($family_type = null) {
		if (!isset($family_type)) return self::$_singleton_def;
		if (!in_array($family_type, $this->family_types()))	return self::$_singleton_def;
		return self::$_singleton_def[$family_type];
	}
	public function family_types() {
		return array_keys(self::$_singleton_def);
	}
	public function type_names($family_type = null) {
		if (!isset($family_type)) return array_merge(array_keys(self::$_singleton_def['primitive_types']), array_keys(self::$_singleton_def['node_types']), array_keys(self::$_singleton_def['group_types']),
													 array_keys(self::$_singleton_def['relationship_types']), array_keys(self::$_singleton_def['capability_types']), array_keys(self::$_singleton_def['interface_types']),
													 array_keys(self::$_singleton_def['data_types']), array_keys(self::$_singleton_def['artifact_types']), array_keys(self::$_singleton_def['policy_types']));
		if (in_array($family_type, $this->family_types())) return array_keys(self::$_singleton_def[$family_type]);
	}
	public function import_definitions($definitions) {
		if (is_array($definitions)) $parsed = $definitions;
		else if (is_file($definitions))  $parsed = yaml_parse_file($definitions);
		foreach($parsed as $key_name => $key_value) {
			switch ($key_name) {
			case 'artifact_types':
			case 'data_types':
			case 'capability_types':
			case 'interface_types':
			case 'relationship_types':
			case 'node_types':
			case 'group_types':
			case 'policy_types':
				self::$_singleton_def[$key_name] = array_merge(self::$_singleton_def[$key_name], $key_value);
			}
		}
	}
	public function check_type($typename, $family_type = null) {
		if (!isset($family_type)) {
			foreach ($this->definitions() as $f_type => $t_def) {
				if(array_key_exists($typename, $t_def)) {
					return true;
				}
			}
		}
		else {
			if (!in_array($family_type, $this->family_types())) return false; 
			if(array_key_exists($typename, $this->definitions($family_type))) return true;
		}
		return false;
	}
	public function type_info($type_name, $e_type = null) {
			$type_def = null;
			if (!isset($type_name)) return null;
			foreach($this->definitions() as $f_type => $t_defs) {
				if ($f_type != 'primitive_types') {
					if (array_key_exists($type_name, $t_defs)) {
						$type_def = $t_defs[$type_name];
						break;
					}
				}
			}
			if (!isset($type_def)) return null;
			if (array_key_exists('derived_from', $type_def)) {
				$type_def2 = $this->type_info($type_def['derived_from']);
				unset($type_def['derived_from']);
				$type_def = array_merge_recursive($type_def, $type_def2);
				if (array_key_exists('description', $type_def)) unset($type_def['description']);
				if (array_key_exists('version', $type_def)) unset($type_def['version']);
			}
			if (!isset($e_type)) return $type_def;
			if (array_key_exists($e_type, $type_def)) return [$e_type => $type_def[$e_type]];
		return null;
	}
}
class tosca_type {							// design pattern : strategy
	private $_type_name = null;
	
	function __construct($type_name) {
		// try {
			if(!isset($type_name)) {
				throw new Exception('Missing argument: Type name is mandatory');
			}
			$this->set_type($type_name);
		// } catch(Exception $e) {
			// echo $e."\n\n";
		// }
	}
	private function set_type($typename) {
		// try {
			if (isset($typename)) {
				if (!tosca_definitions::get_definitions()->check_type($typename)) {
					throw new Exception('Invalid argument: typename '.$typename.' is not a valid type name');
				}
				$this->_type_name = $typename;
			}
			else {
				throw new Exception('Missing argument: typename is mandatory');
			}
		// } catch(Exception $e) {
			// echo $e."\n\n";
		// }
	}
	private function check_name($attr_name, $attr_type, $type_to_check = null) {
		// check for attribute type
		// echo "\n attr_name: ".$attr_name."\n";
		$check = false;
		$attr_type_found = false;
		$derived_from_type = null;
		if ($type_to_check == null) $type_to_check = $this->type_name();
		foreach(tosca_definitions::get_definitions()->definitions() as $family_type => $definition) {
			foreach($definition as $ty_name => $ty_def) {
				if ($type_to_check == $ty_name) {
				// echo "type found ".$type_to_check." \n";
					if(array_key_exists('derived_from', $ty_def)) $derived_from_type = $ty_def['derived_from'];
					if(array_key_exists($attr_type, $ty_def)) {
						$attr_type_found = true;
						if ($attr_type != 'requirements') {
							foreach($ty_def[$attr_type] as $at_name => $at_def) {
								// echo "Confronto ".$attr_name." e ".$at_name."\n";
								if( $attr_name == $at_name ) {
									$check = true;
									// echo "Found! ".$attr_name." Break internal loop\n";
									break;
								}
							}
						}
						else {
							foreach($ty_def[$attr_type] as $req) {
								if (array_key_exists($attr_name,$req)) {
									$check = true;
									// echo "Found! ".$attr_name." Break internal loop\n";
									break;
								}
							}
						}
					}
				}
				if ($check) {
					// echo "Found! Break external loop\n";
					break;
				}
			}
			if ($check) {
				// echo "Found! Break external loop\n";
				break;
			}
		}
		if (!$check) {
			if(isset($derived_from_type)) {
			// type is derived; check recursively for source type
			// echo "derived from ".$derived_from_type."\n";
				$check = $this->check_name($attr_name, $attr_type, $derived_from_type);
			}
			else {
				if (!$attr_type_found) {
					// echo "attr_name not found in definitions but attr_type not present in type definitions => attr_name could have any value";
					$check = true;
				}
			}
		}
		return $check;
	}
	private function check_name_saved($attr_name, $attr_value, $type_to_check = null) {
		// check for attribute type
		// echo "\n attr_name: ".$attr_name."\n";
		$check = false;
		$derived_from_type = null;
		if ($type_to_check == null) $type_to_check = $this->type_name();
		foreach(tosca_definitions::get_definitions()->definitions() as $family_type => $definition) {
			foreach($definition as $ty_name => $ty_def) {
				if ($type_to_check == $ty_name) {
				// echo "type found ".$type_to_check." \n";
					if(array_key_exists('derived_from', $ty_def)) $derived_from_type = $ty_def['derived_from'];
					if(array_key_exists($attr_value, $ty_def)) {
						if ($attr_value != 'requirements') {
							foreach($ty_def[$attr_value] as $at_name => $at_def) {
								// echo "Confronto ".$attr_name." e ".$at_name."\n";
								if( $attr_name == $at_name ) {
									$check = true;
									// echo "Found! ".$attr_name." Break internal loop\n";
									break;
								}
							}
						}
						else {
							foreach($ty_def[$attr_value] as $req) {
								if (array_key_exists($attr_name,$req)) {
									$check = true;
									// echo "Found! ".$attr_name." Break internal loop\n";
									break;
								}
							}
						}
					}
				}
				if ($check) {
					// echo "Found! Break external loop\n";
					break;
				}
			}
			if ($check) {
				// echo "Found! Break external loop\n";
				break;
			}
		}
		if (!$check) {
			if(isset($derived_from_type)) {
			// type is derived; check recursively for source type
			// echo "derived from ".$derived_from_type."\n";
				$check = $this->check_name($attr_name, $attr_value, $derived_from_type);
			}
		}
		return $check;
	}
	public function type_name() {
		return $this->_type_name;
	}
	public function type_info($e_type = null) {
		return tosca_definitions::get_definitions()->type_info($this->_type_name, $e_type);
	}
	public function check_entity($e_type, $e_name) {
		return $this->check_name($e_name, $e_type);
	}
}
class operator {
	public static function equal($value) {
		$_structure = array('equal' => $value);
		return $_structure;
	}
	public static function greater_than($value) {
		$_structure = array('greater_than' => $value);
		return $_structure;
	}
	public static function greater_or_equal($value) {
		$_structure = array('greater_or_equal' => $value);
		return $_structure;
	}
	public static function less_than($value) {
		$_structure = array('less_than' => $value);
		return $_structure;
	}
	public static function less_or_equal($value) {
		$_structure = array('less_or_equal' => $value);
		return $_structure;
	}
	public static function in_range($lower, $upper) {
		$_structure = array();
		if(is_int($lower) and is_int($upper) and $upper >= $lower) {
			$_structure['in_range'][] = $lower;
			$_structure['in_range'][] = $upper;
		}
		else if (is_int($lower) and ($upper == 'UNBOUNDED')) {
			$_structure['in_range'][] = $lower;
			$_structure['in_range'][] = $upper;
		}
		return $_structure;
	}
	public static function valid_values($list_val) {
		$_structure = array();
		if (is_array($list_val)) {
			foreach($list_val as $val) $_structure['valid_values'][] = $val;
		}
		return $_structure;
	}
	public static function length($value) {
		$_structure = array('length' => $value);
		return $_structure;
	}
	public static function min_length($value) {
		$_structure = array('min_length' => $value);
		return $_structure;
	}
	public static function max_length($value) {
		$_structure = array('max_length' => $value);
		return $_structure;
	}
	public static function concat($list_val) {
		$_structure = array();
		if (is_array($list_val)) {
			foreach($list_val as $val) $_structure['concat'][] = $val;
		}
		return $_structure;
	}
	public static function token($string, $token, $index) {
		$_structure = array();
		if (is_string($string) and is_string($token) and is_int($index)) {
			$_structure['token'][] = $string;
			$_structure['token'][] = $token;
			$_structure['token'][] = $index;
		}
		return $_structure;
	}
	public static function get_input($name) {
		if (is_string($name)) $_structure = array('get_input' => $name);
		return $_structure;
	}
	/*
	get_property:  [ <modelable_entity_name>, <optional_req_or_cap_name>, <property_name>,  <nested_property_name_or_index_1>, ..., <nested_property_name_or_index_n> ]
	get_attribute: [ <modelable_entity_name>, <optional_req_or_cap_name>, <attribute_name>, <nested_attribute_name_or_index_1>, ..., <nested_attribute_name_or_index_n>,   ]
	*/
	public static function get_property($entity, $property, $op_name = null) {
		$_structure = array();
		if (is_string($entity) and is_string($property)) {
			$_structure['get_property'][] = $entity;
			if (isset($op_name) and is_string($op_name)) $_structure['get_property'][] = $op_name;
			$_structure['get_property'][] = $property;
		}
		return $_structure;
	}
	public static function get_attribute($entity, $attribute, $op_name = null) {
		$_structure = array();
		if (is_string($entity) and is_string($attribute)) {
			$_structure['get_attribute'][] = $entity;
			if (isset($op_name) and is_string($op_name)) $_structure['get_attribute'][] = $op_name;
			$_structure['get_attribute'][] = $attribute;
		}
		return $_structure;
	}
	public static function get_operation_output($entity, $if_name, $op_name, $out_var) {
		$_structure = array();
		if (is_string($entity) and is_string($if_name) and is_string($op_name) and is_string($out_var)) {
			$_structure['get_operation_output'][] = $entity;
			$_structure['get_operation_output'][] = $if_name;
			$_structure['get_operation_output'][] = $op_name;
			$_structure['get_operation_output'][] = $out_var;
		}
		return $_structure;
	}
	public static function get_nodes_of_type($node_type) {
		$_structure = array();
		if (is_string($node_type)) $_structure['get_nodes_of_type'] = $node_type;
		return $_structure;
	}
	public static function get_artifact($entity, $artifact, $location = null, $remove = null) {
		$_structure = array();
		if (is_string($entity) and is_string($artifact)) {
			$_structure['get_artifact'][] = $entity;
			$_structure['get_artifact'][] = $artifact;
			if (isset($location) and is_string($location)) $_structure['get_artifact'][] = $location;
			if (isset($remove) and is_bool($remove)) $_structure['get_artifact'][] = $remove;
		}
		return $_structure;
	}
	public static function map_of($node, $value) {
		$_structure = array($node, $value);
		return $_structure;
	}
}
interface tosca_component_interface {													// design pattern : composite
	public function get();
	public function add($entities);
	public function delete_childs($todel);
	public function get_child($toget);
}
class tosca_component implements tosca_component_interface {							// design pattern : composite
	protected $_structure = array();		// internal attribute for tosca entities in multidimentional-array format
	protected $_type = null;  				// internal type --> a reference to tosca_type object

	function __construct($type_name = null, $clear = true) {
		if(isset($type_name)) {
			$this->set_type($type_name, $clear);
		}
	}
	protected function error_out($e) {
		echo $e."\n\n";
	}
	protected function set_type($type_name, $clear = false) {
		$this->_type = new tosca_type($type_name);
		if ($clear) $this->simple_string('type', $type_name);
	}
	protected function simple_string($e_type, $str_value) {
		try {
			if (!isset($e_type)) { throw new Exception('Missing argument: entity_type is mandatory');
			}
			if (!is_string($e_type)) { throw new Exception('Invalid argument: entity_type must be a string');
			}
			if (isset($str_value)) {
				if (!is_string($str_value)) { throw new Exception('Invalid argument: value must be string');
				}
				$this->_structure[$e_type] = $str_value;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function simple_scalar($e_type, $str_value) {
		try {
			if (!isset($e_type)) { throw new Exception('Missing argument: entity_type is mandatory');
			}
			if (!is_string($e_type)) { throw new Exception('Invalid argument: entity_type must be a string');
			}
			if (isset($str_value)) {
				$this->_structure[$e_type] = $str_value;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function delete_single_entity($e_type) {
		try {
			if (!isset($e_type)) throw new Exception('Missing argument: entity_type is mandatory');
			if (!is_string($e_type)) throw new Exception('Invalid argument: entity_type must be a string');
			if (!isset($this->_structure[$e_type])) return;
			unset($this->_structure[$e_type]);
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}

	protected function sequence($e_type, $entities) {
		try {
			if(!is_array($entities)) { throw new Exception('Invalid argument: entities must be an array');
			}
			if (isset($e_type)) { 
				if (!is_string($e_type)) { throw new Exception('Invalid argument: entity_type must be a string');
				}
			}
			foreach($entities as $item) {
				if (isset($e_type)) {
					if (!isset($this->_structure[$e_type]))
						$this->_structure[$e_type][] = $item;
					else if (array_search($item, $this->_structure[$e_type]) === false) 
						$this->_structure[$e_type][] = $item;
				}
				else {
					if (array_search($item, $this->_structure) === false) 
						$this->_structure[] = $item;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function mapping($e_type, $entities) {
		try {
			if(!is_array($entities)) { throw new Exception('Invalid argument: entities must be an array');
			}
			if (isset($e_type)) { 
				if (!is_string($e_type)) { throw new Exception('Invalid argument: entity_type must be a string');
				}
			}
			foreach($entities as $e_name => $e_value) {
				if ($this->has_type()) {
					if (!$this->_type->check_entity($e_type, $e_name)) {
						throw new Exception('Invalid element in '.$e_type.': '.$e_name.' not allowed for '.$this->_type->type_name());
					}
				}
				if (isset($e_type)) {
					$this->_structure[$e_type][$e_name] = $e_value;
				}
				else {
					$this->_structure[$e_name] = $e_value;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function sequenced_list($e_type, $entities) {
		try {
			if(!is_array($entities)) { throw new Exception('Invalid argument: entities must be an array');
			}
			if (isset($e_type)) { 
				if (!is_string($e_type)) { throw new Exception('Invalid argument: entity_type must be a string');
				}
			}
			foreach($entities as $e_name => $e_value) {
					$s = new tosca_component;
					$s->mapping(null, [$e_name => $e_value]);
					$this->sequence($e_type, [$s->get()]);
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function delete_sequence($e_type, $todel) { 
		$this->delete_sequenced_list($e_type, $todel);
	}
	protected function delete_mapping($e_type, $todel = null) { 
		try {
			if(isset($todel) && !is_array($todel)) { throw new Exception('Invalid argument: entities to delete must be array');
			}
			if (isset($e_type)) {
				if (!is_string($e_type)) throw new Exception('Invalid argument: entity_type must be a string');
				if (!isset($this->_structure[$e_type])) return;
				if (isset($todel) ) {
					foreach($todel as $name) {
						if (array_key_exists($name, $this->_structure[$e_type])) unset($this->_structure[$e_type][$name]);
					}
					if (count($this->_structure[$e_type]) == 0) unset($this->_structure[$e_type]);
				}
				else {
					unset($this->_structure[$e_type]);
				}
			}
			else {
				if (!isset($this->_structure)) return;
				if (isset($todel) ) {
					foreach($todel as $name) {
						if (array_key_exists($name, $this->_structure)) unset($this->_structure[$name]);
					}
				}
				else {
					unset($this->_structure);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	protected function delete_sequenced_list($e_type, $todel = null) { 
		try {
			if(isset($todel) && !is_array($todel)) { throw new Exception('Invalid argument: entities to delete must be array');
			}
			if (isset($e_type)) {
				if (!is_string($e_type)) throw new Exception('Invalid argument: entity_type must be a string');
				if (!isset($this->_structure[$e_type])) return;
				if (isset($todel) ) {
					foreach($todel as $name) {
						foreach($this->_structure[$e_type] as $pos => $prop) {
							if (array_key_exists($name, $prop)) {
								unset($this->_structure[$e_type][$pos]);
								break;
							}
						}
						$this->_structure[$e_type] = array_values($this->_structure[$e_type]);
					}
					if (count($this->_structure[$e_type]) == 0) unset($this->_structure[$e_type]);
				}
				else {
					unset($this->_structure[$e_type]);
				}
			}
			else {
				if (!isset($this->_structure)) return;
				if (isset($todel) ) {
					foreach($todel as $name) {
						foreach($this->_structure as $pos => $prop) {
							if (array_key_exists($name, $prop)) {
								unset($this->_structure[$pos]);
								break;
							}
						}
						$this->_structure = array_values($this->_structure);
					}
				}
				else {
					unset($this->_structure);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	
	public function yaml($file = null) {
		$out = false;
		if (isset($file)) {
			$out = yaml_emit_file($file, $this->get());
		}
		else {
			$out = yaml_emit($this->get());
		}
		return $out;
	}
	public function has_type() {
		return ($this->_type !== null);
	}
	public function type() {
		return $this->_type->type_name();
	}
	public function is_composite() { 
		return false;
	}
	public function get($e_type = null) { 
		if (!isset($e_type)) {
			if (isset($this->_structure)) return $this->_structure;
		}
		else {
			if (isset($this->_structure[$e_type])) return $this->_structure[$e_type];
		}
	}
	public function add($entities) {  				// da fare
	}
	public function delete_childs($todel) {  		// da fare
	}
	public function get_child($toget) {  			// da fare
	}
	public function description($ds = null) {
		if (isset($ds)) $this->simple_string(__FUNCTION__, $ds);
		return $this;
	}
}
class tosca_composite extends tosca_component implements tosca_component_interface {	// design pattern : composite
	protected   $_childs = array();			// reference to child objects
	
	function __construct($type_name = null, $clear = true) {
		parent::__construct($type_name, $clear);
	}
	protected function collection($e_type, $entities) {
		if (($comp = $this->get_child($e_type)) === null) {
			$comp = new tosca_composite();
		}
		$comp->add($entities);
		$this->add([$e_type => $comp]);
	}
	protected function sequenced_collection($e_type, $entities) {
		if (($comp = $this->get_child($e_type)) === null) {
			$comp = new tosca_sequenced_list();
		}
		$comp->add($entities);
		$this->add([$e_type => $comp]);
	}
	protected function get_collection($e_type, $name) {
		$comp = $this->get_child($e_type);
		if (isset($name) && $comp !== null) { return $comp->get_child($name);
		}
		else { return $comp;
		}
	}
	protected function delete_collection($e_type, $todel) {
		if (isset($todel)) {
			$comp = $this->get_child($e_type);
			if ($comp !== null) {
				$comp->delete_childs($todel);
				if (!$comp->has_childs()) $this->delete_childs([$e_type]);
			}
		}
		else {
			$this->delete_childs([$e_type]);
		}
	}
	
	public function is_composite() { 
		return true;
	}
	public function has_childs() {
		return (count($this->_childs) != 0);
	}
	public function get($e_type = null) {
		if (isset($e_type)) return parent::get($e_type);
		$ar = array();
		$ar = array_merge($ar, $this->_structure);
		foreach($this->_childs as $name => $obj) {
			// echo " -- get ".$name."\n";
			if ($name == 'operations') {
				$ar = array_merge($ar, $obj->get());
			}
			else {
				$ar = array_merge($ar, [$name=>$obj->get()]);
			}
		}
		return $ar;
	}
	public function add($entities) {  
		try {
			if(!is_array($entities)) { throw new Exception('Invalid argument: entities must be an array');
			}
			foreach($entities as $e_name => $e_value) {
				if (!is_object($e_value)) throw new Exception('Invalid element '.$e_name.': his value must be an object');
				if ($this->has_type()) {
					if (get_class($e_value) == 'tosca_sequenced_list') {  
						foreach($e_value->get() as $value) {
							foreach($value as $attr_name => $attr_value ) {
								if (!$this->_type->check_entity($e_name, $attr_name)) {
									throw new Exception('Invalid element in '.$e_name.': '.$attr_name.' not allowed for '.$this->_type->type_name());
								}
							}
						}
					}
					else {
						foreach($e_value->get() as $attr_name => $attr_value ) {
							// echo 'check_entity('.$e_name.', '.$attr_name.")\n";
							if (!$this->_type->check_entity($e_name, $attr_name)) {
								throw new Exception('Invalid element in '.$e_name.': '.$attr_name.' not allowed for '.$this->_type->type_name());
							}
						}
					}
				}
				$this->_childs[$e_name] = $e_value;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	public function delete_childs($todel = null) {
		try {
			if (!isset($this->_childs)) return;
			if (isset($todel) ) {
				if(!is_array($todel)) {
					throw new Exception('Invalid argument: entities to delete must be array');
				}
				foreach($todel as $e_name) {
					if (array_key_exists($e_name, $this->_childs)) unset($this->_childs[$e_name]);
				}
			}
			else {
				unset($this->_childs);
				$this->_childs = array();
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	public function get_child($toget) {
		try {
			if (!is_string($toget)) {
				throw new Exception('Invalid argument: entity to get must be a string');
			}
			else {
				if(isset($this->_childs)) {
					if (array_key_exists($toget, $this->_childs)) return $this->_childs[$toget];
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return null;
	}
}
class tosca_sequenced_list extends tosca_composite implements tosca_component_interface {
	public function get($e_type = null) {
		if (isset($e_type)) return parent::get($e_type);
		$ar = array();
		$ar = array_merge($ar, $this->_structure);
		foreach($this->_childs as $name => $obj) {
			// echo " -- get ".$name."\n";
			$ar = array_merge($ar, [[$name=>$obj->get()]]);
		}
		return $ar;
	}	
}

class tosca_substitution_mapping extends tosca_component {  //OK
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'node_type': 
						$this->node_type($value);
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

	public function node_type($value = null) {
		if (isset($value) ) $this->simple_string(__FUNCTION__, $value);
		return $this;
	}
	public function capabilities($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function requirements($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->delete_single_entity($entity);
				break;
			case 'capabilities':
			case 'requirements':
				$this->delete_mapping($entity, $todel);
				break;
		}
	}
}
class tosca_parameter extends tosca_component {   			//OK
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
					case 'value':
					case 'required':
					case 'default':
					case 'status':							// status values are not controlled 
					case 'constraints':
						$this->keys(array($key => $value));
						break;
				//	case 'entry_schema':					// entity not mapped
				}
			}
		}
	}

	public function keys($keys = null) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) {
					throw new Exception('Invalid argument: keys must be an array');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'value' or $key_name == 'required' or $key_name == 'default' ) $this->simple_scalar($key_name, $key_value);
						else if ($key_name == 'status') $this->simple_string($key_name, $key_value);
						else if ($key_name == 'constraints') $this->sequence($key_name, $key_value);
						else {
							throw new Exception('Invalid argument: key name '.$key_name);
						}
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this;
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'value':
			case 'required':
			case 'default':
			case 'status':
				$this->delete_single_entity($entity);
				break;
			case 'constraints':
				$this->delete_sequence($entity, $todel);
				break;
		}
	}
}
class tosca_capability  extends  tosca_component { 			//OK
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

	public function properties($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function attributes($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'properties':
			case 'attributes':
				$this->delete_mapping($entity, $todel);
				break;
		}
	}
}
class tosca_artifact extends tosca_component { 				//OK
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
					case 'file':
					case 'repository':
					case 'deploy_path':
						$this->keys(array($key => $value));
				}
			}
		}
	}
	
	public function keys($keys = null) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) { throw new Exception('Invalid argument: keys must be an array');
				}
				if (!array_key_exists('file', $keys) && !isset($this->_structure['file'])) { 
					throw new Exception('Invalid argument: file is mandatory in tosca_artifact');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'file' or $key_name == 'repository' or $key_name == 'deploy_path' ) {
							$this->simple_string($key_name, $key_value);
						}
						else {
							throw new Exception('Invalid argument: key name '.$key_name);
						}
					} catch(Exception $e) {
						$this->error_out($e);
					}
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this;
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'file':  // file is mandatory 
				break;
			case 'description':
			case 'repository':
			case 'deploy_path':
				$this->delete_single_entity($entity);
				break;
		}
	}
}
class tosca_node_filter extends  tosca_component {    		//OK
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description': 
						$this->description($value);
						break;
					case 'properties':
						foreach ($value as $prop) {
							$this->properties($prop);
						}
						break;
					case 'capabilities':
						foreach ($value as $cap) {
							foreach($cap as $cap_name => $pr) {
								$properties = array();
								foreach($pr['properties'] as $prop) {
									$properties = array_merge($properties, $prop);
								}
								$this->capabilities(array($cap_name => $properties));
							}
						}
						break;
				}
			}
		}
	}
	
	public function properties($pr = null) {
		if(isset($pr)) $this->sequenced_list(__FUNCTION__, $pr);
		return $this;
	}
	public function capabilities($cp = null) {
		try {
			if(isset($cp)) {
				if (!is_array($cp)) { throw new Exception('Invalid argument: capabilities must be an array');
				}
				foreach($cp as $cp_name => $properies) {
					$prop = new tosca_node_filter();
					$prop->properties($properies);
					$this->sequenced_list(__FUNCTION__, [$cp_name => $prop->get()]);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this;
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->delete_single_entity($entity);
				break;
			case 'properties':
			case 'capabilities':
				$this->delete_sequenced_list($entity, $todel);
				break;
		}
	}
}
class tosca_operation extends tosca_component {   			//OK
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
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
					case 'implementation':
						if (is_string($value))
							$this->implementation($value);
						else if (is_array($value)) 
							$this->implementation($value['primary'], $value['dependencies']);
						break;
				}
			}
		}
		else if (is_string($struct)) {
			$this->implementation($struct);
		}
	}
	
	public function inputs($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function implementation($art_name = null, $dep_art_names = null) {
		try {
			if (isset($art_name)) {
				if (!is_string($art_name)) {
					throw new Exception('Invalid argument: implementation artifact must be a string');
				}
				if(isset($dep_art_names)) {
					if (!is_array($dep_art_names)) {
						throw new Exception('Invalid argument: list of dependent artifacts must be array');
					}
					if (isset($this->_structure['implementation']) && is_string($this->_structure['implementation'])) {
						unset($this->_structure['implementation']);
					}
					$this->_structure['implementation']['primary'] = $art_name;
					foreach($dep_art_names as $dependent)
						$this->_structure['implementation']['dependencies'][] = $dependent;
				}
				else {
					$this->_structure['implementation'] = $art_name;
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this;
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'implementation':
				$this->delete_single_entity($entity);
				break;
			case 'inputs':
				$this->delete_mapping($entity, $todel);
				break;
		}
	}
}
class tosca_requirement extends tosca_composite {   		//OK
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description': 
						$this->description($value);
						break;
					case 'node':
					case 'relationship':						// no extended grammar with with Property Assignments for the relationship’s Interfaces
					case 'capability':
						$this->keys(array($key => $value));
						break;
					case 'node_filter':
						$this->node_filter(new tosca_node_filter(null, $value));
						break;
				}
			}
		}
	}

	public function keys($keys = null) {
		try {
			if(isset($keys)) {
				if(!is_array($keys)) {
					throw new Exception('Invalid argument: keys must be an array');
				}
				foreach($keys as $key_name => $key_value) {
					try {
						if ( $key_name == 'node' or $key_name == 'relationship' or $key_name == 'capability') {
							$this->simple_string($key_name, $key_value);
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
		return $this;
	}
	public function node_filter($tt) {
		if (is_object($tt)) { 
			$this->add([__FUNCTION__ => $tt]);
		}
		return $this;
	}
	public function get_node_filter() { 
		return $this->get_child(substr(__FUNCTION__, 4));
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'node':
			case 'relationship':
			case 'capability':
				$this->delete_single_entity($entity);
				break;
			case 'node_filter':
				$this->delete_childs([$entity]);
				break;
		}
	}
}
class tosca_node_template  extends  tosca_composite { 		//OK
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
					case 'properties':
						$this->properties($value);
						break;
					case 'attributes':							// only short notation is mapped: <attribute_name>: <attribute_value> | { <attribute_value_expression> }
						$this->attributes($value);
						break;
					case 'artifacts':
						foreach($value as $name => $parameter) {
							$this->artifacts([$name => new tosca_artifact(null, $parameter)]);
						}
						break;
					case 'capabilities':
						foreach($value as $name => $parameter) {
							$this->capabilities([$name => new tosca_capability(null, $parameter)]);
						}
						break;
					case 'requirements':					// only extended notation in mapped
						foreach ($value as $req) {
							foreach($req as $name => $parameter) {
							//print_r($req);
								$this->requirements([$name => new tosca_requirement(null, $parameter)]);
							}
						}
						break;
					case 'interfaces':
						foreach($value as $name => $parameter) {
							$this->interfaces([$name => new tosca_interface(null, $parameter)]);
						}
						break;
/*														// entities not mapped
					case 'node_filter':
					case 'metadata':
					case 'directives':
					case 'copy':
						break;
*/
				}
			}
		}
	}

	public function properties($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function attributes($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function interfaces($entities = null) {
		if (isset($entities)) $this->collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_interfaces($name = null) {
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function capabilities($entities = null) {
		if (isset($entities)) $this->collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_capabilities($name = null) {
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function artifacts($entities = null) {
		if (isset($entities)) $this->collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_artifacts($name = null) {
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function requirements($entities = null) {
		if (isset($entities)) $this->sequenced_collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_requirements($name = null) {
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->delete_single_entity($entity);
				break;
			case 'node_filter':
				$this->delete_childs([$entity]);
				break;
			case 'properties':
			case 'attributes':
				$this->delete_mapping($entity, $todel);
				break;
			case 'artifacts':
			case 'capabilities':
			case 'interfaces':
			case 'requirements':
				$this->delete_collection($entity, $todel);
				break;
		}
	}
}
class tosca_interface  extends  tosca_composite { 	 		//OK
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
					default:								// only extended notation for operations is mapped
						$this->operations([$key => new tosca_operation(null, $value)]);
						break;
				}
			}
		}
	}

	public function inputs($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function operations($entities = null) {
		if (isset($entities)) $this->collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_operations($name = null) {
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
			switch ($entity) {
				case 'inputs':
					$this->delete_mapping($entity, $todel);
					break;
				case 'operations':
					$this->delete_collection($entity, $todel);
					break;
			}
	}
}
class tosca_group extends tosca_composite { 	 			//OK
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
						foreach($value as $name => $parameter) {
							$this->interfaces([$name => new tosca_interface(null, $parameter)]);
						}
						break;
					case 'properties':
						$this->properties($value);
						break;
					case 'targets':					// not supported in TOSCA Simple Profile in YAML Version 1.1
					case 'members':
						$this->members($value);
						break;
/*											// entities not mapped
					case 'metadata':
*/						
				}
			}
		}
	}

	public function properties($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function interfaces($entities = null) {
		if (isset($entities)) $this->collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_interfaces($name = null) {
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function members($entities = null) {
		if (isset($entities) ) $this->mapping(__FUNCTION__, $entities);
		return $this;
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->delete_single_entity($entity);
				break;
			case 'properties':
			case 'targets':
				$this->delete_mapping($entity, $todel);
				break;
			case 'interfaces':
				$this->delete_collection($entity, $todel);
				break;
		}
	}
}
class tosca_topology_template extends tosca_composite{   	//OK
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function check_group($gr_def) { 
		if (!array_key_exists('members', $gr_def)) return false;  // members is mandatory
		$check = true;
		foreach ($gr_def['members'] as $gr_member) {
			if (!array_key_exists($gr_member, $this->get_node_templates()->get())) {
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
					case 'substitution_mappings':
						$this->substitution_mappings(new tosca_substitution_mapping(null, $value));
						break;
					case 'inputs':
						foreach($value as $name => $parameter) {
							$this->inputs([$name => new tosca_parameter(null, $parameter)]);
						}
						break;
					case 'node_templates':
						foreach($value as $name => $parameter) {
							$this->node_templates([$name => new tosca_node_template(null, $parameter)]);
						}
						break;
					case 'groups':
						foreach($value as $name => $parameter) {
							$this->groups([$name => new tosca_group(null, $parameter)]);
						}
						break;
					case 'outputs':
						foreach($value as $name => $parameter) {
							$this->outputs([$name => new tosca_parameter(null, $parameter)]);
						}
						break;
/* 																					entities not mapped
					case 'relationship_templates':
					case 'policies':
					case 'workflows':
						break;
*/
				}
			}
		}
	}

	public function inputs($entities = null) {
		if (isset($entities)) $this->collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_inputs($name = null) {
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function outputs($entities = null) {
		if (isset($entities)) $this->collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_outputs($name = null) {
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function node_templates($entities = null) {
		if (isset($entities)) $this->collection(__FUNCTION__, $entities);
		return $this;
	}
	public function get_node_templates($name = null) { 
		return $this->get_collection(substr(__FUNCTION__, 4), $name);
	}
	public function substitution_mappings($sm = null) {
		if (is_object($sm)) { 
			$this->add([__FUNCTION__ => $sm]);
		}
		return $this;
	}
	public function get_substitution_mappings() {
		return $this->get_child(substr(__FUNCTION__, 4));
	}
	public function groups($entities = null) {
		try {
			if (isset($entities)) {
				if (!is_array($entities)) {
					throw new Exception('Invalid argument: entities must be array');
				}
				foreach($entities as $name => $def) {
					if (!$this->check_group($def->get())) {
						throw new Exception('Invalid argument: in group '.$name.': members is mandatory and his items must be node templates defined within this same Topology Template');
					}
				}
				$this->collection(__FUNCTION__, $entities);
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this;
	}
	public function get_groups($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
				$this->delete_single_entity($entity);
				break;
			case 'substitution_mappings':
				$this->delete_childs([$entity]);
				break;
			case 'inputs':
			case 'outputs':
			case 'node_templates':
			case 'groups':
				$this->delete_collection($entity, $todel);
				break;
		}
	}
}
class tosca_service_template extends tosca_composite {   	// to be completed
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {					// to be completed with type definitions
		if (is_file($struct)) {
			$parsed = yaml_parse_file($struct);
			if ($parsed != false) $struct = $parsed;
		}
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
					case 'imports':						// only short notation <label>: <file_name>
						foreach ($value as $file) {
							$this->imports($file);
						}
						break;
					case 'topology_template':
						$this->topology_template(new tosca_topology_template(null, $value));
						break;
					// case 'node_types':
					// case 'group_types':
					// case 'capability_types':
					// case 'interface_types':
					// case 'data_types':
					// case 'artifact_types':
					// case 'relationship_types':
					// case 'policy_types':
						// $this->$key($value, true);
						// break;
					// case 'dsl_defintions':				entities not mapped
					// case 'repositories':
						// break;
				}
			}
		}
	}

	public function tosca_definitions_version($profile) {		// required (to be done)
		if (isset($profile)) $this->simple_string(__FUNCTION__, $profile);
		return $this;
	}
	public function imports($imp) {
		if(isset($imp)) {
			$this->sequenced_list(__FUNCTION__, $imp);
			foreach($imp as $imp_name => $imp_value) {
				tosca_definitions::get_definitions()->import_definitions($imp_value);
			}
		}
		return $this;
	}
	public function metadata($mds) {
		if (isset($mds)) $this->mapping(__FUNCTION__, $mds);
		return $this;
	}
	public function topology_template($tt) {
		if (is_object($tt)) { 
			$this->add([__FUNCTION__ => $tt]);
		}
		return $this;
	}
	public function get_topology_template() { 
		return $this->get_child(substr(__FUNCTION__, 4));
	}
/*														to be done
	public function node_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_node_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function group_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_group_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function capability_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_capability_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function interface_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_interface_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function data_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_data_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function artifact_types($types = null, $new = false) {
		if (isset($types)) {
			$this->simple_list(__FUNCTION__, $types, $new);
			$this->import_definitions([__FUNCTION__ => $types]); // add the new type definitions to definitions
		}
		return $this->_structure[__FUNCTION__];
	}
	public function get_artifact_types($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
*/
	public function delete($entity, $todel = null) {	// to be completed
		switch ($entity) {
			case 'description':
			case 'tosca_definitions_version':
				$this->delete_single_entity($entity);
				break;
			case 'topology_template':
				$this->delete_childs([$entity]);
				break;
			case 'node_types':
			case 'group_types':
			case 'capability_types':
			case 'interface_types':
			case 'data_types':
			case 'artifact_types':
			case 'relationship_types':
			case 'policy_types':
				break;
			case 'metadata':
				$this->delete_mapping($entity, $todel);
				break;
			case 'imports':
				$this->delete_sequenced_list($entity, $todel);
				break;
		}
	}
	public function type_info($type_name, $e_type = null) {
		return tosca_definitions::get_definitions()->type_info($type_name, $e_type);
	}
}
?>