<?php
global $_mem_allocation; 
$_mem_allocation = round(memory_get_usage()/1024);

function mem_usage($msg) {
	global $_mem_allocation;
	$mem = round(memory_get_usage()/1024);
	$delta = $mem - $_mem_allocation;
	echo __FUNCTION__ . ": << " . $msg . " >> Total memory: " . $mem . " KByte, Delta memory: " . $delta . " KByte\n";
	$_mem_allocation = $mem;
}

?>