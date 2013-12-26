#!/usr/bin/php
<?php

require_once 'CallAPI.php';

if (! defined ( "STDIN" )) {
	define ( "STDIN", fopen ( "php://stdin", "r" ) );
}

while ( $input = fgets ( STDIN ) ) {
	$username = null;
	$password = null;
	$line = trim ( $input );
	
	$fields = explode ( ' ', $line );
	if (sizeof ( $fields ) == 2) {
		$username = rawurldecode ( $fields [0] );
		$password = rawurldecode ( $fields [1] );
	}
	
	if ($username !== null and $password !== null) {
		// check auth on db
		if (!successLogIn($username, $password)) {
			
			fwrite ( STDOUT, "ERR\n" );
		} else {
			// connection passed
			fwrite ( STDOUT, "OK\n" );
		}
	} else {
		fwrite ( STDOUT, "ERR\n" );
	}
}
function successLogIn($username, $password) {
	
	// Call API
	$data = array (
			"action" => "get_member_by_credentials", // method
			"username" => $username,
			"password" => $password 
	);
	
	$login = CallAPI::sample($data);
	if ($login->status === "ok" && count($login->member)===1){
		return true;
	}else{
		return false;
	}
	
}
?>