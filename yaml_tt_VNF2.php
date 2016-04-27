<?php

require('tosca_classes2.0.php');

tosca_definitions::import_definitions("C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_definition_1_0.yml");
tosca_definitions::import_definitions("C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml");

$st = new tosca_service_template();
	$st->tosca_definitions_version('tosca_simple_profile_for_nfv_1_0_0');
	$st->description('example for VNF2');
	$st->metadata(array('ID' => 'ID0001', 'vendor' => 'Telecom Italia', 'version' => 'version 1.0'));

$tt = new tosca_topology_template();

$sm = new tosca_substitution_mapping('tosca.nodes.nfv.VNF');
	$sm->requirements(array('virtualLink1' => operator::map_of('CP21', 'virtualLink')));
	$sm->capabilities(array('forwarder1' => operator::map_of('CP21', 'forwarder')));
$tt->substitution_mappings($sm->get());

$VDU1 = new tosca_node_template('tosca.nodes.nfv.VDU');
	$nf = new tosca_node_filter();
	$nf->properties(array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')), 'host');
	$nf->properties(array('architecture' => operator::equal('x86_64'),'type' => 'linux', 'distribution' => 'ubuntu'), 'os');
	$rq = new tosca_requirement();
	$rq->keys(array('node_filter' => $nf->get()));
	$VDU1->requirements(array('host' => $rq->get()));
	$VDU1->artifacts(array('VM_image' => 'vdu1.image'));
	$if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');
	$if->operations(array('create' => 'vdu1_install.sh', 'configure' => 'vdu1_configure.sh'));
	$VDU1->interfaces(array('Standard' => $if->get()));
$tt->node_templates(array('VDU1' => $VDU1->get(), 'VDU2' => $VDU1->get(), 'VDU3' => $VDU1->get()));

$CP21 = new tosca_node_template('tosca.nodes.nfv.CP.FW');
	$CP21->properties(array('type' => 'some type'));
	$CP21->requirements(array('virtualbinding' => 'VDU1' ));
	$CP21->capabilities(array('forwarder' => null));

$CP22 = new tosca_node_template('tosca.nodes.nfv.CP');
	$CP22->properties(array('type' => 'some type'));
	$CP22->requirements(array('virtualbinding' => 'VDU1', 'virtualLink' => 'internal_VL'));
	
$CP23 = new tosca_node_template('tosca.nodes.nfv.CP');
	$CP23->properties(array('type' => 'some type'));
	$CP23->requirements(array('virtualbinding' => 'VDU2', 'virtualLink' => 'internal_VL'));
	
$CP24 = new tosca_node_template('tosca.nodes.nfv.CP');
	$CP24->properties(array('type' => 'some type'));
	$CP24->requirements(array('virtualbinding' => 'VDU3', 'virtualLink' => 'internal_VL'));
$tt->node_templates(array('CP21' => $CP21->get(), 'CP22' => $CP22->get(), 'CP23' => $CP23->get(), 'CP24' => $CP24->get()));

$VL = new tosca_node_template('tosca.nodes.nfv.VL.ELAN');
	$VL->properties(array('vendor' => 'some vendor'));
	$VL->capabilities(array('virtual_linkable' => operator::occurrences(5)));
$tt->node_templates(array('internal_VL' => $VL->get()));
$st->topology_template($tt->get());

$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TT_VNF2_generated.yml";
if( yaml_emit_file($yaml, $st->get()) ) echo "TUTTO OK";

?>