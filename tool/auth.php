#!/usr/bin/php
<?php
require_once 'CallAPI.php';

require_once 'LogReader.php';

if (! defined ( "STDIN" )) {
	define ( "STDIN", fopen ( "php://stdin", "r" ) );
}

while ( ! feof ( STDIN ) ) {
	$username = null;
	$password = null;
	$line = trim ( fgets ( STDIN ) );
	
	// $line = rawurldecode($line);
	$fields = explode ( ' ', $line );
	if (sizeof ( $fields ) == 2) {
		$username = rawurldecode ( $fields [0] );
		$password = rawurldecode ( $fields [1] );
	}
	
	if ($username !== null and $password !== null) {
		
		// check auth on db
		$goodcred = successLogIn ( $username, $password );
		if ($goodcred) {
			
			// connection passed
			
			// start Logreader
			
			try {
				$reader = LogReader::start ();
			} catch ( Exception $e ) {
			}
			fwrite ( STDOUT, "OK\n" );
		} else {
			
			fwrite ( STDOUT, "ERR\n" );
		}
	}
	
	fflush(STDIN);
	fflush(STDOUT);
}
/**
 *
 * @param String $username        	
 * @param String $password        	
 * @return boolean : false if no user found else ture
 */
function successLogIn($username, $password) {
	
	// Call API
	$data = array (
			"action" => "get_member_by_credentials", // method
			"username" => $username,
			"password" => $password 
	);
	try {
		
		$login = CallAPI::sample ( $data );
		if ($login->status === "ok" && count ( $login->member ) === 1) {
			return true;
		} else {
			return false;
		}
	} catch ( Exception $e ) {
		return false;
	}
}

?>
