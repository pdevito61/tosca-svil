<?php


$yaml = <<<EOD
tosca.nodes.Root:
  description: 
    The TOSCA root node all other TOSCA base node types derive from.
  attributes:
    tosca_id:
      type: string
    tosca_name:
      type: string
    state:
      type: string
  capabilities:
      feature:
        type: tosca.capabilities.Node
  requirements:
    - dependency:
        capability: tosca.capabilities.Node
        node: tosca.nodes.Root
        relationship: tosca.relationships.DependsOn
        occurrences: [ 0, UNBOUNDED ]
  interfaces:
    Standard:
      type: tosca.interfaces.node.lifecycle.Standard

EOD;

$parsed = yaml_parse($yaml);
//var_dump($parsed);
//print_r($parsed);

$reparsed_yaml = yaml_emit($parsed);

print_r($reparsed_yaml);



?>