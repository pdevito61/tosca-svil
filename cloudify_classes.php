<?php
require('tosca_classes3.0.php');

class cloudify_blueprint extends tosca_service_template {
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
					case 'imports':
						$this->imports($value);
						break;
					case 'inputs':
						$this->inputs($value);
						break;
					case 'outputs':
						$this->outputs($value);
						break;
					case 'node_templates':
						$this->node_templates($value);
						break;
					case 'metadata':
					case 'topology_template':
					case 'dsl_defintions':
					case 'repositories':
						break;
				}
			}
		}
	}
	protected static function import_definitions($file) {
		parent::import_definitions($file);
		$parsed = yaml_parse_file($file);
		foreach($parsed as $key_name => $key_value) {
			switch ($key_name) {
			case 'relationships':
			case 'workflows':
			case 'plugins':
			case 'policy_triggers':
				self::$_def = array_merge(self::$_def, $key_value);
			}
		}
		
	}

	public function imports($imp = null) {
		try {
			if(isset($imp)) {
				if(!is_array($imp)) {
					throw new Exception('Invalid argument');
				}
				foreach($imp as $imp_value) {
					$this->_structure['imports'][] = $imp_value;
					$this->import_definitions($imp_value);
				}
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->_structure['imports'];
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
}
class cloudify_node_template extends tosca_node_template {
	function __construct($type_name, $struct = null) {
		if (isset($type_name)) {
			parent::__construct($type_name);
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
					case 'instances':
						$this->instances($value);
						break;
					case 'relationships':
						$this->relationships($value);
						break;
				}
			}
		}
	}
	
	public function instances() {
	}
	public function relationships() {
	}
}
class cloudify_interface  extends  tosca_root {
	function __construct($struct = null) {
		if (isset($struct)) $this->set($struct);
	}
	private function set($struct) {
		if (is_array($struct)) {
			foreach($struct as $key => $value) {
				switch ($key) {
					case 'inputs':
						$this->inputs($value);
						break;
					case 'operations':
						$this->operations($value);
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
}
?>