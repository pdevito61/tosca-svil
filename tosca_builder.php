<?php
require('tosca_pclasses.php');

define("_NONAME", "NONAME");
define("_NOTYPE", "NOTYPE");

// parts of a template (entities)
define("_TEMPLATE", 		"TEMPLATE");
define("_TOPOLOGY", 		"TOPOLOGY");
	define("_EXPORT_MAPPING",	"SUB_MAPPING");
	define("_INPUT", 			"INPUT");
	define("_NODE", 			"NODE");
		define("_REQUIREMENT", 		"REQUIREMENT");
			define("_REQ_FILTER", 		"REQ_NODE_FILTER");
		define("_CAPABILITY", 		"CAPABILITY");
		define("_ARTIFACT", 		"ARTIFACT");
		define("_NODE_IF", 			"NODE_IF");
			define("_NODE_IF_OP", 		"NODE_IF_OPERATION");
	define("_GROUP", 			"GROUP");
		define("_GROUP_IF", 		"GROUP_IF");
			define("_GROUP_IF_OP", 		"GROUP_IF_OPERATION");
	define("_OUTPUT", 			"OUTPUT");


interface template_builder_interface {
	public function create($part, $path, $name, $type);
	public function modify($part, $path,  $attribute, $value, $name);
	public function delete($part, $path,  $name, $attribute, $todel);
	public function get();
}
class tosca_builder implements template_builder_interface {
	private $_template = null;
	private $_expt = null;

	// references to current entities when they are collected
	private $_current_input = null;
	private $_current_node = null;
		private $_current_requirement = null;
		private $_current_capability = null;
		private $_current_artifact = null;
		private $_current_node_interface = null;
			private $_current_node_operation = null;
	private $_current_group = null;
		private $_current_group_interface = null;
			private $_current_group_operation = null;
	private $_current_output = null;

