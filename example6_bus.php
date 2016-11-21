<?php

require_once("Math/Matrix.php");
require_once("Math/Complex.php");
require_once("Math/ComplexOp.php");

//Autoloader

function PSAutoloader($class){
    include 'classes/' .  $class . '.php';
}

spl_autoload_register('PSAutoloader');
//initialization procedures

$time = -microtime(true);
$mem = -memory_get_usage();

$p = new PowerNetwork();

$p->loadXMLNetwork("xmlnetworks/example6_Bus.xml");


$p->initializeNetwork();

lfNR::solve($p, 2.5E-4, 12);
displayFn::printNetwork($p);
displayFn::printNetworkTable($p);
displayFn::showLineCurrents($p);

$time += microtime(true);
$mem += memory_get_usage();
$memkb = $mem/1024;

echo "<br><hr>";
echo "completed in $time seconds";
echo "<br>";
echo "total memory allocated: $memkb";


?>
