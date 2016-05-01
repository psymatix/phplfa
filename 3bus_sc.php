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


$zBus = array();
$zBus[1] = array();
    $zBus[1][1] = new Math_Complex(0, 0.16); 
    $zBus[1][2] = new Math_Complex(0, 0.08); 
    $zBus[1][3] = new Math_Complex(0, 0.12);
$zBus[2] = array();
    $zBus[2][1] = new Math_Complex(0, 0.08); 
    $zBus[2][2] = new Math_Complex(0, 0.24); 
    $zBus[2][3] = new Math_Complex(0, 0.16);
$zBus[3] = array();
    $zBus[3][1] = new Math_Complex(0, 0.12); 
    $zBus[3][2] = new Math_Complex(0, 0.16); 
    $zBus[3][3] = new Math_Complex(0, 0.34);

$time = -microtime(true);
$mem = -memory_get_usage();

$p = new PowerNetwork();

$p->loadXMLNetwork("xmlnetworks/3bus_sc.xml");

$p->initializeNetwork();

//impedance matrix
$p->formImpedanceMatrix($zBus);

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

var_dump($p->admittanceMatrix);
//var_dump($p->buses);
//var_dump($p->impedanceMatrix);



?>
