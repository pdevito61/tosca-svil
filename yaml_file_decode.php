<?php

$filename = "cloudify-types";
$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/".$filename.".yml";

$parsed = yaml_parse_file($yaml);

print_r($parsed);

//$reparsed_yaml = yaml_emit($parsed);
//print_r($reparsed_yaml);

$yaml = "C:\DATI_PERSONALI\TILAB\OSS Evolution\TOSCA_php/".$filename."_reconverted.yml";
if( yaml_emit_file($yaml, $parsed) ) echo "TUTTO OK";



?>