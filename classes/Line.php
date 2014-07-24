<?php


/**
 * Description of Line
 *
 * @author abjb667
 */



class Line {
    
    function __construct($from = null, $to = null, $zReal = null, $zIm = null) {
        $this->from = $from;
        $this->to = $to;
        $this->impedance = ($zReal !== NULL && $zIm !== NULL) ? new Math_Complex($zReal, $zIm) : NULL;
        $this->admittance = $this->impedance ? Math_ComplexOp::inverse($this->impedance) : NULL;
            
        $this->label = ($from !== NULL && $to !== NULL) ? (string)$from . (string)$to : null;
        
        return $this;
    }
    
    
   
    
}// line object

?>
