<?php



class JacobianMatrix {
    
    public $elements = array();
    private $network;
    
    
    function __construct() {
      
    }
    
    function delPdelD($Bi,$Bj){
        #J1
        #$dPdD = E(j!=i)|Vi||Vj||Yij|sin(0ij - Di + Dj) ; 0 = theta
        $Vi = $this->network->buses[$Bi]->voltagePU ? $this->network->buses[$Bi]->voltagePU : $this->initialV;
        $Di = $this->network->buses[$Bi]->voltageAngle ? $this->network->buses[$Bi]->voltageAngle : $this->initialD;
        
         if($Bi === $Bj){
            #diagonal terms of J1, go through all buses before and after this one as Bj
             $dPdD = 0;
       
            foreach($this->network->buses as $bus){
               if($bus->number != $Bi){
                   //get values from admittance Matrix of complex numbers
                   $Yij = $this->network->admittanceMatrix[$Bi][$bus->number]->abs();
                   $Tij = $this->network->admittanceMatrix[$Bi][$bus->number]->angle();
                   $Vj = $bus->voltagePU ? $bus->voltagePU : $this->initialV;
                   $Dj = $bus->voltageAngle ? $bus->voltageAngle : $this->initialD;
                   
                  $dPdD += ($Vi * $Vj * $Yij * sin($Tij - $Di + $Dj));
               }
            }
       
        }else{
           #OffDiagonal Terms
            
           //get values from admittance Matrix of complex numbers
                   $Yij = $this->network->admittanceMatrix[$Bi][$Bj]->abs(); 
                   $Tij = $this->network->admittanceMatrix[$Bi][$Bj]->angle();
                   $Vj = $this->network->buses[$Bj]->voltagePU ? $this->network->buses[$Bj]->voltagePU : $this->initialV;
                   $Dj = $this->network->buses[$Bj]->voltageAngle ? $this->network->buses[$Bj]->voltageAngle : $this->initialD;
                   
                  $dPdD = -($Vi * $Vj * $Yij * sin($Tij - $Di + $Dj));  
            
            
        }
       
        return $dPdD;
    }//delPdelD
    
    
    
