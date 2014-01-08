#!/usr/bin/php
<?php
/*
 * global
 */
require_once 'CallAPI.php';


if (! defined ( "STDIN" )) {
	define ( "STDIN", fopen ( "php://stdin", "r" ) );
}
date_default_timezone_set ( 'Europe/Paris' );
$temp = array ();
// Extend stream timeout to 24 hours
// stream_set_timeout(STDIN, 86400);

while ( ! feof ( STDIN ) ) {
	// Split the output (space delimited) from squid into an array.
	// external_acl_type myhelper %SRC %URI %LOGIN /usr/bin/php /var/www/squid/restrictions.php
	// declarations, inits
	$clientIP = null; // cleint local IP ex: 192.168.0.5
	$url = null; // visited page full url ex: exemple.com/exemple.php?foo=1&...
	$ip = null; // from $url
	$username = null; // client username
	
	$input = trim ( fgets ( STDIN ) );
	
	if ($input != NULL) {
		
		$temp = null;
		$temp = split ( ' ', $input );
		if (sizeof ( $temp ) === 3) {
			$clientIP = trim ( $temp [0] );
			$url = trim ( $temp [1] );
			if (array_key_exists ( 'host', parse_url ( $url ) )) {
				$ip = gethostbyname ( parse_url ( $url )['host'] );
			}
			$username = trim ( $temp [2] );
		}
		$ERR_MESSAGE = ""; // ERROR MESSAGE note: add %o to error page. For error pages in french use /usr/share/errors/fr/ERR_ACCESS_DENIED
		
		/*
		 * verify : if $username has restrictions if $clientIP has restrictions if $hostname has restrictions if $url has restrictions etc... result: if true, result should be OK [message=Your%20restriction...] else result should be ERR rsults must end with "\n" type of restrictions: - quota time per day/week - downloaded volume per day/week
		 */
		
		// CallAPI class <-----
		
		///TEST
		$reqRestrictions = array(
			
				"action"=> "get_restrictions_by_username",
				"username" => $username
		);
		
		$restrictions = CallAPI::sample($reqRestrictions);
		if($restrictions !=NULL && $restrictions->status ==="ok"){
			foreach ($restrictions->restrictions as $restriction) {
				//print_r($restriction);
			}
		}
		
		
		
		$users = array();
		
		
			$stef = array(
					"action" => "get_member_by_id",
					"id"=>1
			);
			$gent = array(
					"action" => "get_member_by_id",
					"id"=>2
			);
			
			
			$stef = CallAPI::sample($stef);
			$gent = CallAPI::sample($gent);
			
			if($stef->status ==="ok"){
				$stef=$stef->member;
			
			$users[$stef->username] = $stef;
			
			}
			
			if($gent->status ==="ok"){
				$gent=$gent->member;
					
				$users[$gent->username] = $gent;
					
			}
		
		
		//print_r($users);
		//echo $users[$username]->allow."\n";
		
		$ERR_MESSAGE = $clientIP . " " . $url . " " . $ip . " " . $username;
		if ( intval($users[$username]->allow) === 0 ) {
			fwrite ( STDOUT, "ERR message=" . rawurlencode ( $ERR_MESSAGE ) . "\n" ); // deny access
		} else {
			fwrite ( STDOUT, "OK\n" ); // allow access
		}
	} else {
		//fwrite ( STDOUT, "ERR\n" );
	}
	
	
}

?>
