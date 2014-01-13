#!/usr/bin/php
<?php
require_once 'CallAPI.php';

if (! defined ( "STDIN" )) {
	define ( "STDIN", fopen ( "php://stdin", "r" ) );
}

$temp = array ();

// Extend stream timeout to 24 hours
stream_set_timeout ( STDIN, 86400 );

while ( $input = fgets ( STDIN ) ) {
	// Split the output (space delimited) from squid into an array.
	$temp = split ( ' ', $input );
	
	// Set the URL from squid to a temporary holder.
	$output = $temp [0] . "\n";
	
	$username = trim ( $temp [2] );
	
	$reqRestrictionsUrl = array (
			"action" => "get_restrictions_by_username_and_restype",
			"username" => $username,
			"restype" => 'url' 
	);
	
	$restrictionsUrl = CallAPI::sample ( $reqRestrictionsUrl );
	
	if ($restrictionsUrl != NULL && $restrictionsUrl->status === 'ok') {

		$restriction = $restrictionsUrl->restrictions [0]; // get only first restriction //TODO 
		$urls = explode ( " ", $restriction->resdata );
		
		foreach ( $urls as $url ) {
			
			if (strpos (  parse_url ($temp [0])['host'] , parse_url ( $url )['host'] ) !== false) {
				$output = "302:".$restriction->resredirect."\n";
				break;
			}
		}
	}
	
	echo $output;
}

?>