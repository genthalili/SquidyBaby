<?php

// $url ="http://cache.20minutes.fr/images/hat-accueil.gif";
// print_r(parse_url($url)) ."\n";

// $test = array("i" =>null, "value"=>null);
// //$test["i"]=1;
// //$test["value"]=500;
// print_r($test);

//require_once 'LogReader.php';
require_once 'CallAPI.php';

//$reader = LogReader::start ();

// $a = array();

// $b = array (
// 				"i" => 1,
// 				"time" => null 
// 		);
// $key="bla";

// $a[$key] =$b;

// $a[$key]['time'] = "ok";

// $a[$key]['i']++;

// print_r($a);

// echo array_search("ok", $a)."\n";

// $data = array (
// 		"action" => "get_volume",
// 		"username" => "*",
// 		"start_date" => "2013-12-22"
		
// );

// $test = CallAPI::sample ( $data );

// print_r($test->volume[0]->val);

$getlastlog = array (
		"action" => "get_log_all",
		"order" => "time DESC",
		"limit" => 1
);

$resultgetlastlog = CallAPI::sample ( $getlastlog );
	
if ($resultgetlastlog != NULL && $resultgetlastlog->status === 'ok') {

	$val = floatval($resultgetlastlog->logs[0]->time);
}
echo $val;

?>