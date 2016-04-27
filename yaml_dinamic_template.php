<?php

class template {
	private $_structure = array(
			'topology_template' => null,
			'inputs' => null,
			'node_templates' => array()
		);
	public function get(){ return $this->_structure;
	}
	public function node_add( $node_name, $node) {
		$this->_structure['node_templates'][$node_name] = $node;
	}
	public function node_templates($nt = null) {
		if (isset($nt)) $this->_structure['node_templates'] = $nt;
		return $this->_structure['node_templates'];
	}
	public function node_search($node_name, $node_type=null) {
		$nt = $this->node_templates();
		$found = null;
		foreach($nt as $name => $node) {
			if(isset($node_name) and isset($node_type)) {
				if (($node['type'] == $node_type) and ($name == $node_name)) $found = $node;
			} else if (isset($node_name)){
				if ($name == $node_name) $found = $node;
			} else if (isset($node_type)) {
				if ($node['type'] == $node_type) $found = $node;
			}
		}
		return $found;
	}
}

class node {
	private $_structure = array(
			'type' => null,
			'properties' => null,
			'requirements' => null,
			'artifacts' => null,
			'interfaces' => null
		);		
	public function get(){ return $this->_structure;
	}
	public function type($typename = null) {
		if (isset($typename)) $this->_structure['type'] = $typename;
		return $this->_structure['type'];
	}
}

//creo il template 1
$ot = new template();
$on1 = new node();
$on1->type('tosca.nodes.nfv.VDU');
$on2 = new node();
$on2->type('tosca.nodes.nfv.VDX');
$ot->node_add('VD1',$on1->get());
$ot->node_add('VD2',$on2->get());

$my_template = $ot->get();

//print_r($my_template);

//creo un secondo template
$ot2 = new template();
$ot2->node_templates(array('VD3' => $on1->get()));

$node = $ot->node_search(null,'tosca.nodes.nfv.VDX' );
if(isset($node))
	print_r($node);
else
	echo "NOT FOUND";

$ot2->node_add('Y01',$node);
print_r($ot2->get());


//$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/din_template_generated.yml";
//if( yaml_emit_file($yaml, $my_template) ) echo "TUTTO OK";


?>