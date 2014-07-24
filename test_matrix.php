<?php

/*
 * 
 */

require_once 'Math/Matrix.php';
//require_once 'Math/Vector.php';


$delPdelQ = new Math_matrix( array( 
    
                            array((float) -2.8600),
                            array((float) 1.4384),
                            array((float) -0.2200)
                            
                            ));

$delPdelQVector = new Math_Vector( array((float) -2.8600, (float) 1.4384, (float) -0.2200 ) );


$j = new Math_Matrix ( array(
                    
        array(54.28000, -33.28000, 24.86000),
        array(-33.28000, 66.04000, -16.64000),
        array(-27.14000, 16.64000, 49.72000)       
        
                       ));

$soln = Math_Matrix::solve($j, $delPdelQVector);
print_r($soln->_tuple->getData());

#print_r($soln->getData());

function jacobianMatrix($n){
    //formed from network
    
       
    
}


function busAdmittanceMatrix($n){
    // formed from lines in a network
    
}




##################################




 
        foreach($p->buses as $bus){ 
     
                if($bus->number != $p->slackBus){
                  echo "Bus Number:" .  $bus->number . "<br>";
                  print_r("V:" . $bus->voltagePU . "<br>"); 
                  print_r("D:" . $bus->voltageAngle . "<br>"); 
                  print_r("P:" . $bus->S->getReal() . "<br>"); 
                  print_r("Q:" . $bus->S->getIm() . "<br>"); 
                  print_r($p->dPdQM_network);
                  echo "<br>";

                  //test for power mismatch and then break if within limits
                  //first make all values absolute, using a custom function or a loop

                  $absdPdQ = array_map("lfNR::absoluteArray", $p->dPdQM_network);
                  print_r($absdPdQ);
                  echo "<br>";
                  $maxmismatch = max($absdPdQ);
                  if($maxmismatch <= 2.5E-4){ break 2; } 

                } //iterate through buses  
  
 }// number of iterations




?>
