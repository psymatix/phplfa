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
printNetwork($p); 

$time += microtime(true);
$mem += memory_get_usage();
$memkb = $mem/1024;

echo "<br><hr>";
echo "completed in $time seconds";
echo "<br>";
echo "total memory allocated: $memkb";

function printNetwork($n){
    
        
     foreach($n->buses as $bus){ 
     
                
                  echo "Bus Number:" .  $bus->number . "<br>";
                  print_r("V:" . $bus->voltagePU . "<br>"); 
                  print_r("D:" . $bus->voltageAngle . "<br>"); 
                  if($bus->S){
                  print_r("P:" . $bus->S->getReal() . "<br>"); 
                  print_r("Q:" . $bus->S->getIm() . "<br>"); 
                  }
                  echo "<br><hr>";
                 
                  

  
 }
 
 echo "converged after " . lfNR::$step . " steps <br><hr>";
 print_r($n->voltageControlledBuses);
 
}

?>
