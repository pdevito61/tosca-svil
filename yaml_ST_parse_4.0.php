<?php

require('tosca_classes4.0.php');

$filename = "ST_test_4.0_generated";
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/".$filename.".yml";
//$parsed = yaml_parse_file($yaml);
// $ST = new tosca_service_template($parsed);

$ST = new tosca_service_template($yaml);

$TT = $ST->get_topology_template();
if (isset($TT)) {
	$SM = $TT->get_substitution_mappings();
	$SM->requirements(array('virtualLink1' => operator::map_of('CP22', 'virtualLink')));
	$SM->capabilities(array('forwarder1' => operator::map_of('CP22', 'forwarder')));
	$TT->substitution_mappings($SM->get());

	$ipt_edt = $TT->get_inputs('number_of_cpu');
	if (isset($ipt_edt)) {
		$ipt_edt->description('Example of input parameter edited');
		$ipt_edt->keys(array('value' => 8, 'required' => true, 'default' => 4, 'constraints' => operator::in_range(1,16)));
		$TT->inputs(array('number_of_cpu' => $ipt_edt->get()));
	}

	$node_obj = $TT->get_node_templates('VDU1');
	if (isset($node_obj)) {
		$node_obj->properties(array('component_version' => 'version 0.3', 'admin_credential' => 'my credential modified'));
		$node_obj->attributes(array('tosca_id' => '0023', 'tosca_name' => 'NEW_VDU'));
		
		$rq_edt = $node_obj->get_requirements('high_availability');
		if(is_object($rq_edt)) {
			$rq_edt->keys(array('capability' => 'nfv.capabilities.HA', 'node' => 'tosca.nodes.nfv.VDU', 'relationship' => 'nfv.relationships.HA'));
		}
		$rq_edt1 = $node_obj->get_requirements('host');
		if(is_object($rq_edt1)) {
			$rq_edt1->keys(array('capability' => 'tosca.capabilities.Container'));
			$nf_edt = $rq_edt1->get_node_filter();
			if (is_object($nf_edt)) {
				$nf_edt->properties(array('num_cpus' => operator::in_range(1,16),'mem_size' => operator::equal('8 GB')));
				$nf_edt->capabilities(array('hosts' => array('num_cpus' => operator::in_range(1,16),'mem_size' => operator::equal('8 GB')),
										    'os' => array('architecture' => operator::equal('x86_64'),'type' => 'linux', 'distribution' => 'red_hat')));
				$rq_edt1->keys(array('node_filter' => $nf_edt->get()));
				
			}
		}
		$node_obj->requirements(array('high_availability' => $rq_edt->get(), 'host' => $rq_edt1->get()));
		
		$if_edt = $node_obj->get_interfaces('Standard');
		if (is_object($if_edt)) {
			$if_edt->inputs(array('input1' => '145', 'input2' => '165'));

			$op_edt = $if_edt->get_operations('configure');
			if (is_object($op_edt)) {
				$op_edt->description('Example of operation edited');
				$op_edt->implementation('vdu1_configure.sh', array('setup.sh','library.rpm'));
				$op_edt->inputs(array('input1' => '45', 'input2' => '65'));
				$if_edt->operations(array('configure' => $op_edt->get()));
			}
			$node_obj->interfaces(array('Standard' => $if_edt->get()));
			
		}

		$art_to_edit = $node_obj->get_artifacts('VM_image');
		if (is_object($art_to_edit)) {
			$art_to_edit->description('Example of artifact edited');
			$art_to_edit->keys(array('file' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/vdu1.image", 'repository' => "MY_REPOSITORY_NAME", 'deploy_path' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/"));
			$node_obj->artifacts(array('VM_image' => $art_to_edit->get()));
		}
		
		$TT->node_templates(array('VDU1' => $node_obj->get()));
	}
	
	
	if( is_object($gr1_edt = $TT->get_groups('VNFFG1'))) {
		$gr1_edt->properties(array('vendor' => 'Telecom Italia SPA', 'dependent_virtual_link' => array('VL1','VL2','VL3')));
		$gr1_edt->targets(array('VDU1','VDU1'));
		
		$if2_ed = $gr1_edt->get_interfaces('Standard');
		if(is_object($if2_ed)) {
			$if2_ed->inputs(array('input1' => '4445', 'input2' => '6665'));
			
			$operation2_ed = $if2_ed->get_operations('start');
			if (is_object($operation2_ed)) {
				$operation2_ed->implementation('activate.sh');
				$operation2_ed->inputs(array('input3' => '4775', 'input4' => '6775'));
				$if2_ed->operations(array('start' => $operation2_ed->get()));
			}
			$gr1_edt->interfaces(array('Standard' => $if2_ed->get()));
		}
		$TT->groups(array('VNFFG1' => $gr1_edt->get()));
	}

	
	$ST->topology_template($TT->get());
}

//var_dump($ST);
print_r($ST);


//echo yaml_emit($ST->get());
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/".$filename."_parsed.yml";
if( $ST->yaml($yaml) ) echo "TUTTO OK";

?>