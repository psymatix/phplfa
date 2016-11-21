<?php
/**
 * Description of shortCkt.
 *
 * @author Simon
 */
class shortCkt
{
    //put your code here

 public function __construct($e, &$network, $maxIterations = 0)
 {
     $this->maxIterations = $maxIterations;
     $this->e = $e;
     $this->network = &$network;
 }

    public static function faultCurrent(&$network, &$bus, $Zf)
    {

        //If = (Vpu / (Zkk + Zf))
        $k = $bus->number;
        $Zkk = $network->impedanceMatrix[$k][$k];
        $Zeqv = Math_ComplexOp::inverse(Math_ComplexOp::add($Zkk, $Zf));

        $If = Math_ComplexOp_mult($bus->voltagePU, $Zeqv);

        return $If;
    }
}