    function delPdelV($Bi,$Bj){
        #J2
        
        $Vi = $this->network->buses[$Bi]->voltagePU ? $this->network->buses[$Bi]->voltagePU : $this->initialV;
        $Di = $this->network->buses[$Bi]->voltageAngle ? $this->network->buses[$Bi]->voltageAngle : $this->initialD;
        
         if($Bi === $Bj){
            #diagonal terms of J2, go through all buses before and after this one as Bj
             $dPdV_term2 = 0;
             $Yii = $this->network->admittanceMatrix[$Bi][$Bi]->abs();
             $Tii = $this->network->admittanceMatrix[$Bi][$Bi]->angle();
       
            foreach($this->network->buses as $bus){
               if($bus->number != $Bi){
                   //get values from admittance Matrix of complex numbers
                   $Yij = $this->network->admittanceMatrix[$Bi][$bus->number]->abs();
                   $Tij = $this->network->admittanceMatrix[$Bi][$bus->number]->angle();
                   $Vj = $bus->voltagePU ? $bus->voltagePU : $this->initialV;
                   $Dj = $bus->voltageAngle ? $bus->voltageAngle : $this->initialD;
                   
                  $dPdV_term2 += ($Vj * $Yij * cos($Tij - $Di + $Dj));
               }
            }
            
            $dPdV = (2 * $Vi * $Yii * cos($Tii)) + $dPdV_term2;
       
        }else{
           #OffDiagonal Terms
            
           //get values from admittance Matrix of complex numbers
                   $Yij = $this->network->admittanceMatrix[$Bi][$Bj]->abs(); 
                   $Tij = $this->network->admittanceMatrix[$Bi][$Bj]->angle();
                   $Vj = $this->network->buses[$Bj]->voltagePU ? $this->network->buses[$Bj]->voltagePU : $this->initialV;
                   $Dj = $this->network->buses[$Bj]->voltageAngle ? $this->network->buses[$Bj]->voltageAngle : $this->initialD;
                   
                  $dPdV = ($Vi * $Yij * cos($Tij - $Di + $Dj));  
            
            
        }
       
        return $dPdV;
    }//delPdelV
    
   
    
    
     function delQdelD($Bi,$Bj){
        #J3
        
        $Vi = $this->network->buses[$Bi]->voltagePU ? $this->network->buses[$Bi]->voltagePU : $this->initialV;
        $Di = $this->network->buses[$Bi]->voltageAngle ? $this->network->buses[$Bi]->voltageAngle : $this->initialD;
        
         if($Bi === $Bj){
            #diagonal terms of J1, go through all buses before and after this one as Bj
             $dQdD = 0;
       
            foreach($this->network->buses as $bus){
               if($bus->number != $Bi){
                   //get values from admittance Matrix of complex numbers
                   $Yij = $this->network->admittanceMatrix[$Bi][$bus->number]->abs(); 
                   $Tij = $this->network->admittanceMatrix[$Bi][$bus->number]->angle(); 
                   $Vj = $bus->voltagePU ? $bus->voltagePU : $this->initialV; 
                   $Dj = $bus->voltageAngle ? $bus->voltageAngle : $this->initialD; 
                   
                  $dQdD += ($Vi * $Vj * $Yij * cos($Tij - $Di + $Dj));
               }
            }
       
        }else{
           #OffDiagonal Terms
            
           //get values from admittance Matrix of complex numbers
                   $Yij = $this->network->admittanceMatrix[$Bi][$Bj]->abs(); 
                   $Tij = $this->network->admittanceMatrix[$Bi][$Bj]->angle();
                   $Vj = $this->network->buses[$Bj]->voltagePU ? $this->network->buses[$Bj]->voltagePU : $this->initialV;
                   $Dj = $this->network->buses[$Bj]->voltageAngle ? $this->network->buses[$Bj]->voltageAngle : $this->initialD;
                   
                  $dQdD = -($Vi * $Vj * $Yij * cos($Tij - $Di + $Dj));  
            
            
        }
       
        return $dQdD;
    }//delQdelD
    
    
  
       
    function delQdelV($Bi,$Bj){
        #J4
        
        $Vi = $this->network->buses[$Bi]->voltagePU ? $this->network->buses[$Bi]->voltagePU : $this->initialV;
        $Di = $this->network->buses[$Bi]->voltageAngle ? $this->network->buses[$Bi]->voltageAngle : $this->initialD;
        
         if($Bi === $Bj){
            #diagonal terms of J2, go through all buses before and after this one as Bj
             $dQdV_term2 = 0;
             $Yii = $this->network->admittanceMatrix[$Bi][$Bi]->abs();
             $Tii = $this->network->admittanceMatrix[$Bi][$Bi]->angle();
       
            foreach($this->network->buses as $bus){
               if($bus->number != $Bi){
                   //get values from admittance Matrix of complex numbers
                   $Yij = $this->network->admittanceMatrix[$Bi][$bus->number]->abs();
                   $Tij = $this->network->admittanceMatrix[$Bi][$bus->number]->angle();
                   $Vj = $bus->voltagePU ? $bus->voltagePU : $this->initialV;
                   $Dj = $bus->voltageAngle ? $bus->voltageAngle : $this->initialD;
                   
                  $dQdV_term2 += ($Vj * $Yij * sin($Tij - $Di + $Dj));
               }
            }
            
            $dQdV = -(2 * $Vi * $Yii * sin($Tii)) - $dQdV_term2;
       
        }else{
           #OffDiagonal Terms
            
           //get values from admittance Matrix of complex numbers
                   $Yij = $this->network->admittanceMatrix[$Bi][$Bj]->abs(); 
                   $Tij = $this->network->admittanceMatrix[$Bi][$Bj]->angle();
                   $Vj = $this->network->buses[$Bj]->voltagePU ? $this->network->buses[$Bj]->voltagePU : $this->initialV;
                   $Dj = $this->network->buses[$Bj]->voltageAngle ? $this->network->buses[$Bj]->voltageAngle : $this->initialD;
                   
                  $dQdV = -($Vi * $Yij * sin($Tij - $Di + $Dj));  
            
            
        }
       
        return $dQdV;
    }//delQdelV
    
   
    public function formMatrix($network){
        $network->rmstart("jacobian");
        
        $this->network =& $network;
        $this->initialV = $this->network->initialV;
        $this->initialD = $this->network->initialD;     
        
        # form each segment matrix and combine at the end
        
        $n = count($this->network->buses); 
        $m = count($this->network->voltageControlledBuses);
        
        #delPdelD
        #$J1 (n-1) * (n-1) - minus 1 because of slack bus
        $J1 = array();
        for($i = 2; $i <= $n ; $i++){
            
            for($j = 2; $j <= $n; $j++){
                $J1[$i][$j] = $this->delPdelD($i, $j);    
            }#columns loop
            
        }// rows loop
       
        
        #delPdelV
        #$J2 (n-1) x (n-1-m)
        $J2 = array();
         for($i = 2; $i <= $n ; $i++){
            
            for($j = 2; $j <= $n; $j++){
                if(in_array($j, $this->network->voltageControlledBuses)){continue;}#looking out for V
                
                $J2[$i][$j] = $this->delPdelV($i, $j);    
            }#columns loop
            
        }// rows loop
        
   
        #delQdelD
        #$J3 (n-1-m) x (n-1)
        
         $J3 = array();
         for($i = 2; $i <= $n ; $i++){
           if(in_array($i, $this->network->voltageControlledBuses)){continue;}#looking out for Q
            
           for($j = 2; $j <= $n; $j++){
                $J3[$i][$j] = $this->delQdelD($i, $j);    
            }#columns loop
            
        }// rows loop
        
        

        #delQdelV
        #$J4 (n-1-m) x (n-1-m)
        
         $J4 = array();
         for($i = 2; $i <= $n ; $i++){
           if(in_array($i, $this->network->voltageControlledBuses)){continue;}#looking out for Q
            
           for($j = 2; $j <= $n; $j++){
           if(in_array($j, $this->network->voltageControlledBuses)){continue;}#looking out for V
                $J4[$i][$j] = $this->delQdelV($i, $j);    
            }#columns loop
            
        }// rows loop
        
        //join arrays by leading index recursively
       
       $J = array();
       # join J1 to J2
       foreach($J1 as $key=>$value){
           $J[] = array_merge($value, $J2[$key]);           
       }
       
       #join J3 to J4
       
       foreach($J3 as $key=>$value){
           $J[] = array_merge($value, $J4[$key]);
       }
   
       
       
      // make matrix object and return it
      $JM = new Math_Matrix($J);
       $network->rmend("jacobian");
      return $JM;
        
    }
    
    
    
    
}//jacobian class


?>
