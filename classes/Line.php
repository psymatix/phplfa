<?php


/**
 * Description of Line.
 *
 * @author abjb667
 */
class Line
{
    public function __construct($from = null, $to = null, $zReal = null, $zIm = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->impedance = ($zReal !== null && $zIm !== null) ? new Math_Complex($zReal, $zIm) : null;
        $this->admittance = $this->impedance ? Math_ComplexOp::inverse($this->impedance) : null;
        $this->label = ($from !== null && $to !== null) ? (string) $from.(string) $to : null;
        $this->bm = array();

        return $this;
    }

    public function LineFlowCurrent($Vi, $Vj)
    {
        $this->rmstart('lineflow');
       //stores the current as a property of this line
        $vdiff = Math_ComplexOp::sub($Vi, $Vj);

        //currents
        $this->Iij = Math_ComplexOp::mult($this->admittance, $vdiff);
        $this->Iji = Math_ComplexOp::negative($this->Iij);

        //flows
        $this->Sij = Math_ComplexOp::mult($Vi, Math_ComplexOp::conjugate($this->Iij));
        $this->Sji = Math_ComplexOp::mult($Vj, Math_ComplexOp::conjugate($this->Iji));

        //losses
        $this->SLij = Math_ComplexOp::add($this->Sij, $this->Sji);
        $this->rmend('lineflow');
    }

    //repeated benchmar functions

   public function rmstart($f)
   {
       $this->bm[$f.'_starttime'] = -microtime(true);
       $this->bm[$f.'_startmem'] = -memory_get_usage();
   }

    public function rmend($f)
    {
        $this->bm[$f.'_exectime'] = $this->bm[$f.'_starttime'] += microtime(true);
        $this->bm[$f.'_execmem'] = $this->bm[$f.'_startmem'] += memory_get_usage();

        echo $f.' Exec time: '.$this->bm[$f.'_exectime'].'<br />';
        echo $f.' Exec Memory: '.($this->bm[$f.'_execmem'] / 1024).'<br /><br />';
    }
}// line object
;
