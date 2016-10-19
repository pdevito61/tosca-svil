<?php
require('tosca_pclasses.php');

header('Content-Type: application/json');

// test tosca_definitions
// $dd = tosca_definitions::get_definitions();
// print_r($dd);
// print_r($dd->type_info('tosca.nodes.Compute', 'attributes'));
// print_r($dd->definitions('data_types'));

// test tosca_type
// $my_type = new tosca_type('tosca.nodes.DBMS');
// echo $my_type->type_name();
// print_r($my_type->type_info('capabilities'));
// if($my_type->check_entity('attributes', 'state')) echo "\n\nOK";

// test tosca_component e tosca_composite
// $c1 = new tosca_component();
// $c1->simple_string('description1','valore1');
// $c1->simple_string('c1.campo2','valore2');
// $c1->simple_string('c1.campo3','valore3');
// $c2 = new tosca_component();
// $c2->simple_string('description2','valore1');
// $c2->simple_string('c2.campo2','valore2');
// $c2->simple_string('c2.campo3','valore3');
// $comp = new tosca_composite();  // capabilities
// $comp->add(['host' => $c1, 'scalable' => $c2]);
// $st = new tosca_composite('tosca.nodes.Compute');  // node_template
// $st->simple_string('description','node compute description');
// $st->add(['capabilities' => $comp]);
// print_r($st->get());
// echo $st->yaml();

$st = new tosca_service_template();
	$st->tosca_definitions_version('tosca_simple_profile_for_nfv_1_0_0')
	->description('Example of service template for tosca classes 5.0')
	->metadata(array('ID' => 'ID0001', 'vendor' => 'Telecom Italia', 'version' => 'version 1.0'))
	->imports(array('TOSCA_nfv_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml"));
	$tt = new tosca_topology_template();
		$tt->description('Example of topology template for tosca classes');

		// $c1 = new tosca_component();
		// $c1->description('component 1');
		// $c2 = new tosca_component();
		// $c2->description('component 2');
		
		// $tt->inputs(['input 1' => $c1, 'input 2' => $c2]);
		// $tt->outputs(['output 1' => $c1, 'output 2' => $c2]);
		// $tt->node_templates(['node_template 1' => $c1, 'node_template 2' => $c2]);
	
	$st->topology_template($tt);
		
echo $st->yaml();
print_r($st->get());
// print_r($st);


$stp = new tosca_service_template($st->get());
$ttp = $stp->get_topology_template();


echo "\n\nPARSED ENTITIES: \n\n";
echo $stp->yaml();
?>