<html>
    <head>
        <style type="text/css">
            table {
                border: 1px solid #000;
            }
            
            tr, td {
                 border: 1px solid #000;  
            }
        </style>
    </head>
    <body>
<?php

require_once("Math/Matrix.php");
require_once("Math/Complex.php");
require_once("Math/ComplexOp.php");

//Autoloader

function PSAutoloader($class){
    include 'classes/' .  $class . '.php';
}

spl_autoload_register('PSAutoloader');
//initialization procedures

$time = -microtime(true);

$p = new PowerNetwork();
//$p->loadXMLNetwork("example1.xml");
//$p->loadXMLNetwork("exercise6_1.xml");
$p->loadXMLNetwork("xmlnetworks/IEEE30busSystem.xml");

$p->initializeNetwork();

//print_r($p->voltageControlledBuses);

//print_r( lfNR::exec($p) );

lfNR::solve($p, 0.001, 12);
printNetwork($p); 

$time += microtime(true);
echo "<br><hr>";
echo "completed in $time seconds";


function printNetwork($n){
    
         //   print_r($n);
    
    $html = '<table><thead>';
    $html .= '<tr>';
    $html .= '<th>No.</th>';
    $html .= '<th>|V|</th>';
    $html .= '<th>d</th>';
   // $html .= '<th>Load MW</th>';
   // $html .= '<th>Load Mvar</th>';
    $html .= '<th>Gen MW</th>';
    $html .= '<th>Gen Mvar</th>';
    $html .= '<th>qMax, qMin</th>';
    
    $html .= '</tr>';
    $html .= '<tbody>';
    
     foreach($n->buses as $bus){ 
         
                $html .= '<tr>';
                $html .= '<td>'. $bus->number .'</td>';
                $html .= '<td>'. $bus->voltagePU . '</td>';
                $html .= '<td>'. $bus->voltageAngle . '</td>';
              // $html .= '<td></td>';
              // $html .= '<td></td>';
                $p = $n->basemva ? $bus->S->getReal() * $n->basemva : $bus->S->getReal();
                $q = $n->basemva ? $bus->S->getIm() * $n->basemva : $bus->S->getIm();
                $html .= '<td>' . $p .'</td>';
                $html .= '<td>' . $q . '</td>';
                $html .= '<td>'. $bus->qMax . ',' . $bus->qMin .'</td>';
                $html .= '</tr>';
                 
                }
  $html .= '</tbody></table>';
  
  echo $html;
                  

  
 }

 echo "converged after " . lfNR::$step . " steps <br><hr>";
 print_r($p->voltageControlledBuses);
 


?>
</body>
</html>