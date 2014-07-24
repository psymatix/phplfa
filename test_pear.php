<?php 
echo "<link rel='stylesheet' type='text/css' href='sample.css'>"; 
echo "hello world";

require_once("Text/Highlighter.php"); 

// This is the code we want to display 
$code = "<? 
// This is a test page for PEAR Text_Highlighter package 
\$message = \"Hello, world!\"; 

echo \$message; 
?>"; 

// What to display - PHP code 
$what = "php"; 

// Define the class 
$highlighter =& Text_Highlighter::factory($what); 

// Call highlight method to display the code to web browser 
echo $highlighter->highlight($code); 


?>