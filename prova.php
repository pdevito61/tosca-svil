<?php
$array = array(
    'fruit1' => 'apple',
    'fruit2' =>  array('fruit10'=>'banana','fruit11'=>'ananas','fruit12'=>'peach'),
    'fruit3' => 'grape',
    'fruit4' => 'apple',
    'fruit5' => 'apple');

	
print_r(array_key_exists('fruit3',$array)); 

print_r(array_key_exists('fruit12',$array['fruit2']));
echo "Tutto OK";
?>