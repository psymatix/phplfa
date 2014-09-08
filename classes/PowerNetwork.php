<?php

/*
 *Power Network Class
 * 
 */


class PowerNetwork {
    
     public $buses = array();
     public $lines = array();
     
     public $admittanceMatrix;
     public $slackBus;
     public $jacobianMatrix;
     public $voltageControlledBuses = array();
     public $initialV = 1.0;
     public $initialD = 0;
     public static $powerOrder = array();
     public static $solution = array();
     public static $dPdQM_network;
     public $lfStep;
     public $vAdjust;
     
     //benchmark variable
     
     public $bm = array();
     
   function __construct($params = array()) {
        $this->basemva = $params["basemva"] ? $params["basemva"] : null;           
    }
    
   
   function addBus(Bus $Bus){
       $this->buses[$Bus->number] = $Bus;
   }
    
   function addLine(Line $Line){
      // $l = array();
      // $l[$Line->from][$Line->to] = $Line;
     //  $this->lines[] = $l;
      $this->lines[$Line->from][$Line->to] = $Line;
       
   }
   
   public static function bus($n){
       //return the Bus labeled $n
       return $this->buses[$n];
   }
   
   function initializeNetwork(){
       
       // init to set buses in order and lines also, to fill in blanks and make sure bus ordering is numeric
       // also to check that all lines connect to a bus by checking that the keys exist
       // name buses
       
       #choose slack bus
       $this->slackBus = 1;
       
       // sort buses numerically in ascending order
       if(ksort($this->buses)){
           
         //first name buses to know which parameters are unknown   
        foreach($this->buses as $bus){
          $this->busUnknowns($bus); 
        }
       
        
        }
       //form admittance Matrix
       
       $this->formAdmittanceMatrix();
       
    
   }// initialize network
   
   function busUnknowns($b){
       // get bus known parameters and 
       // use binary grouping to divide them in a switch statement
       
       //1 means it is unknown according to spreadsheet
      
        $p = ($b->P && $b->P != 0) ? 1:0;
        $q = ($b->Q && $b->Q != 0) ? 1:0;
        $v = $b->voltagePU ? 1:0;
        $d = $b->voltageAngle ? 1:0;
        
        $typeBin = $p . $q . $v . $d;
        $typeDec = bindec($typeBin);
        
        $voltageControlledNums = array(10,11,14,15); 
        
        //
        
       // $voltageControlledNums = array(1,2,3,6,7,10,11,14,15); 
        
        
        $pUnknown = array(1,2,3,4,5,6,7);  if(in_array($typeDec, $pUnknown)){ $b->unknowns[] = "P"; }
        $qUnknown = array(1,2,3,8,9,10,11); if(in_array($typeDec, $qUnknown)){ $b->unknowns[] = "Q"; }
        $vUnknown = array(4,5,8,9,12,13); if(in_array($typeDec, $vUnknown)){ $b->unknowns[] = "V"; }
        $dUnknown = array(4,6,8,10,12,14); if(in_array($typeDec, $dUnknown)){ $b->unknowns[] = "D"; }
        
    
        if(in_array($typeDec, $voltageControlledNums) && ($b->number != $this->slackBus) && ($b->gen)){ $this->voltageControlledBuses[] = $b->number; }
                                                             // && ($b->gen) ----------^
       
        
        //add buses with reactive power control to VC buses
        
        if( ($b->qMax || $b->qMin) && !in_array($b->number, $this->voltageControlledBuses) ){
            $this->voltageControlledBuses[] = $b->number;   
        }
         
   }//bus unknowns
   
 
   
