<?php

require('tosca_classes5.0.php');

header('Content-Type: application/json');

$td = new tosca_type_definition();

$td->tosca_definitions_version('tosca_simple_yaml_1_0');
$td->description('Example of type definition for tosca classes 5.0');
$td->imports(array(	'TOSCA_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_definition_1_0.yml"));
	
	$nt = new tosca_node_type();
	$nt->description('Example of node type definition');
	$nt->derived_from('tosca.nodes.Root');
	$nt->version('5.3.1');
	
		$nt_pi = new tosca_property_definition('integer');
		$nt_pi->description('example of property definition');
		$nt_pi->keys(['required'=>false, 'default'=> 8, 'constraints' => operator::in_range(1,16)]);
	$nt->properties(['port_info'=>$nt_pi->get()]);

		$ar = new tosca_artifact('tosca.artifacts.File');		
		$ar->description('Example of artifact');
		$ar->keys(array('file' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/class_template_generated.yml", 'repository' => "MY_REPOSITORY_NAME", 'deploy_path' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/"));
	$nt->artifacts(['my artifact 1'=>$ar->get(), 'my artifact 2'=>$ar->get()]);
	
		$at = new tosca_attribute_definition('string');
		$at->description('example of attribute definition');
		$at->keys(['default' => 'default value', 'status' => 'my_status']);
	$nt->attributes(['my_attribute' => $at->get()]);
		
		$rq = new tosca_requirement_definition();
		$rq->description('example of requirement definition');
		$rq->keys(array('capability' => 'tosca.capabilities.Node', 'node' => 'tosca.nodes.Compute', 'relationship' => 'tosca.relationships.HostedOn'));
		$rq->occurrences(1, 'UNBOUNDED');
	$nt->requirements(['my_requirement' => $rq->get()]);
	
		$cp = new tosca_capability_definition('tosca.capabilities.Node');
		$cp->valid_source_types(['tosca.nodes.Compute', 'tosca.nodes.SoftwareComponent']);
		$cp->properties(['my_property'=>$nt_pi->get()]);
		$cp->attributes(['my_attribute' => $at->get()]);
	$nt->capabilities(['my_capability' => $cp->get()]);
		
		$if = new tosca_interface_definition('tosca.interfaces.node.lifecycle.Standard');
		$if->inputs(['my_input'=>$nt_pi->get()]);
			$operation = new tosca_operation();			// extended notation for operation
				$operation->description('Example of operation');
				$operation->implementation('implemen.sh', array('setup.sh','library.rpm'));
				$operation->inputs(array('input1' => '45', 'input2' => '65'));
		$if->operations(array('create' => $operation->get(), 
							  'configure' => 'vdu1_configure.sh'));     // short notation for operation
	$nt->interfaces(['my_interface' => $if->get()]);

		
		print_r($if);
	
$td->types('node_types', array('tosca.nodes.my_nodetype' => $nt->get()));
	
// $td->types('node_types');

echo $td->yaml();
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/typedef_5.0_generated.yml";
$td->yaml($yaml);
// print_r($nt);


?>