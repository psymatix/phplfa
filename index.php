<?php

/* 
 * Sample Load Flow Application to calculate Voltage iteratively
 * Using GS Method
 * 
 */

/*includes*/

require_once("Math/Complex.php");
require_once("Math/ComplexOp.php");

#define network
#in main one, use array for each parameter on each bus - or bus data object 
#so that the iteration can be generalized
#use post to get values from form

if(isset($_POST["runcompute"])){
    

#bus 1, slack bus - v,delta
    
$v1 = new Math_Complex(1,0);

#bus 2 - pq bus 

$q2_im = filter_input(INPUT_POST, "cap2Q_im", FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
$q2 =  new Math_Complex(0,$q2_im);

$l2r = filter_input(INPUT_POST, "load2S_real", FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
$l2im = filter_input(INPUT_POST, "load2S_im", FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
$l2 = new Math_Complex($l2r,$l2im);
 
$s2 = Math_ComplexOp::sub($q2,$l2);

#line

$lineZ_real = filter_input(INPUT_POST, "lineZ_real", FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
$lineZ_im = filter_input(INPUT_POST, "lineZ_im", FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);


$line1_z = new Math_Complex($lineZ_real,$lineZ_im);

#admittance between the two buses

$node2_y22 = Math_ComplexOp::inverse($line1_z);
$node2_y21 = Math_ComplexOp::negative($node2_y22);

# final output

$vfinal = "";

}


#use procedural method

#if isset ---

function computeV($currV){
   
global $v1, $s2, $l2im, $q2, $node2_y21, $node2_y22, $vfinal;

$prevbusIsum = Math_ComplexOp::mult($node2_y21, $v1);
$currVconj = Math_ComplexOp::conjugate($currV);
$currbusI = Math_ComplexOp::div($s2,$currVconj);

$IBus = Math_ComplexOp::sub($currbusI,$prevbusIsum);
$ZBus = Math_ComplexOp::inverse($node2_y22);

$vNext = Math_ComplexOp::mult($ZBus,$IBus);

return $vNext;

}//compute V



function GSiteration($initial,$err){
    global $vfinal;
   
    #iterations 
    $iterations = filter_input(INPUT_POST, "iterations", FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);

    #start with initial guess;
    $v = array();
    $v[0] = $initial;
    $v[1] = computeV($v[0]);
    
    for($i = 0; $i < $iterations; $i++){
    
                $v[0] = $v[1];
                $v[1] = computeV($v[0]);
            	$e = abs( ( $v[1]->abs() - $v[0]->abs() )/$v[1]->abs() ) * 100;

		$html = $e < $err ? "<tr class='converged'>" : "<tr>";
               
               $html .= "<td>" . $i . "</td>";
               $html .= "<td>" .  round($v[1]->abs(), 6) . " < " . round(rad2deg( $v[1]->angle() ), 4) . "</td>";
               $html .= "<td>" .  $v[1]->toString() . "</td>";
               $html .= "<td>" .  $e . "</td>";
               $html .= "</tr>";
              //  if($e < $err){ break; }
               echo $html;
        
    }
    $vfinal = $v[1];
  
    
}//GSIteration




?>

<html>
    <head>
        <title>Simon Agamah :: Load Flow using Gauss-Siedel Iteration </title>
        <link href="style.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div clas="wrapper">
            <h1>PHP Load Flow Analysis using GS Iteration by Simon Agamah</h1>
            <div class="networkInfo">
                <div class="networkDiagram">
                    <img src="network.jpg" style="width: 100%;"/>
                </div><!-- network diagram -->
                <div class="networkData">
                    <h2>Network Data</h2>
                    <form name="networkData" method="post" action="index.php">
                    <ul>
                        <li><div>Line Z</div><div><input type="text" value="0" name="lineZ_real"/> + <input type="text" value="0.5" name="lineZ_im"/>i</div></li>
                        <li><div>Bus 2 Load Power; S</div><div><input type="text" value="0.5" name="load2S_real"/> + <input type="text" value="1.0" name="load2S_im"/>i</div></li>
                        <li><div>Bus 2 Capacitor Power; Q</div><div><input type="text" value="1.0" name="cap2Q_im"/>i</div></li>
                        <li><div>Num. of Iterations</div><div><input type="text" value="10" name="iterations"/></div></li>
                        <li><div><input type="submit" class="submit" name="runcompute" value="Compute V2 &raquo;" /></div></li>  
                    </ul>
                    </form>
                </div><!-- network data -->
            </div><!-- networkInfo -->
            <?php if(isset($_POST["runcompute"])){ ?>
            <div class="resultsWindow">
                <div class="iterationResults">
                    <h2>Iteration Results</h2>
                    <table>
                        <thead>
                            <tr>
                        <th>Iteration</th>
                        <th>v2 ( Magnitude < Angle )</th>
                        <th>v2 ( Complex )</th>
                        <th>e</th>
                        </thead>
                    </tr>
                        <tbody>
                    <?php 
                   $vStart = new Math_Complex(1,0);
                   GSiteration($vStart,0.03);
                
                   ?>
                      </tbody> 
                    </table>
                </div><!-- iteration results -->
                <div class="resultsSummary">
                    <h3>v2 is <span><?php echo round($vfinal->abs(), 6) . " < " . round(rad2deg( $vfinal->angle() ), 4)?></span> after <span><?php echo $_POST["iterations"]; ?></span> Iterations</h3>
                    <h4>Line Z: <?php echo $line1_z->toString(); ?></h4>
                    <h4>Load 2 Power: <?php echo $l2->toString(); ?></h4>
                    <h4>Capacitor Power: <?php echo $q2->toString(); ?></h4>
                </div><!-- results summary -->
            </div><!-- resultsWindow -->
            <?php } ?>
        </div><!-- wrapper -->
        
        
    </body>
</html>