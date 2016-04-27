<?php
require('cloudify_classes.php');


$bp = new cloudify_blueprint();
	$bp->tosca_definitions_version('cloudify_dsl_1_0');
	$bp->imports(array(	"C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/cloudify-types.yml", 
						"C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/cloudify-types - Copy.yml"));

$ip1 = new tosca_parameter('string');
	$ip1->keys(array('default' => 'valore di default'));
	$bp->inputs(array('vcloud_instance' => $ip1->get()));

$node = new cloudify_node_template('cloudify.nodes.Compute');
	$node->properties(array('ip' => '10.10.22.33', 'cloudify_agent' => 'cloudify.agent', 'install_agent' => false));
	$if = new cloudify_interface();
		$operation = new tosca_operation();
			$operation->description('Descrizione della operazione');
			$operation->implementation('implemen.sh', array('setup.sh','library.rpm'));
		$if->operations(array('create' => $operation->get(), 'configure' => 'vdu1_configure.sh'));
	$node->interfaces(array('cloudify.interfaces.worker_installer' => $if->get()));
	$bp->node_templates(array('NODE001' => $node->get()));
	
$op = new tosca_parameter('integer');
	$op->description('valore di output');
	$op->keys(array('value' => operator::get_input('vcloud_instance')));
	$bp->outputs(array('vcloud_instance' => $op->get()));

	
	

echo yaml_emit($bp->get());
?>