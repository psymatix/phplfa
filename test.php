<?php
//complex test

require_once("Math/Complex.php");
require_once("Math/ComplexOp.php");

$c = new Math_Complex(4,3);
$c2 = new Math_Complex(2,1);
print_r($c);
echo "<br>";
echo $c->abs();
echo "<br>";

$csum = Math_ComplexOp::add($c,$c2);

print_r($csum);
echo "<br>";

echo $csum->toString();
?>