<?php

$host_prop = array(
				array(
					'num_cpus' => array('in_range' => array( 1, 4 ))
				),
				array(
					'mem_size' => array('greater_or_equal' => '2 GB')
				)
			);
$os_prop = array(
				array(
					'architecture' => array('equal' => 'x86_64')
				),
				array(
					'type' => 'linux'
				),
				array(
					'distribution' => 'ubuntu'
				)
			);
$capabilities = array(
					array(
						'host' => array('properties' => $host_prop)
					),
					array(
						'os' => array('properties' => $os_prop)
					)
				);
$filters = array(
					'capabilities' => $capabilities
				);
$requirements = array(
					array( 
						'host' => null,
						'node_filter' => $filters
					)
			);
$artifacts = array(
				'VM_image' => 'vdu1.image'
			);
$node1 = array(
			'type' => 'tosca.nodes.nfv.VDU',
			'properties' => null,
			'requirements' => $requirements,
			'artifacts' => $artifacts,
			'Interfaces' => null
		);			
$nodes = array(
			'VD1' => $node1
		);
$template = array(
			'topology_template' => null,
			'inputs' => null,
			'node_templates' => $nodes
		);
			
print_r($template);

$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/template_generated.yml";
if( yaml_emit_file($yaml, $template) ) echo "TUTTO OK";

?>