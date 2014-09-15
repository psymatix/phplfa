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
$mem = -memory_get_usage();

$p = new PowerNetwork();

$p->loadXMLNetwork("xmlnetworks/example6_7.xml");


$p->initializeNetwork();

lfNR::solve($p, 5E-5, 12);
printNetwork($p); 
printNetworkTable($p);
showLineCurrents($p);


$time += microtime(true);
$mem += memory_get_usage();
$memkb = $mem/1024;

echo "<br><hr>";
echo "completed in $time seconds";
echo "<br>";
echo "total memory allocated: $memkb";

function printNetwork($n){
    
        
     foreach($n->buses as $bus){ 
     
                
                  echo "Bus Number:" .  $bus->number . "<br>";
                  print_r("V:" . $bus->voltagePU . "<br>"); 
                  print_r("D:" . $bus->voltageAngle . "<br>"); 
                  if($bus->S){
                  print_r("P:" . $bus->S->getReal() . "<br>"); 
                  print_r("Q:" . $bus->S->getIm() . "<br>"); 
                  }
                  echo "<br><hr>";
                 
                  

  
 }
 
 
 function printNetworkTable($n){
    
         //   print_r($n);
    
    $html = '<table><thead>';
    $html .= '<tr>';
    $html .= '<th>No.</th>';
    $html .= '<th>|V|</th>';
    $html .= '<th>d</th>';
    $html .= '<th>Load MW</th>';
    $html .= '<th>Load Mvar</th>';
    $html .= '<th>Gen MW</th>';
    $html .= '<th>Gen Mvar</th>';
    $html .= '<th>qMax, qMin</th>';
    $html .= '<th>q Lim</th>';
    $html .= '<th>vMax, vMin</th>';
    $html .= '<th>v LIM</th>';
    $html .= '<th>Bus P</th>';
    $html .= '<th>Bus Q</th>';
    
    
    $html .= '</tr>';
    $html .= '<tbody>';
    
     foreach($n->buses as $bus){ 
         
                $html .= '<tr>';
                $html .= '<td>'. $bus->number .'</td>';
                $html .= '<td>'. $bus->voltagePU . '</td>';
                $html .= '<td>'. $bus->voltageAngle . '</td>';
           
                $p = $n->basemva ? $bus->S->getReal() * $n->basemva : $bus->S->getReal();
                $q = $n->basemva ? $bus->S->getIm() * $n->basemva : $bus->S->getIm();
                
           #     $genP = $n->basemva ? $bus->genMW * $n->basemva : $bus->genMW;
           #     $genQ = $n->basemva ? $bus->genMVAR * $n->basemva : $bus->genMVAR;
                
                $loadP = $n->basemva ? $bus->loadMW * $n->basemva : $bus->loadMW;
                $loadQ = $n->basemva ? $bus->loadMVAR * $n->basemva : $bus->loadMVAR;
                
                
                $genP = $p + abs($loadP);
                $genQ = $q + abs($loadQ);
              
                $html .= '<td>' . abs($loadP) . '</td>';
                $html .= '<td>' . abs($loadQ) . '</td>';
                $html .= '<td>' . ($bus->gen ? $genP : '') .'</td>';
                $html .= '<td>' . ($bus->gen ? $genQ : '') . '</td>';
                $html .= '<td>'. $bus->qMax . ',' . $bus->qMin .'</td>';
                $qlim = ($bus->qMax * $n->basemva  < $genQ) || ($bus->qMin * $n->basemva > $genQ) ? "excess" : "OK";  
                $html .= '<td>'.  $qlim .'</td>';
                $html .= '<td>'. $bus->vMax . ',' . $bus->vMin .'</td>';
                $lim = ($bus->vMax < $bus->voltagePU) || ($bus->vMin > $bus->voltagePU) ? "excess" : "OK";  
                $html .= '<td>'.  $lim .'</td>';
                $html .= '<td>'.  $p . '</td>';
                $html .= '<td>'.  $q .'</td>';
                $html .= '</tr>';
                 
                }
  $html .= '</tbody></table>';
  
  echo $html;
                  

  
 }
 
 echo "converged after " . lfNR::$step . " steps <br><hr>";
 print_r($n->voltageControlledBuses);

}



?>
