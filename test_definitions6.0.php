<?php

require('tosca_classes6.0.php');

header('Content-Type: application/json');

$st = new tosca_service_template();
$st->imports(array(	'TOSCA_nfv_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml"));

// $nt = new tosca_node_type();
// $nt->derived_from('tosca.nodes.Compute');

	$art = new tosca_artifact_type();
	$art->description('Example of artifact type definition');
	$art->derived_from('tosca.artifacts.Deployment.Image.VM.ISO');
	$art->version('5.3.1');
	$art->mime_type('application/x-sh');
	$art->file_ext(['arc', 'bin', 'dump']);

	$nt_pi = new tosca_property_definition('integer');
	$nt_pi->description('example of property definition');
	$nt_pi->keys(['required'=>false, 'default'=> 8, 'constraints' => [operator::in_range(1,16), operator::max_length(3)]]);
	$nt_pi->keys(['constraints' => [operator::in_range(1,156), operator::equal(66)]]);

	$art->properties(['my_prop' => $nt_pi->get()]);

$st->artifact_types(['tosca.nodes.my_artifact_type' => $art->get()]);

			
echo $st->yaml();


// print_r($st->definitions());
// print_r($st->type_names());
// print_r($st->family_types());

print_r($st->type_info('tosca.nodes.my_artifact_type', 'properties'));

?>