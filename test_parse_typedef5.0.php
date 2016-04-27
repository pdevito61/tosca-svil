<?php

require('tosca_classes5.0.php');

header('Content-Type: application/json');
$filename = "typedef_5.0_generated";
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/".$filename.".yml";

$TD = new tosca_type_definition($yaml);
$nt = $TD->get_types('node_types', 'tosca.nodes.my_nodetype');
if (isset($nt)) {
	$pr = $nt->get_properties('port_info');
	
	$ar = $nt->get_artifacts('my artifact 2');
	
	$at = $nt->get_attributes();
	
	$rq = $nt->get_requirements();
	
	$cp = $nt->get_capabilities('my_capability');
		
		$pp = $cp->get_properties('my_property');
		$aa = $cp->get_attributes('my_attribute');
	
	$if = $nt->get_interfaces('my_interface');
		
		$op = $if->get_operations('create');
		$ip = $if->get_inputs('my_input');
	
	print_r($ip);
}

$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/".$filename."_parsed.yml";
$TD->yaml($yaml);
echo $TD->yaml();

// print_r($TD);
?>