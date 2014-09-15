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
    
    function LineFlowCurrent($Vi, $Vj){
       //stores the current as a property of this line
        $vdiff = Math_ComplexOp::sub($Vi, $Vj);
        
        //currents
        $this->Iij = Math_ComplexOp::mult($this->admittance,$vdiff);
        $this->Iji = Math_ComplexOp::negative($this->Iij);
        
        //flows
        $this->Sij = Math_ComplexOp::mult($Vi, Math_ComplexOp::conjugate($this->Iij));
        $this->Sji = Math_ComplexOp::mult($Vj, Math_ComplexOp::conjugate($this->Iji));
        
        //losses
        $this->SLij = Math_ComplexOp::add($this->Sij, $this->Sji);
        
    }
   
    
    
}// line object

?>