    function formAdmittanceMatrix(){
      $this->rmstart("admittanceMatrix");
       //for each bus, form an array with admittances to other buses if there is a connection
       // sort buses numerically to give some order to process
      
        $admittanceMatrixArray = array();
        
        
      if(ksort($this->buses)){
        
       foreach ($this->buses as $key1=>$value1){
           
                
           $yii = new Math_Complex(0,0); //self admittance object, reset on every "row"
           $yin = array(); //row array
           
           
           foreach($this->buses as $key2=>$value2){
            
                                //check if element exists in lines array
                                 if(isset($this->lines[$value1->number][$value2->number])){
                                      $yij = $this->lines[$value1->number][$value2->number]->admittance; 

                                 }elseif(isset($this->lines[$value2->number][$value1->number])){

                                     $yij = $this->lines[$value2->number][$value1->number]->admittance; 

                                 }else{
                                    $yij_obj = new Math_Complex(0,0);
                                    $yij = $yij_obj;
                                 }


                               // check if it isn't a self admittance,
                               // if not, add it up to self admittance number
                               // and invert 

                              if($value1->number !== $value2->number){
                                  $yii = Math_ComplexOp::add($yii, $yij); // add only row wise
                                  $yij = Math_ComplexOp::negative($yij);

                                 //add to row array which will eventually go into matrix object
                                 $yin[intval($value2->number)] = $yij;
                              }    



                           //   echo $value1->number . "-" .  $value2->number . " = " . $yij->toString() . ", ";   
                           // echo "row" . $key1 . " = " . $lines[$value1->number][$value2->number]->admittance->toString() . ", ";

                 }//level 2
                 
             //insert self admittance element in the right place at "column" i
              $yin[ intval($value1->number) ] = $yii;
              
             //sort this into the right order, add some error handling here
             // admittanceMatrixArray is not zero indexed
              
            if(ksort($yin)){ $admittanceMatrixArray[ intval($value1->number) ] = $yin; }
            
       }//level 1  
    
       // create matrix object here after looping has finished for all lines and buses
       
       $this->admittanceMatrix = $admittanceMatrixArray;      
       
    }// buses sorted
     $this->rmend("admittanceMatrix");
  }//formAdmittanceMatrix
  
  
  
   function busRealPower($Bi){
       
        #P = E|Vi||Vj||Yij|cos(0ij - Di + Dj) ; 0 = theta
        $Vi = $this->buses[$Bi]->voltagePU ? $this->buses[$Bi]->voltagePU : $this->initialV;
        $Di = $this->buses[$Bi]->voltageAngle ? $this->buses[$Bi]->voltageAngle : $this->initialD;
        
      
            #diagonal terms of J1, go through all buses before and after this one as Bj
             $P = 0;
       
            foreach($this->buses as $bus){
              
                   //get values from admittance Matrix of complex numbers
                   $Yij = $this->admittanceMatrix[$Bi][$bus->number]->abs();
                   $Tij = $this->admittanceMatrix[$Bi][$bus->number]->angle();
                   $Vj = $bus->voltagePU ? $bus->voltagePU : $this->initialV;
                   $Dj = $bus->voltageAngle ? $bus->voltageAngle : $this->initialD;
                   
                  $P += ($Vi * $Vj * $Yij * cos($Tij - $Di + $Dj));
              
            }
       
    
            return $P;
    }//real Power at Bus
  
  
    
    function busReactivePower($Bi){
       echo $Bi . " " . $this->lfStep . "<br>"; 
        #Q = -E|Vi||Vj||Yij|sin(0ij - Di + Dj) ; 0 = theta
        $Vi = $this->buses[$Bi]->voltagePU ? $this->buses[$Bi]->voltagePU : $this->initialV;
        $Di = $this->buses[$Bi]->voltageAngle ? $this->buses[$Bi]->voltageAngle : $this->initialD;
         
        $isSlack = $this->buses[$Bi]->number == $this->slackBus ? true : false;
      
            #diagonal terms of J1, go through all buses before and after this one as Bj
             $Q = 0;
       
            foreach($this->buses as $bus){
        
                   //get values from admittance Matrix of complex numbers
                   $Yij = $this->admittanceMatrix[$Bi][$bus->number]->abs();
                   $Tij = $this->admittanceMatrix[$Bi][$bus->number]->angle();
                   $Vj = $bus->voltagePU ? $bus->voltagePU : $this->initialV;
                   $Dj = $bus->voltageAngle ? $bus->voltageAngle : $this->initialD;
                   
                  $Q += ($Vi * $Vj * $Yij * sin($Tij - $Di + $Dj));
             
            }
           
            /*
        if(!$isSlack && in_array($Bi, $this->voltageControlledBuses) && ($this->lfStep == 1)){
            //specify a range which will be satisfied if qMin and qMax are not specified
            return $this->qRegulate($Bi, $Q);
        }else{
            //slackbus
            return -$Q;
        }
      */
              return -$Q;
          
    }//reactive Power at Bus
    
