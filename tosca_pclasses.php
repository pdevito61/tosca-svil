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
	private $_type_name;
	
	function __construct($type_name) {
		try {
			if(!isset($type_name)) {
				throw new Exception('Missing argument: Type name is mandatory');
			}
			$this->set_type($type_name);
		} catch(Exception $e) {
			echo $e."\n\n";
		}
	}
	private function set_type($typename) {
		try {
			if (isset($typename)) {
				if (!tosca_definitions::get_definitions()->check_type($typename)) {
					throw new Exception('Invalid argument: typename '.$typename);
				}
				$this->_type_name = $typename;
			}
			else {
				throw new Exception('Missing argument: typename is mandatory');
			}
		} catch(Exception $e) {
			echo $e."\n\n";
		}
	}
	private function check_name($attr_name, $attr_value, $type_to_check = null) {
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
interface tosca_component_interface {													// design pattern : composite
	public function get();
	public function add($entities);
	public function delete($todel);
	public function get_child($toget);
}
class tosca_component implements tosca_component_interface {							// design pattern : composite
	protected $_structure = array();		// internal attribute for tosca entities in multidimentional-array format
	protected $_type;  						// internal type --> a reference to tosca_type object

	function __construct($type_name = null, $clear = true) {
		if(isset($type_name)) {
			$this->_type = new tosca_type($type_name);
			if ($clear) $this->_structure['type'] = $type_name;
		}
	}
	protected function error_out($e) {
		echo $e."\n\n";
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
	protected function single_entity_delete($e_type) {
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
	protected function sequence_delete($e_type, $todel) {  			// da fare
	}
	protected function mapping_delete($e_type, $todel) {  			// da fare
	}
	protected function sequenced_list_delete($e_type, $todel) {  		// da fare
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
	public function is_composite() { 
		return false;
	}
	public function get() { 
		if (isset($this->_structure)) return $this->_structure;
	}
	public function add($entities) {  			// da fare
	}
	public function delete($todel) {  			// da fare
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
	public function is_composite() { 
		return true;
	}
	public function get() {
		$ar = array();
		$ar = array_merge($ar, $this->_structure);
		foreach($this->_childs as $name => $obj) {
			$ar = array_merge($ar, [$name=>$obj->get()]);
		}
		return $ar;
	}
	public function add($entities) {  
		try {
			if(!is_array($entities)) { throw new Exception('Invalid argument: entities must be an array');
			}
			foreach($entities as $e_name => $e_value) {
				if ($this->has_type()) {
					foreach($e_value->get() as $attr_name => $attr_value ) {
						if (!$this->_type->check_entity($e_name, $attr_name)) {
							throw new Exception('Invalid element in '.$e_name.': '.$attr_name.' not allowed for '.$this->_type->type_name());
						}
					}
				}
				$this->_childs[$e_name] = $e_value;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
	}
	public function delete($todel = null) {
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
class tosca_service_template extends tosca_composite {  
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {					// da completare
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
					case 'imports':
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
					case 'dsl_defintions':
					case 'repositories':
						break;
				}
			}
		}
	}

	public function imports($imp) {
		try {
			if(isset($imp)) {
				if(!is_array($imp)) { throw new Exception('Invalid argument: imports must be an array');
				}
				$this->sequenced_list(__FUNCTION__, $imp);
				foreach($imp as $imp_name => $imp_value) {
					tosca_definitions::get_definitions()->import_definitions($imp_value);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this;
	}
	public function tosca_definitions_version($profile) {
		if (isset($profile)) $this->simple_string(__FUNCTION__, $profile);
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
/*														da fare
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
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'tosca_definitions_version':
			case 'topology_template':
				$this->single_entity_delete($entity);
				break;
			case 'metadata':
			case 'node_types':
			case 'group_types':
			case 'capability_types':
			case 'interface_types':
			case 'data_types':
			case 'artifact_types':
			case 'relationship_types':
			case 'policy_types':
				$this->list_delete($entity, $todel);
				break;
			case 'imports':
				$this->list_delete($entity, $todel, true);
				break;
		}
	}
	public function type_info($type_name, $e_type = null) {
		return $this->super_type($type_name, $e_type);
	}
*/
}
class tosca_topology_template extends tosca_composite{
	function __construct($dummy = null, $struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function check_group($gr_def) {  			// da fare
		$check = true;
		foreach ($gr_def['targets'] as $gr_member) {
			if (!array_key_exists($gr_member, $this->node_templates())) {
				$check = false;
				break;
			}
		}
		return $check;
	}
	private function set($struct) {  					// da completare
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'description':
						$this->description($value);
						break;
/*					case 'inputs':
						$this->inputs($value, true);
						break;
					case 'node_templates':
						$this->node_templates($value, true);
						break;
					case 'relationship_templates':
						//$this->relationship_templates($value);
						break;
					case 'groups':
						$this->groups($value, true);
						break;
					case 'policies':
						//$this->policies($value);
						break;
					case 'outputs':
						$this->outputs($value, true);
						break;
					case 'substitution_mappings':
						$this->substitution_mappings($value, true);
						break;
*/
				}
			}
		}
	}

	public function inputs($entities) {
		if (isset($entities)) {
			$comp = new tosca_composite();
			$comp->add($entities);
			$this->add([__FUNCTION__ => $comp]);
		}
		return $this;
	}
	public function get_inputs($name = null) {
		$comp = $this->get_child(substr(__FUNCTION__, 4));
		if (isset($name) && $comp !== null) { return $comp->get_child($name);
		}
		else { return $comp;
		}
	}
	public function outputs($entities) {
		if (isset($entities)) {
			$comp = new tosca_composite();
			$comp->add($entities);
			$this->add([__FUNCTION__ => $comp]);
		}
		return $this;
	}
	public function get_outputs($name = null) {
		$comp = $this->get_child(substr(__FUNCTION__, 4));
		if (isset($name) && $comp !== null) { return $comp->get_child($name);
		}
		else { return $comp;
		}
	}
	public function node_templates($entities) {
		if (isset($entities)) {
			$comp = new tosca_composite();
			$comp->add($entities);
			$this->add([__FUNCTION__ => $comp]);
		}
		return $this;
	}
	public function get_node_templates($name = null) { 
		$comp = $this->get_child(substr(__FUNCTION__, 4));
		if (isset($name) && $comp !== null) { return $comp->get_child($name);
		}
		else { return $comp;
		}
	}
	/*														da fare
	public function groups($gr = null, $new = false) {
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
						$this->new_entity_object(__FUNCTION__, $name, $def, $new);
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
	public function get_groups($name = null) {
		return $this->entity_objects(substr(__FUNCTION__, 4), $name);
	}
	public function substitution_mappings($sm = null, $new = false) {
		if (isset($sm)) {
			$this->new_entity_object(__FUNCTION__, null, $sm, $new);
		}
		return $this->_structure['substitution_mappings'];
	}
	public function get_substitution_mappings() {
		return $this->entity_objects(substr(__FUNCTION__, 4));
	}
	public function delete($entity, $todel = null) {
		switch ($entity) {
			case 'description':
			case 'substitution_mappings':
				$this->single_entity_delete($entity);
				break;
			case 'inputs':
			case 'outputs':
			case 'node_templates':
			case 'groups':
				$this->list_delete($entity, $todel);
				break;
		}
	}
	*/
}
?>