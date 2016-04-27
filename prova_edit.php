<?php
require('tosca_classes2.0.php');

tosca_definitions::import_definitions();

$VDU1 = new tosca_node_template('tosca.nodes.nfv.VDU');
	
	$nf = new tosca_node_filter();
	$nf->properties(array('num_cpus' => operator::in_range(1,4),'mem_size' => operator::equal('2 GB')), 'host');
	$nf->properties(array('architecture' => operator::equal('x86_64'),'type' => 'linux', 'distribution' => 'ubuntu'), 'os');
	$rq = new tosca_requirement();
	$rq->keys(array('node_filter' => $nf->get()));
	$VDU1->requirements(array('host' => $rq->get()));
	
	$ar = new tosca_artifact('tosca.artifacts.File');
	$ar->keys(array('file' => "class_template_generated.yml", 'repository' => "MY_REPOSITORY_NAME"));
	$VDU1->artifacts(array('my_yaml_descriptor' => $ar->get(), 'my_env_file' => "env_file.sh"));
	
	$if = new tosca_interface('tosca.interfaces.node.lifecycle.Standard');
	$if->operations(array('create' => 'vdu1_install.sh', 'configure' => 'vdu1_configure.sh'));
	$VDU1->interfaces(array('Standard' => $if->get()));

//print_r($VDU1->get());

	$VDU1->artifacts(array('my_env_file' => "env_file_new.sh"));


	$arts = $VDU1->artifacts();
	foreach($arts as $name => $cont) {
		/*if (is_array($cont)) {
			$art_obj = new tosca_artifact($cont['type']);
			if (isset($cont['file'])) $art_obj->keys(array('file' => $cont['file']));
			if (isset($cont['repository'])) $art_obj->keys(array('repository' => $cont['repository']));
			if (isset($cont['deploy_path'])) $art_obj->keys(array('deploy_path' => $cont['deploy_path']));
			$VDU1->artifacts(array($name.'_new' => $art_obj->get()));
		}
		else {
			$art_obj = new tosca_artifact('tosca.artifacts.File');
			$art_obj->keys(array('file' => $cont));
			
		}
		*/
		$art_obj = new tosca_artifact(null, $cont);
		
		$VDU1->artifacts(array($name.'_new' => $art_obj->get()));
	}
	
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/prova_edit.yml";
if( yaml_emit_file($yaml, $VDU1->get()) ) echo "TUTTO OK";
	

?>