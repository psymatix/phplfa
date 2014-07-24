<?php



class Element {
    
    //generic element class
    
    public $S; //apparent power
    
    function __construct( $args = array("flow" => null,"P" => null, "Q" => null, "name" => null) ){
            $this->P = $args["P"];
            $this->Q = $args["Q"];
            $this->flow = $args["flow"]; // flow for loads is "out" and for generators "in"; option exists to 
            $this->name = $args["name"];

            if($this->P && $this->Q){
                $this->S = new Math_Complex($this->P,$this->Q);
            }elseif($this->P && !$this->Q){
                $this->S = new Math_Complex($this->P,0);
            }elseif(!$this->P && $this->Q){
                $this->S = new Math_Complex(0,$this->Q);
            }else{
                $this->S = null;
            }
            
           if($this->flow == "out" && $this->S){ $this->S = Math_ComplexOp::negative($this->S);  }
       
       }
    
    
}

?>
