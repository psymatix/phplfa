# PHP Power Systems Analysis and Modelling Library

A library for performing electrical power systems analysis including a power flow analysis using the Newton-Raphson method and a simple short circuit fault evaluation.

Electrical network is loaded either via XML definition or using class instance methods. This library allows power systems to be simulated in PHP websites without using a simulation engine in a different language.

Requirements and Dependencies
-----------------------------

1. PHP 4.0.0
2. PEAR packages
  * Math Matrix http://pear.php.net/package/Math_Matrix
  * Math Complex http://pear.php.net/package/Math_Complex
  * Math Vector http://pear.php.net/package/Math_Vector 

(Credit to J.M. Castagnetto for developing these packages <https://github.com/jmcastagnetto>)

API
-----
The library allows user to create the following models of a Power Network

1. Transmission Line

2. Bus / Feeder with multiple connections

3. Generic Element which may be a Load or Generator depending on direction of power flow out of or into Bus

4. Newton Raphson power flow solver (lfNR)

5. Admittance matrix generator

6. Jacobian matrix generator

Comments are included in Class files for formulas etc.

Usage
-----
Sample networks and formats in /xmlnetworks directory and sample files in root directory including non-xml format

```php
#instatiate class
$p = new PowerNetwork();

#load network data from XML
$p->loadXMLNetwork("xmlnetworks/IEEE26busSystem.xml");

#initialize and form matrices to be solved
$p->initializeNetwork();

#perform power flow analysis (network, tolerance, maximum iterations)
lfNR::solve($p, 0.001, 12);

#display results and original network
displayFn::printNetwork($p);
displayFn::printNetworkTable($p);
displayFn::showLineCurrents($p);

```
