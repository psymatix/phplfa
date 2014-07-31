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

$p = new PowerNetwork();

//eventually this will be populated using database or XML 
//add busses ($number, $voltagePU, $vAngle, $P, $Q, $type = null, $elements = array())
$p->addBus(new Bus(1, 1.05, 0));
$p->addBus(new Bus(2));
$p->addBus(new Bus(3, 1.04));

// add lines ($from = null, $to = null, $zReal = null, $zIm = null)
$p->addLine( new Line(1,2,0.02,0.04) );
$p->addLine( new Line(1,3,0.01,0.03) );
$p->addLine( new Line(2,3,0.0125,0.025) );


//add elements to buses ($P = null, $Q = null, $flow = null, $name = null)
$p->buses[2]->addElement( new Element( array("P"=>4, "flow"=>"out")) );
$p->buses[2]->addElement( new Element( array("Q"=>2.5, "flow"=>"out")) );
$p->buses[3]->addElement( new Element( array("P"=>2.0, "flow"=>"in")) );


$p->initializeNetwork();

lfNR::solve($p, 2.5E-4, 10);

printNetwork($p); 

$time += microtime(true);
echo "<br><hr>";
echo "completed in $time seconds";


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
