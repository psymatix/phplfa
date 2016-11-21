<?php

/*
 * Bus Class
 *
 */

class Bus
{
    //defines buses

    //derived parameters

    public $P = null;
    public $Q = null;
    public $S = null; //set power to 0 initially
    public $type = null;
    public $elements = array();
    public $unknowns = array();
    public $previousV;
    public $previousD;
    public $previousP;
    public $previousQ;
    public $vMax;
    public $vMin;
    public $gen;
    public $genMVAR = 0;
    public $genMW = 0;
    public $loadMVAR = 0;
    public $loadMW = 0;

    public function __construct($number, $voltagePU = null, $vAngle = null, $qMax = null, $qMin = null)
    {
        //   all parameters can be null if not specified
       //   bus power is a function of the elements connected to it -  loads or generators
       $vAngle = $vAngle ? (float) $vAngle : null;

        $this->number = intval($number);
        $this->voltagePU = $voltagePU;
        $this->voltageAngle = $vAngle;
        $this->qMax = $qMax;
        $this->qMin = $qMin;
       //set voltage in complex notation
       $this->voltage = new Math_Complex($voltagePU * cos($vAngle), $voltagePU * sin($vAngle));
        $this->S = new Math_Complex(0, 0);

        return $this;
    }

    public function addElement(Element $element)
    {

        // add a new element to the bus
         $this->elements[] = $element;

        if ($element->flow == 'in' && $element->P !== null) {
            $this->gen = true;
            $this->genMW += $element->P;
        }

        if ($element->flow == 'in' && $element->Q !== null) {
            $this->genMVAR += $element->Q;
        }

        if ($element->flow == 'out' && $element->Q !== null) {
            $this->loadMVAR += $element->Q;
        }

        if ($element->flow == 'out' && $element->P !== null) {
            $this->loadMW += $element->P;
        }

        //separate gen power from bus power

         //update parameters based on this new element
         // if element power is null then it has no effect on the Bus apparent power
         // maybe a function should check if

         if ($this->S && $element->S) {
             $this->S = Math_ComplexOp::add($this->S, $element->S);
         } elseif (!$this->S && $element->S) {
             $this->S = $element->S;
         }

        $this->P = $this->S->getReal();
        $this->Q = $this->S->getIm();
    }// add element
}//Bus Object
