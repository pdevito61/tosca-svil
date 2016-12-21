<?php
require('tosca_builder.php');

header('Content-Type: application/json');

// parts of a template (entities)
// _TEMPLATE
// _TOPOLOGY
	// _EXPORT_MAPPING
	// _INPUT
	// _NODE
		// _REQUIREMENT
			// _REQ_FILTER
		// _CAPABILITY
		// _ARTIFACT
		// _NODE_IF
			// _NODE_IF_OP
	// _GROUP
		// _GROUP_IF
			// _GROUP_IF_OP
	// _OUTPUT

$TB = new tosca_builder();

$TB->modify(_TEMPLATE, $TB->path(), 'tosca_definitions_version', 'tosca_simple_profile_for_nfv_1_0_0');
$TB->modify(_TEMPLATE, $TB->path(), 'description', 'Example of service template for tosca builder');
$TB->modify(_TEMPLATE, $TB->path(), 'metadata', ['ID' => 'ID0001', 'vendor' => 'Telecom Italia', 'version' => 'version 1.0']);
$TB->modify(_TEMPLATE, $TB->path(), 'imports', ['TOSCA_nfv_definition_1_0' => "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/TOSCA_nfv_definition_1_0.yml"]);

$TB->create(_TOPOLOGY, $TB->path());
$TB->create(_INPUT, $TB->path(), 'INP-001', 'integer');
$TB->create(_NODE, $TB->path(), 'VDU1', 'tosca.nodes.nfv.VDU');
$TB->create(_OUTPUT, $TB->path(), 'OUT-001', 'integer');
$TB->create(_NODE_IF, $TB->node('VDU1')->path(), 'Standard', 'tosca.interfaces.node.lifecycle.Standard');
$TB->create(_NODE_IF_OP, $TB->node('VDU1')->node_interface('Standard')->path(), 'create');
$TB->create(_NODE_IF_OP, $TB->node('VDU1')->node_interface('Standard')->path(), 'configure');

// create($part, $path, $name = _NONAME, $type = _NOTYPE)

$TB->modify(_TOPOLOGY, $TB->path(), 'description', 'Example of topology template for tosca builder');
$TB->modify(_NODE, $TB->path(), 'description', 'Example of node template for tosca builder', 'VDU1');
$TB->modify(_NODE, $TB->path(), 'properties', ['component_version' => 'version 0.1', 'admin_credential' => 'my credential'], 'VDU1');
$TB->modify(_NODE, $TB->path(), 'attributes', ['tosca_id' => '0003', 'tosca_name' => 'vdu'], 'VDU1');

$TB->modify(_NODE_IF, $TB->node('VDU1')->path(), 'inputs', ['input1' => '45', 'input2' => '65'], 'Standard');
$TB->modify(_NODE_IF_OP, $TB->node('VDU1')->node_interface('Standard')->path(),'description', 'Example of operation in create', 'create');
$TB->modify(_NODE_IF_OP, $TB->node('VDU1')->node_interface('Standard')->path(),'implementation', 'script_create.sh', 'create');
$TB->modify(_NODE_IF_OP, $TB->node('VDU1')->node_interface('Standard')->path(),'description', 'Example of operation in configure', 'configure');
$TB->modify(_NODE_IF_OP, $TB->node('VDU1')->node_interface('Standard')->path(),'inputs', ['input1' => '45', 'input2' => '65'], 'configure');
$TB->modify(_NODE_IF_OP, $TB->node('VDU1')->node_interface('Standard')->path(),'implementation', 'configuration.sh', 'configure');

// modify($part, $path,  $attribute, $value, $name = _NONAME)

if ($TB->error_status()) {
	echo "found error! ".$TB->error()->getMessage()."\n\n";
	$TB->reset_error();
}


echo "\n\n".$TB->get();
// print_r($TB);

$ptb = new tosca_builder($TB->get());

echo "\n\n".$ptb->get();
// print_r($ptb);

?>