    function qRegulate($Bi, $Q){
         
        $qMax = $this->buses[$Bi]->qMax;
        $qMin = $this->buses[$Bi]->qMin;
        
        $vMax = $this->buses[$Bi]->vMax;
        $vMin = $this->buses[$Bi]->vMin;
        
        $result = Q;
         
            if($qMin === null && $qMax === null){
                //no values; no regulation 
              echo "nulls";
               $result = $Q;
               return $result;
            }
            
            $genQ = $Q + abs($this->buses[$Bi]->loadMVAR);
            
            if( ($qMin <=  $genQ) && ( $genQ <= $qMax)){
                //within limits 
                    echo "<br/><strong>within limits: " . $Bi . "Q" . $genQ . " qMin:" . $qMin .  " qMax:" . $qMax . " step:" . $this->lfStep . "</strong><br/>";
             $result = $Q;
             return $result;
            }
        
        
        
        
        if( $genQ > $qMax){
               //adjust voltage by a certain percentage for next iteration, for PV buses 
                // actually for buses with qMin and qMax
               //reduce voltage slightly within limits and steps
         echo "greater";
            while(($result + abs($this->buses[$Bi]->loadMVAR) > $qMax)  && ($this->buses[$Bi]->voltagePU > $this->buses[$Bi]->vMin) ){
                   
                        $this->buses[$Bi]->voltagePU = $this->buses[$Bi]->voltagePU * (1 - $this->vAdjust);
                        echo $Bi . 'new vPU ---->' . $this->buses[$Bi]->voltagePU . ' STEP:' . $this->lfStep .'<br>'; 
                        $result = $this->busReactivePower($Bi);
                   }
                
                //return $Q;
                 
              
           }elseif($genQ < $qMin){ 
                //too low so increase
                 echo "lesser";
               while(($result + abs($this->buses[$Bi]->loadMVAR) < $qMin) && ($this->buses[$Bi]->voltagePU > $this->buses[$Bi]->vMax) ){
                        
                        $this->buses[$Bi]->voltagePU = $this->buses[$Bi]->voltagePU * (1 + $this->vAdjust);
                        echo $Bi . 'new vPU ---->' . $this->buses[$Bi]->voltagePU . ' STEP:' . $this->lfStep . '<br>'; 
                        $result = $this->busReactivePower($Bi);
                  }
             
              //  return $Q;
              // return $qMin;
           }
          
           return $result;
           
    }// reactive power regulation 
    
    
    
    
 
    function delPDelQMatrix(){
        $this->rmstart("dPdQ");
        
        $dP = array(); $dQ = array(); 
        #array to be used to obtain solution for linear equations  
        
       //real power elements delP
        foreach($this->buses as $num=>$bus){
        if($num != $this->slackBus){
       
                //label each key readable and compute delP i.e. difference between scheduled power and actual power 
                //calculate P for all buses
                $k = "P" . $num; 
                $dP[] = floatval($bus->S->getReal() - $this->busRealPower($num));          
                $this->powerOrder[] = $k;
            }//not slack bus condition
        }//foreach
        
        //reactive power elements delQ
        foreach($this->buses as $num=>$bus){
        if($num != $this->slackBus){
        #add non voltage controlled buses and then voltage controlled buses to array
             if(!in_array($num, $this->voltageControlledBuses)){
                //label each key readable and compute delP i.e. difference between scheduled power and actual power 
                //load buses, so calculate P                
                $k = "Q" . $num; //unused key variable
                $dQ[] = floatval($bus->S->getIm() - $this->busReactivePower($num));
                $this->powerOrder[] = $k;
                    
                }
                
            }//not slack bus condition
        }//foreach
        
        
      $dPdQM = array_merge($dP, $dQ);
      $this->dPdQM_network = $dPdQM;
      $this->rmend("dPdQ");
      return new Math_Vector( $dPdQM );
        
    }
    
