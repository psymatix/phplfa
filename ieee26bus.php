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
$mem = -memory_get_usage();

$p = new PowerNetwork();
$p->loadXMLNetwork("xmlnetworks/IEEE26busSystem.xml");

$p->initializeNetwork();

lfNR::solve($p, 0.001, 12);

displayFn::printNetwork($p);
displayFn::printNetworkTable($p);
displayFn::showLineCurrents($p);


$time += microtime(true);
$mem += memory_get_usage();
$memkb = $mem/1024;

echo "<br><hr>";
echo "completed in $time seconds <br>";
echo "<br>";
echo "total memory allocated: $memkb";
echo "converged after " . lfNR::$step . " steps <br><hr>";


?>
</body>
</html>
