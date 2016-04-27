<?php
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA - OASIS/template_for_VNF2.yml";

$parsed = yaml_parse_file($yaml);

//print_r($parsed);

$node_templates = $parsed[node_templates];
//print_r($parsed[node_templates]);


$nodes = array(
			'VD1' => $node_templates[VDU1],
			'CP22' => $node_templates[CP22],
			'internal_VL' => $node_templates[internal_VL]
		);
$nodes[VD1][requirements][0][node_filter][capabilities][1][os][properties][2][distribution] = 'redhat';

$new_template = array(
			'topology_template' => null,
			'inputs' => null,
			'node_templates' => $nodes
		);

print_r($new_template);

$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/blocks_template_generated.yml";
if( yaml_emit_file($yaml, $new_template) ) echo "TUTTO OK";


?>