   function loadXMLNetwork($src){
       
       #benchmark start
       $this->rmstart("loadnetwork");
       
       $xml = new DOMDocument();
       $xml->load($src);
       
       //network params
       $networkParams = $xml->getElementsByTagName("PowerNetwork");
      
       $basemva = $networkParams->item(0)->getAttribute("basemva");
        $voltageUpperLimit = $networkParams->item(0)->getAttribute("voltageUpperLimit");
        $voltageLowerLimit = $networkParams->item(0)->getAttribute("voltageLowerLimit");
        $this->vAdjust = $networkParams->item(0)->getAttribute("voltageAdjustment");
        
       if($basemva && $basemva !== ""){
           $this->basemva = $basemva;
       }
       
       
       // add buses
       $xmlbuses = $xml->getElementsByTagName("Bus");
       
       foreach ($xmlbuses as $busData){
           //add buses to network
           
           $qMax = $busData->getAttribute("qMax") != "" ? $busData->getAttribute("qMax") : null;
           $qMin = $busData->getAttribute("qMin") != "" ? $busData->getAttribute("qMin") : null;
           
           //use PUQ basemva
          if($this->basemva){ $qMax = ($qMax / $this->basemva); $qMin = ($qMin / $this->basemva);}
           
           
            $this->addBus(new Bus( (int) $busData->getAttribute("number"), $busData->getAttribute("voltagePU"), $busData->getAttribute("vAngle"), $qMax, $qMin) );
            
            //set bus voltage limits to limit set in network
            $this->buses[ (int) $busData->getAttribute("number")]->vMax = $busData->getAttribute("voltagePU") * $voltageUpperLimit;
            $this->buses[ (int) $busData->getAttribute("number")]->vMin = $busData->getAttribute("voltagePU") * $voltageLowerLimit;
           
            //add element to buses
            $xmlelements = $busData->getElementsByTagName("Element");
            
              if($xmlelements->length > 0){
            
                  foreach ($xmlelements as $elementData){
                        $elemprop = array();
                        if($elementData->hasAttribute("p")){
                       $elemprop["P"] = $this->basemva ? ($elementData->getAttribute("p") / $this->basemva) : $elementData->getAttribute("p");
                        }elseif($elementData->hasAttribute("q")){
                       $elemprop["Q"] = $this->basemva ?  ($elementData->getAttribute("q") / $this->basemva) : $elementData->getAttribute("q");
                        }
                         $elemprop["flow"] = $elementData->getAttribute("flow");
                         $this->buses[$busData->getAttribute("number")]->addElement( new Element($elemprop) );
              }//foreach
                        
              }// if elements > 0
       }
       
       
       
       //add Lines
       
       $xml_lines = $xml->getElementsByTagName("Line");
       
        foreach($xml_lines as $lineData){
            $this->addLine( new Line( $lineData->getAttribute("from"), $lineData->getAttribute("to"), $lineData->getAttribute("zReal"), $lineData->getAttribute("zIm")) );
        }
       
       #benchmark start
       $this->rmend("loadnetwork");
    
   }
   
   function rmstart($f){
       $this->bm[$f . "_starttime"] = -microtime(true); 
       $this->bm[$f . "_startmem"] = -memory_get_usage();
      
   }
   
   function rmend($f){
       $this->bm[$f . "_exectime"] =  $this->bm[$f . "_starttime"] += microtime(true); 
       $this->bm[$f . "_execmem"] = $this->bm[$f . "_startmem"] += memory_get_usage(); 
       
       echo $f . " Exec time: " . $this->bm[$f . "_exectime"] . "<br />";
       echo $f . " Exec Memory: " .  ($this->bm[$f . "_execmem"]/1024) . "<br /><br />";
      
   }
    
}// Network Class


?>