	public function __construct($template = null) {
		if (isset($template) && is_string($template)) { $this->_template = new tosca_service_template(yaml_parse($template));
		}
		else { $this->_template = new tosca_service_template();
		}
	}
	public function get() {
		return $this->_template->yaml();
	}
	public function create($part, $path, $name = null, $type = null) {  
		try {
			echo "create --> part = ".$part." , path = ".$path." , name = ".$name." , type = ".$type."\n";
			switch ($part) {
				case _TOPOLOGY : 
					$this->topology_template_add();
					break;
				case _NODE : 			
					$this->node_template_add($name, $type);
					break;
				case _INPUT :
					$this->input_add($name, $type);
					break;
				case _OUTPUT : 			
					$this->output_add($name, $type);
					break;
				case _EXPORT_MAPPING :
					$this->substitution_mapping_add();
					break;
				case _REQUIREMENT : 
					$this->requirement_add($name);
					break;
				case _CAPABILITY :
					$this->capability_add($name, $type);
					break;
				case _ARTIFACT : 	
					$this->artifact_add($name, $type);
					break;
				case _REQ_FILTER :
					$this->requirement_node_filter_add();
					break;
				case _NODE_IF :
					$this->node_template_interface_add($name, $type);
					break;
				case _NODE_IF_OP : 
					$this->node_template_if_operation_add($name);
					break;
				case _GROUP : 
					$this->group_add($name, $type);
					break;
				case _GROUP_IF : 
					$this->group_interface_add($name, $type);
					break;
				case _GROUP_IF_OP :
					$this->group_if_operation_add($name);
					break;
				default:				// invalid part name
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->error_status();
	}
	public function modify($part, $path,  $attribute, $value, $name = null) {  	
		try {
			echo "modify --> part = ".$part." , path = ".$path." ,  attribute = ".$attribute." , name = ".$name."\n";
			switch ($part) {
				case _TEMPLATE : 
					$this->service_template_mod($attribute, $value);
					break;
				case _TOPOLOGY : 
					$this->topology_template_mod($attribute, $value);
					break;
				case _NODE : 			
					$this->node_template_mod($name, $attribute, $value);
					break;
				case _INPUT :
					$this->input_mod($name, $attribute, $value);
					break;
				case _OUTPUT : 			
					$this->output_mod($name, $attribute, $value);
					break;
				case _EXPORT_MAPPING :
					$this->substitution_mapping_mod($attribute, $value);
					break;
				case _REQUIREMENT : 
					$this->requirement_mod($name, $attribute, $value);
					break;
				case _CAPABILITY :
					$this->capability_mod($name, $attribute, $value);
					break;
				case _ARTIFACT : 	
					$this->artifact_mod($name, $attribute, $value);
					break;
				case _REQ_FILTER :
					$this->requirement_node_filter_mod($attribute, $value);
					break;
				case _NODE_IF :
					$this->node_template_interface_mod($name, $attribute, $value);
					break;
				case _NODE_IF_OP : 
					$this->node_template_if_operation_mod($name, $attribute, $value);
					break;
				case _GROUP : 
					$this->group_mod($name, $attribute, $value);
					break;
				case _GROUP_IF : 
					$this->group_interface_mod($name, $attribute, $value);
					break;
				case _GROUP_IF_OP :
					$this->group_if_operation_mod($name, $attribute, $value);
					break;
				default:				// invalid part name
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->error_status();
	}
	public function delete($part, $path,  $name = null, $attribute = null, $todel = null) {  // TBD controllo su obbligatorietà di $name
		try {
			switch ($part) {
				case _TEMPLATE : 
					$this->service_template_del($attribute, $todel);
					break;
				case _TOPOLOGY : 
					$this->topology_template_del($attribute, $todel);
					break;
				case _NODE : 			
					$this->node_template_del($name, $attribute, $todel);
					break;
				case _INPUT :
					$this->input_del($name, $attribute, $todel);
					break;
				case _OUTPUT : 			
					$this->output_del($name, $attribute, $todel);
					break;
				case _EXPORT_MAPPING :
					$this->substitution_mapping_del($attribute, $todel);
					break;
				case _REQUIREMENT : 
					$this->requirement_del($name, $attribute, $todel);
					break;
				case _CAPABILITY :
					$this->capability_del($name, $attribute, $todel);
					break;
				case _ARTIFACT : 	
					$this->artifact_del($name, $attribute, $todel);
					break;
				case _REQ_FILTER :
					$this->requirement_node_filter_del($attribute, $todel);
					break;
				case _NODE_IF :
					$this->node_template_interface_del($name, $attribute, $todel);
					break;
				case _NODE_IF_OP : 
					$this->node_template_if_operation_del($name, $attribute, $todel);
					break;
				case _GROUP : 
					$this->group_del($name, $attribute, $todel);
					break;
				case _GROUP_IF : 
					$this->group_interface_del($name, $attribute, $todel);
					break;
				case _GROUP_IF_OP :
					$this->group_if_operation_del($name, $attribute, $todel);
					break;
				default:				// invalid part name
					break;
			}
		} catch(Exception $e) {
			$this->error_out($e);
		}
		return $this->error_status();
	}

	public function st() {
		$this->reset_currents();
		return $this;
	}
	public function input($name) {
		$this->reset_currents();
		// setting...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_inputs($name)) !== null) ) {  // TBD error handling
			$this->_current_input = $name;
		}
		return $this;
	}
	public function node($name) {
		$this->reset_currents();
		// setting...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($name)) !== null) ) {  // TBD error handling
			$this->_current_node = $name;
		}
		return $this;
	}
	public function requirement($name) {
		$this->reset_currents_2lev();
		// setting ...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($rq = $nt->get_requirements($name)) !== null) ) {  // TBD error handling
			$this->_current_requirement = $name;
		}
		return $this;
	}
	public function capability($name) {
		$this->reset_currents_2lev();
		// setting ...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($cp = $nt->get_capabilities($name)) !== null) ) {  // TBD error handling
			$this->_current_capability = $name;
		}
		return $this;
	}
	public function artifact($name) {
		$this->reset_currents_2lev();
		// setting ...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($ar = $nt->get_artifacts($name)) !== null) ) {  // TBD error handling
			$this->_current_artifact = $name;
		}
		return $this;
	}
	public function node_interface($name) {
		$this->reset_currents_2lev();
		// setting ...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($if = $nt->get_interfaces($name)) !== null) ) {  // TBD error handling
			$this->_current_node_interface = $name;
		}
		return $this;
	}
	public function node_operation($name) {
		$this->reset_currents_3lev();
		// setting ...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_node_interface)) !== null) && 
			 (($op = $if->get_operations($name)) !== null) ) {  // TBD error handling
			$this->_current_node_operation = $name;
		}
		return $this;
	}
	public function group($name) {
		$this->reset_currents();
		// setting...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_groups($name)) !== null) ) {  // TBD error handling
			$this->_current_group = $name;
		}
		return $this;
	}
	public function group_interface($name) {
		$this->reset_currents_2lev();
		// setting ...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_groups($this->_current_group)) !== null) &&
			 (($if = $nt->get_interfaces($name)) !== null) ) {  // TBD error handling
			$this->_current_group_interface = $name;
		}
		return $this;
	}
	public function group_operation($name) {
		$this->reset_currents_3lev();
		// setting ...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_groups($this->_current_group)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_group_interface)) !== null) && 
			 (($op = $if->get_operations($name)) !== null) ) {  // TBD error handling
			$this->_current_group_operation = $name;
		}
		return $this;
	}
	public function output($name) {
		$this->reset_currents();
		// setting...
		if ( is_a($this->_template, 'tosca_service_template') &&
			 (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_outputs($name)) !== null) ) {  // TBD error handling
			$this->_current_output = $name;
		}
		return $this;
	}
	public function path() {
		$path = '|';
		if ($this->_current_input != null) {
			$path .= 'input:'.$this->_current_input.'|';
		}
		else if ($this->_current_node != null) {
			$path .= 'node:'.$this->_current_node.'|';
			if ($this->_current_requirement != null) {
				$path .= 'requirement:'.$this->_current_requirement.'|';
			}
			else if ($this->_current_capability != null) {
				$path .= 'capability:'.$this->_current_capability.'|';
			}
			else if ($this->_current_artifact != null) {
				$path .= 'artifact:'.$this->_current_artifact.'|';
			}
			else if ($this->_current_node_interface != null) {
				$path .= 'interface:'.$this->_current_node_interface.'|';
				if ($this->_current_node_operation != null) {
					$path .= 'operation:'.$this->_current_node_operation.'|';
				}
			}
		}
		else if ($this->_current_group != null) {
			$path .= 'group:'.$this->_current_group.'|';
			if ($this->_current_group_interface != null) {
				$path .= 'interface:'.$this->_current_group_interface.'|';
				if ($this->_current_group_operation != null) {
					$path .= 'operation:'.$this->_current_group_operation.'|';
				}
			}
		}
		else if ($this->_current_output != null) {
			$path .= 'output:'.$this->_current_output.'|';
		}
		return $path;
	}
	
	public function error_status() {
		return ($this->_expt !== null);
	}
	public function error() {
		return $this->_expt;
	}
	public function reset_error() {
		$this->_expt = null;
	}
	
	private function error_out($e) {
		$this->_expt = $e;
		echo $e."\n\n";
	}
	private function check_tt() {
		if ($this->_template->get_topology_template() === null) $this->topology_template_add();
		return $this;
	}
	private function reset_currents() {
		$this->_current_input = null;
		$this->_current_node = null;
		$this->_current_requirement = null;
		$this->_current_capability = null;
		$this->_current_artifact = null;
		$this->_current_node_interface = null;
		$this->_current_node_operation = null;
		$this->_current_group = null;
		$this->_current_group_interface = null;
		$this->_current_group_operation = null;
		$this->_current_output = null;
	}
	private function reset_currents_2lev() {
		$this->_current_requirement = null;
		$this->_current_capability = null;
		$this->_current_artifact = null;
		$this->_current_node_interface = null;
		$this->_current_node_operation = null;
		$this->_current_group_interface = null;
		$this->_current_group_operation = null;
	}
	private function reset_currents_3lev() {
		$this->_current_node_operation = null;
		$this->_current_group_operation = null;
	}

	private function topology_template_add() {							// ok + error handling
		$tt = new tosca_topology_template();
		$this->_template->topology_template($tt);
	}
	private function substitution_mapping_add() {						// ok to test
		$newp = new tosca_substitution_mapping();
		$this->check_tt();
		if (($tt = $this->_template->get_topology_template()) !== null) {
			$tt->substitution_mappings($newp);
		}
		else { throw new Exception('Invalid topology template');
		}
	}
	private function input_add($name, $type) {							// ok to test
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if (!isset($type)) { throw new Exception('Invalid argument : type is mandatory');
		}
		$newp = new tosca_parameter($type);
		$this->check_tt();
		if (($tt = $this->_template->get_topology_template()) !== null) {
			$tt->inputs([$name => $newp]);
			$this->_current_input = $name;
		}
		else { throw new Exception('Invalid topology template');
		}
	}
	private function node_template_add($name, $type) {					// ok + error handling
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if (!isset($type)) { throw new Exception('Invalid argument : type is mandatory');
		}
		$newp = new tosca_node_template($type);
		$this->check_tt();
		if (($tt = $this->_template->get_topology_template()) !== null) {
			$tt->node_templates([$name => $newp]);
			$this->_current_node = $name;
		}
		else { throw new Exception('Invalid topology template');
		}
	}
	private function node_template_interface_add($name, $type) {		// ok + error handling
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if (!isset($type)) { throw new Exception('Invalid argument : type is mandatory');
		}
		$newp = new tosca_interface($type);
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) ) { 
			$nt->interfaces([$name => $newp]);
			$this->_current_node_interface = $name;
		}
		else { throw new Exception('Invalid path: '.$this->path());
		}
	}
	private function node_template_if_operation_add($name) {			// ok + error handling
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		$newp = new tosca_operation();
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_node_interface)) !== null) ) { 
			$if->operations([$name => $newp]);
			$this->_current_node_operation = $name;
		}
		else { throw new Exception('Invalid path: '.$this->path());
		}
	}
	private function output_add($name, $type) {							// ok to test
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if (!isset($type)) { throw new Exception('Invalid argument : type is mandatory');
		}
		$newp = new tosca_parameter($type);
		$this->check_tt();
		if (($tt = $this->_template->get_topology_template()) !== null) {
			$tt->outputs([$name => $newp]);
			$this->_current_output = $name;
		}
		else { throw new Exception('Invalid topology template');
		}
	}
	private function requirement_add($name) {							// ok to test
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		$newp = new tosca_requirement();
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) ) { 
			 $nt->requirements([$name => $newp]);
			 $this->_current_requirement = $name;
		}
		else { throw new Exception('Invalid path: '.$this->path());
		}
	}
	private function capability_add($name, $type) {
	}
	private function artifact_add($name, $type) {
	}
	private function requirement_node_filter_add() {
	}
	private function group_add($name, $type) {
	}
	private function group_interface_add($name, $type) {
	}
	private function group_if_operation_add($name) {
	}

	private function service_template_mod($attribute, $value) {							// ok + error handling
		switch ($attribute) {
			case 'description':
			case 'imports':
			case 'metadata':
			case 'tosca_definitions_version':
				$this->_template->$attribute($value);
				break;
			default:  
				throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
				break;
		}
	}
	private function topology_template_mod($attribute, $value) {						// ok + error handling
		if (($tt = $this->_template->get_topology_template()) !== null) {
			switch ($attribute) {
				case 'description':
					$tt->$attribute($value);
					break;
				default:  
					throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
					break;
			}
		}
		else { throw new Exception('Invalid topology template');
		}
	}
	private function substitution_mapping_mod($attribute, $value) {						// ok to test
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($sm = $tt->get_substitution_mappings()) !== null) ) { 
			switch ($attribute) {
				case 'node_type': 
				case 'description':
				case 'capabilities':
				case 'requirements':
					$sm->$attribute($value);
					break;
				default:  
					throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
					break;
			}
		}
		else { throw new Exception('Invalid topology template');
		}
	}
	private function input_mod($name, $attribute, $value) {								// ok to test
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($ip = $tt->get_inputs($name)) !== null) ) { 
			switch ($attribute) {
				case 'description':
					$ip->description($value);
					break;
				case 'value':
				case 'required':
				case 'default':
				case 'status':							// status values are not controlled 
				case 'constraints':
					$ip->keys(array($attribute => $value));
					break;
				//	case 'entry_schema':					// entity not mapped
				default:  
					throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
					break;
			}
		}
		else { throw new Exception('Invalid argument : entity '.$name.' not found in path '.$this->path());
		}
	}
	private function node_template_mod($name, $attribute, $value) {						// ok + error handling
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($name)) !== null) ) { 
			switch ($attribute) {
				case 'description':
				case 'properties':
				case 'attributes':
					$nt->$attribute($value);
					break;
				default:  
					throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
					break;
			}
		}
		else { throw new Exception('Invalid argument : entity '.$name.' not found in path '.$this->path());
		}
	}
	private function node_template_interface_mod($name, $attribute, $value) {			// ok + error handling
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($if = $nt->get_interfaces($name)) !== null) ) {
			switch ($attribute) {
				case 'inputs':
					$if->$attribute($value);
					break;
				default:
					throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
					break;
			}
		}
		else { throw new Exception('Invalid argument : entity '.$name.' not found in path '.$this->path());
		}
	}
	private function node_template_if_operation_mod($name, $attribute, $value) {		// ok + error handling
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($if = $nt->get_interfaces($this->_current_node_interface)) !== null) && 
			 (($op = $if->get_operations($name)) !== null) ) { 
			switch ($attribute) {
				case 'description':
				case 'inputs':
				case 'implementation':
					$op->$attribute($value);
					break;
				default:
					throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
					break;
			}
		}
		else { throw new Exception('Invalid argument : entity '.$name.' not found in path '.$this->path());
		}
	}
	private function output_mod($name, $attribute, $value) {							// ok to test
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($ip = $tt->get_outputs($name)) !== null) ) { 
			switch ($attribute) {
				case 'description':
					$ip->description($value);
					break;
				case 'value':
				case 'required':
				case 'default':
				case 'status':							// status values are not controlled 
				case 'constraints':
					$ip->keys(array($attribute => $value));
					break;
				//	case 'entry_schema':					// entity not mapped
				default:  
					throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
					break;
			}
		}
		else { throw new Exception('Invalid argument : entity '.$name.' not found in path '.$this->path());
		}
	}
	private function requirement_mod($name, $attribute, $value) {						// ok to test
		if (!isset($name)) { throw new Exception('Invalid argument : entity name is mandatory');
		}
		if ( (($tt = $this->_template->get_topology_template()) !== null) &&
			 (($nt = $tt->get_node_templates($this->_current_node)) !== null) &&
			 (($if = $nt->get_requirements($name)) !== null) ) {
			switch ($attribute) {
				case 'description': 
					$this->description($value);
					break;
				case 'node':
				case 'relationship':						// no extended grammar with with Property Assignments for the relationship’s Interfaces
				case 'capability':
					$this->keys(array($attribute => $value));
					break;
				default:
					throw new Exception('Invalid argument : attribute '.$attribute.' is not a valid one');
					break;
			}
		}
		else { throw new Exception('Invalid argument : entity '.$name.' not found in path '.$this->path());
		}
	}
	private function capability_mod($name, $attribute, $value) {
	}
	private function artifact_mod($name, $attribute, $value) {
	}
	private function requirement_node_filter_mod($attribute, $value) {
	}
	private function group_mod($name, $attribute, $value) {
	}
	private function group_interface_mod($name, $attribute, $value) {
	}
	private function group_if_operation_mod($name, $attribute, $value) {
	}
	
	private function service_template_del($attribute, $todel) {
	}
	private function topology_template_del($attribute, $todel) {
	}
	private function node_template_del($name, $attribute, $todel) {
	}
	private function input_del($name, $attribute, $todel) {
	}
	private function output_del($name, $attribute, $todel) {
	}
	private function substitution_mapping_del($attribute, $todel) {
	}
	private function requirement_del($name, $attribute, $todel) {
	}
	private function capability_del($name, $attribute, $todel) {
	}
	private function artifact_del($name, $attribute, $todel) {
	}
	private function requirement_node_filter_del($attribute, $todel) {
	}
	private function node_template_interface_del($name, $attribute, $todel) {
	}
	private function node_template_if_operation_del($name, $attribute, $todel) {
	}
	private function group_del($name, $attribute, $todel) {
	}
	private function group_interface_del($name, $attribute, $todel) {
	}
	private function group_if_operation_del($name, $attribute, $todel) {
	}
}
class director {
	private $_builder = null;
	
    function __construct($builder_in) {
	     $this->_builder = $builder_in;
    }	
	public function get() {
		return $this_builder->get();
	}
	public function build($entity, $name = 'NO_NAME', $type = 'NO_TYPE') {
	}
	public function parse($to_parse) {
	}
}
?>

