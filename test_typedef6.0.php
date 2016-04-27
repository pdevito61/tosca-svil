<?php

require('tosca_classes6.0.php');

header('Content-Type: application/json');

$td = new tosca_service_template();

$td->tosca_definitions_version('tosca_simple_yaml_1_0');
$td->description('Example of type definition for tosca classes 5.0');
	
	$nt = new tosca_node_type();
	$nt->description('Example of node type definition');
	$nt->derived_from('tosca.nodes.Root');
	$nt->version('5.3.1');
	
		$nt_pi = new tosca_property_definition('integer');
		$nt_pi->description('example of property definition');
		$nt_pi->keys(['required'=>false, 'default'=> 8, 'constraints' => [operator::in_range(1,16), operator::max_length(3)]]);
		$nt_pi->keys(['constraints' => [operator::in_range(1,156), operator::equal(66)]]);
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

$td->node_types(['tosca.nodes.my_nodetype' => $nt->get()]);

	$art = new tosca_artifact_type();
	$art->description('Example of artifact type definition');
	$art->derived_from('tosca.artifacts.Deployment.Image.VM');
	$art->version('5.3.1');
	$art->mime_type('application/octet-stream');
	$art->file_ext(['arc', 'bin', 'dump']);

$td->artifact_types(['tosca.nodes.my_artifacttype' => $art->get()]);

	$cap = new tosca_capability_type();
	$cap->description('Example of capability type definition');
	$cap->derived_from('tosca.capabilities.Node');
	$cap->properties(['port_info' => $nt_pi->get()]);
	$cap->valid_source_types(['tosca.nodes.Compute', 'tosca.nodes.SoftwareComponent']);
	$cap->attributes(['my_attribute' => $at->get()]);

$td->capability_types(['tosca.capabilities.my_capability' => $cap->get()]);

	$ift = new tosca_interface_type();
	$ift->description('Example of interface type definition');
	$ift->derived_from('tosca.interfaces.node.lifecycle.Standard');
	$ift->inputs(['my_input1'=>$nt_pi->get()]);
	$ift->operations(array('create' => $operation->get(), 
							  'configure' => 'vdu1_configure.sh'));
	
$td->interface_types(['tosca.interfaces.node.lifecycle.my_Standard' => $ift->get()]);

	$grt = new tosca_group_type();
	$grt->description('Example of group type definition');
	$grt->derived_from('tosca.groups.Root');
	$grt->properties(['my_property' => $nt_pi->get()]);
	$grt->interfaces(['my_interface2' => $if->get()]);
	$grt->targets(['tosca.nodes.Compute', 'tosca.nodes.Database']);
$td->group_types(['tosca.groups.my_group' => $grt->get()]);


	
// print_r($if);
echo $td->yaml();


$tdp = new tosca_service_template($td->get());
$ntp = $tdp->get_node_types('tosca.nodes.my_nodetype');
$prp = $ntp->get_properties('port_info');
$arp = $ntp->get_artifacts('my artifact 2');
$atp = $ntp->get_attributes('my_attribute');
$rqp = $ntp->get_requirements('my_requirement');
$cpp = $ntp->get_capabilities('my_capability');
$cpprp = $cpp->get_properties('port_info');
$ifp = $ntp->get_interfaces('my_interface');
$artp = $tdp->get_artifact_types('tosca.nodes.my_artifacttype');
$capp = $tdp->get_capability_types('tosca.capabilities.my_capability');
$iftp = $tdp->get_interface_types('tosca.interfaces.node.lifecycle.my_Standard');
$grtp = $tdp->get_group_types('tosca.groups.my_group');


// $grtp->delete('targets');
// $grtp->delete('targets', ['tosca.nodes.DBMS', 'tosca.nodes.Compute']);


$tdp->group_types(['tosca.groups.my_group' => $grtp->get()]);
$tdp->interface_types(['tosca.interfaces.node.lifecycle.my_Standard' => $iftp->get()]);
$tdp->capability_types(['tosca.capabilities.my_capability' => $capp->get()]);
$tdp->artifact_types(['tosca.nodes.my_artifacttype' => $artp->get()]);
$ntp->interfaces(['my_interface' => $ifp->get()]);
$ntp->capabilities(['my_capability' => $cpp->get()]);
$ntp->requirements(['my_requirement' => $rqp->get()]);
$ntp->attributes(['my_attribute' => $atp->get()]);
$ntp->artifacts(['my artifact 2'=>$arp->get()]);
$ntp->properties(['port_info'=>$prp->get()]);
$tdp->node_types(['tosca.nodes.my_nodetype' => $ntp->get()]);

echo "\n\nPARSED ENTITIES: \n\n";
echo $tdp->yaml();

// print_r($td->definitions());

?>