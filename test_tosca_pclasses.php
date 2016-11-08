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

		$sm = new tosca_substitution_mapping('tosca.nodes.nfv.VNF');
			$sm->requirements(array('virtualLink1' => operator::map_of('CP21', 'virtualLink')));
			$sm->capabilities(array('forwarder1' => operator::map_of('CP21', 'forwarder')));
		$tt->substitution_mappings($sm);
		
		$ip = new tosca_parameter('integer');
			$ip->description('Example of input parameter');
			$ip->keys(array('value' => 4, 'required' => false, 'default' => 2, 'status' => 'my status', 'constraints' => [operator::in_range(1,4), operator::equal(2)]));
		$tt->inputs(array('number_of_cpu' => $ip));

			$cap = new tosca_capability('tosca.capabilities.nfv.HA');
				$cap->properties(array('component_version' => 'version 0.1', 'admin_credential' => 'my credential'));
				$cap->attributes(array('tosca_id' => '0003', 'tosca_name' => 'vdu'));

			$ar = new tosca_artifact('tosca.artifacts.File');		
				$ar->description('Example of artifact');
				$ar->keys(array('file' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/class_template_generated.yml", 'repository' => "MY_REPOSITORY_NAME", 'deploy_path' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/"));
			$ar2 = new tosca_artifact('tosca.artifacts.File');
				$ar2->keys(['file' => 'vdu1.image']);

			$nf = new tosca_node_filter();
				$nf->properties(array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')));
				$nf->capabilities(array('host' => array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')),
										'os' => array('architecture' => operator::equal('x86_64'),'type' => 'linux', 'distribution' => 'ubuntu')));
			$rq = new tosca_requirement();    
				$rq->keys(array('node' => 'tosca.nodes.Compute', 'relationship' => 'tosca.relationships.HostedOn', 'capability' => 'tosca.capabilities.Node'))
					->node_filter($nf);

			$if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');  
				$if->inputs(array('input1' => '45', 'input2' => '65'));
				$operation = new tosca_operation();			// extended notation for operation
					$operation->description('Example of operation');
					$operation->implementation('implemen.sh', array('setup.sh','library.rpm'));
					$operation->inputs(array('input1' => '45', 'input2' => '65'));
				$if->operations(array('create' => $operation, 'configure' => $operation));

									  
			$VDU1 = new tosca_node_template('tosca.nodes.nfv.VDU');

			$VDU1->description('Example of node template')
			->properties(array('component_version' => 'version 0.1', 'admin_credential' => 'my credential'))
			->attributes(array('tosca_id' => '0003', 'tosca_name' => 'vdu'))
			->capabilities(['high_availability' => $cap])
			->artifacts(array('VM_image' => $ar2, 'my_yaml_descriptor' => $ar))
			->requirements(array('host' => $rq, 'high_availability' => $rq))
			->interfaces(array('Standard' => $if)); 
			
// echo $nf->yaml();

		$tt->node_templates(array('VDU1' => $VDU1));

		$gr1 = new tosca_group('tosca.groups.nfv.vnffg');
			$gr1->description('Example of group 1');
			$gr1->properties(array('vendor' => 'Pinco pallino SPA', 'number_of_endpoints' => 2, 'dependent_virtual_link' => array('VL1','VL2','VL4')));
			$gr1->members(array('VDU1','VDU1','VDU1'));
		$gr2 = new tosca_group('tosca.groups.nfv.vnffg');
			$gr2->description('Example of group 1');
			$gr2->properties(array('vendor' => 'Pinco pallino SPA', 'number_of_endpoints' => 2, 'dependent_virtual_link' => array('VL1','VL2','VL4')));
			$gr2->members(array('VDU1','VDU1'));
			
		$tt->groups(array('VNFFG1' => $gr1, 'VNFFG2' => $gr2));

		
		$op = new tosca_parameter('scalar-unit.size');
			$op->description('Example of output parameter');
			$op->keys(array('value' => '10 GB', 'required' => false, 'default' => '5 GB', 'status' => 'my status', 'constraints' => [operator::equal(4)]));
		$tt->outputs(array('RAM allocated' => $op));

		// $c1 = new tosca_component();
		// $c1->description('component 1');
		// $c2 = new tosca_component();
		// $c2->description('component 2');
		// $tt->inputs(['input 1' => $c1, 'input 2' => $c2]);
		// $tt->outputs(['output 1' => $c1, 'output 2' => $c2]);
		// $tt->node_templates(['node_template 1' => $c1, 'node_template 2' => $c2]);
		
	$st->topology_template($tt);

echo $st->yaml();
// print_r($st->get());
// print_r($st);


$stp = new tosca_service_template($st->get());
$ttp = $stp->get_topology_template();
$smp = $ttp->get_substitution_mappings();
$ipp = $ttp->get_inputs('number_of_cpu');
$ntp = $ttp->get_node_templates('VDU1');
$rqp = $ntp->get_requirements('host');
$nfp = $rqp->get_node_filter();
$ifp = $ntp->get_interfaces('Standard');
$opp = $ifp->get_operations('configure');



echo "\n\nPARSED ENTITIES: \n\n";
echo $stp->yaml();
print_r($ntp);
// $stp->get();
?>