<?php


class lfNR {
    
    public static $step = 0;
  
    
    function __construct( $e, &$network, $maxIterations = 0){
        $this->maxIterations = $maxIterations;
        $this->e = $e;
        $this->network = &$network;
        
    }
    
    public static function exec(&$network){
        // check if network is initialized first
        
          $J = new JacobianMatrix;
          $JM = $J->formMatrix($network);
          
          //form deltaPdeltaQ vector
          $dPdQ = $network->delPDelQMatrix();
        //  echo "DPDQ(" . self::$step . "): ";  print_r($dPdQ); echo "<br>"; 
          $stepSolution = Math_Matrix::solve($JM, $dPdQ);
          $network->solution[] = $stepSolution;
          self::$step++;
          $network->lfStep = self::$step;
          
          return $stepSolution;
    }
    
   public static function updateNetwork(&$network, $stepSolution){
        
          $solnArray = $stepSolution->_tuple->getData();
        
        # degrees in radians, voltage in P.U
          
         foreach($network->powerOrder as $key=>$np){
             (int)$busNum = substr($np, 1);
             $updateGuide = substr($np, 0, 1);
                     
             //if P update Delta, if Q update V;
             //solution follows the same order as power order;
            
             if($updateGuide === "P"){
                 //store previous value
                $network->buses[$busNum]->previousD = $network->buses[$busNum]->voltageAngle ? $network->buses[$busNum]->voltageAngle : $network->initialD;
                //update value
                $network->buses[$busNum]->voltageAngle = $network->buses[$busNum]->previousD + $solnArray[$key];
               
                                
             }elseif($updateGuide === "Q"){
                
                  //store previous value
                 $network->buses[$busNum]->previousV = $network->buses[$busNum]->voltagePU ? $network->buses[$busNum]->voltagePU : $network->initialV;
                 //update Value using delta
                 $network->buses[$busNum]->voltagePU = $network->buses[$busNum]->previousV + $solnArray[$key];
                              
             }
             
              //store updated voltage as complex number
              $vAngle = $network->buses[$busNum]->voltageAngle;
              $voltagePU = $network->buses[$busNum]->voltagePU;
                 
              $network->buses[$busNum]->voltage = new Math_Complex( $voltagePU * cos($vAngle), $voltagePU * sin($vAngle) );
            
             
         }//iterate through powerOrder for elements to be updated
       
       
   }//update Function
    
   
   public static function absoluteArray($val){
       return abs($val);
   }//absolutearray
    
   
   public static function solve(&$network, $epsilon, $maxiter){
       //iteration function
       
       for($i =0; $i < $maxiter; $i++){
           $soln = self::exec($network);
           //update values
           self::updateNetwork($network,$soln);
           $absdPdQ = array_map("lfNR::absoluteArray", $network->dPdQM_network);
          
          //  $maxmismatch = max($absdPdQ);
           if(max($absdPdQ) <= $epsilon){ break;  } 
       }
       
       # after break, solve for other unknowns in voltage controlled buses and slack bus params
       
       #consider another method of choosing buses with unknown power - rather than Voltage Controlled Buses
       
       
       foreach($network->voltageControlledBuses as $key=>$busNum){
           
            $network->buses[$busNum]->Q = $network->busReactivePower($busNum);
            $network->buses[$busNum]->P = ( $network->buses[$busNum]->P && ($network->buses[$busNum]->P > 0) ) ? $network->buses[$busNum]->P : $network->busRealPower($busNum); 
            $network->buses[$busNum]->S = new Math_Complex( $network->buses[$busNum]->P , $network->buses[$busNum]->Q);
            
       }
       
       #solve for slack bus power and voltage
       $SB = $network->buses[$network->slackBus]; 
       $SB->P = $network->busRealPower($network->slackBus);
       $SB->Q = $network->busReactivePower($network->slackBus);
       $SB->S = new Math_Complex( $SB->P , $SB->Q);
       
   }//solve
   
}//lfNR class


?>
