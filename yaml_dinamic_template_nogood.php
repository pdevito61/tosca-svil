<?php

class template {
	private static $_structure = array(
			'topology_template' => null,
			'inputs' => null,
			'node_templates' => array()
		);
	public static function get(){ return self::$_structure;
	}
	public static function node_templates( $name, $node) {
		self::$_structure['node_templates'][$name] = $node;
	}
}

class node {
	private static $_structure = array(
			'type' => null,
			'properties' => null,
			'requirements' => null,
			'artifacts' => null,
			'interfaces' => null
		);		
	public static function get(){ return self::$_structure;
	}
	public static function type($typedef) {
		self::$_structure['type'] = $typedef;
	}
}

node::type('tosca.nodes.nfv.VDU');
$my_node1 = node::get();
node::type('tosca.nodes.nfv.VDX');
$my_node2 = node::get();
template::node_templates('VD1',$my_node1);
template::node_templates('VD2',$my_node2);
$my_template = template::get();

print_r($my_template);

$obj_temlp = new template();
$my_tp2 = $obj_temlp->get();
print_r($my_tp2);


$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/din_template_generated.yml";
if( yaml_emit_file($yaml, $my_template) ) echo "TUTTO OK";


?>