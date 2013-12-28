<?php

// $url ="http://cache.20minutes.fr/images/hat-accueil.gif";
// print_r(parse_url($url)) ."\n";

// $test = array("i" =>null, "value"=>null);
// //$test["i"]=1;
// //$test["value"]=500;
// print_r($test);

require_once 'LogReader.php';

//$reader = LogReader::start ();

$a = array();

$b = array (
				"i" => 1,
				"time" => null 
		);
$key="bla";

$a[$key] =$b;

$a[$key]['time'] = "ok";

$a[$key]['i']++;

print_r($a);

echo array_search("ok", $a)."\n";

?>