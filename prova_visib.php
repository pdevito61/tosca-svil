<?php
/**
 * Define MyClass
 */
class MyClass
{
    public $public = 'Public';
    protected $_protected = 'Protected';
    private $private = 'Private';

    /*function printHello()
    {
        echo $this->public;
        echo $this->_protected;
        echo $this->private;
    }*/
}

/**
 * Define MyClass2
 */
class MyClass2 extends MyClass
{
    // We can redeclare the public and protected method, but not private
    protected $_protected = 'Protected2';

    public function printHello()
    {
		$this->_protected = 'nuovo valore';
        echo $this->public;
        echo $this->_protected;
        echo $this->private;
    }
}

$obj2 = new MyClass2();
echo $obj2->public; // Works
//echo $obj2->protected; // Fatal Error
echo $obj2->private; // Undefined
$obj2->printHello(); // Shows Public, Protected2, Undefined

?>
