<?php

require('tosca_classes4.0.php');

	$nf = new tosca_node_filter();
	$nf->properties(array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')));
	$nf->capabilities(array('hosts' => array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')),
							'os' => array('architecture' => operator::equal('x86_64'),'type' => 'linux', 'distribution' => 'ubuntu')));
	print_r($nf->get());
	echo yaml_emit($nf->get())."\n\n";
	
	$nf_new = new tosca_node_filter($nf->get());
	
	echo yaml_emit($nf_new->get());
?>