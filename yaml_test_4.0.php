<?php

require('tosca_classes4.0.php');

$st = new tosca_service_template();
	$st->imports(array(	'TOSCA_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_definition_1_0.yml",
						'TOSCA_nfv_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml"));
/*  test node_template
	$ws = new tosca_node_template('tosca.nodes.WebServer');
	$ws->description('Example of web server');
	$ws->properties(array('component_version' => 'version 0.1', 'admin_credential' => 'my credential'));
	$ws->attributes(array('tosca_id' => '0003', 'tosca_name' => 'my_web_server', 'state' => 'my_state'));
	print_r($ws);
	echo yaml_emit($ws->get())."\n\n";
	//$ws->del_properties(array('admin_credential', 'component_version'));
	$ws->delete('description', array('component_version'));
	print_r($ws);
	echo yaml_emit($ws->get())."\n\n";
*/	
/*	test parameter
	$ip = new tosca_parameter('integer');
		$ip->description('Example of input parameter');
		$ip->keys(array('value' => 4, 'required' => false, 'default' => 2, 'status' => 'my status', 'constraints' => operator::in_range(1,4)));

	print_r($ip->get());
	echo yaml_emit($ip->get())."\n\n";
	$ip_new = new tosca_parameter(null, $ip->get());
	echo yaml_emit($ip_new->get());
*/
/*  test artifact
	$ar = new tosca_artifact('tosca.artifacts.File');		// extended notation for artifacts
	$ar->description('Example of artifact');
	$ar->keys(array('file' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/class_template_generated.yml", 'repository' => "MY_REPOSITORY_NAME", 'deploy_path' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/"));

	print_r($ar->get());
	echo yaml_emit($ar->get())."\n\n";
	$ar_new = new tosca_artifact(null, $ar->get());
	echo yaml_emit($ar_new->get());
*/
/*  test node_filter	
*/
	$nf = new tosca_node_filter();
	$nf->properties(array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')));
	$nf->capabilities(array('hosts' => array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')),
							'os' => array('architecture' => operator::equal('x86_64'),'type' => 'linux', 'distribution' => 'ubuntu')));
	print_r($nf->get());
	echo yaml_emit($nf->get())."\n\n";
	$nf->delete('capabilities', array('os'));
	print_r($nf->get());
	echo yaml_emit($nf->get())."\n\n";
	
	// $nf_edt = new tosca_node_filter($nf->get());
	// echo yaml_emit($nf_edt->get());
	// $nf_edt->properties(array('num_cpus' => operator::in_range(1,16),'mem_size' => operator::equal('8 GB')));
	// $nf_edt->capabilities(array('hosts' => array('num_cpus' => operator::in_range(1,16),'mem_size' => operator::equal('8 GB')),
								// 'os' => array('distribution' => 'red_hat')));

		
?>