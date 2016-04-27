<?php
class my_root {
	protected $_structure = array();
	public function get(){ return $this->_structure;
	}
}

class my_derived extends my_root {
	
	public static function equal($value) {
		$prp = array('equal' => $value);
		return $prp;
	}
	
	public function funz($var) {
		$this->_structure['paolo'] = $var;
		return $this->_structure['paolo'];
	}
}

$pp = new my_derived();
$pp->funz('de vito piscicelli');
print_r($pp->get());

print_r($pp->equal('pinco'));


?>