<?php

require('tosca_classes2.0.php');

$filename = "TT_VNF2_generated";
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/".$filename.".yml";

tosca_definitions::import_definitions();

$parsed = yaml_parse_file($yaml);

$tt = new tosca_topology_template($parsed);
$vdu1 = new tosca_node_template(null, $tt->node_templates()['VDU1']);
foreach($vdu1->requirements() as $req) {
	$host = new tosca_requirement($req['host']);
	$nf = new tosca_node_filter($host->keys()['node_filter']);
	$nf->properties(array('mem_size' => operator::equal('8 GB')), 'host');
	
	//print_r($nf);
}

/*
$node = new tosca_topology_template(null, );

foreach($node->artifacts() as $ar_name => $ar_v) {
	$arts = new tosca_artifact(null, $ar_v);
	$name = $ar_name;
}
$arts->description('Esempio di file imagine');
$arts->keys(array('deploy_path' => 'new_deploy path'));
$node->artifacts(array($name => $arts->get()));

$cps = array();
foreach($node->capabilities() as $c_name => $c_val) {
	$cps[$c_name] = new tosca_capability(null, $c_val);
	$cps[$c_name]->description('Esempio di descrizione');
	$node->capabilities(array($c_name => $cps[$c_name]->get()));
}
print_r($cps);

$tt->node_templates(array('VDU1_new' => $node->get()));
*/


$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/".$filename."_reconverted.yml";
if( yaml_emit_file($yaml, $nf->get()) ) echo "TUTTO OK";


?>