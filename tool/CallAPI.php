<?php

/**
 * @author Gent
 *
 */

class CallAPI {
	//public static $PATH_TO_API = "http://localhost/SquidyBaby/API/";
	public static $PATH_TO_API = "http://dicodraw.com/API/";
	
	function __construct() {
	}
	
	public static function sample($data = false) {
		// default $method = GET
		return self::callAPI ( "GET", self::$PATH_TO_API, $data );
	}
	
	public static function pameters($method, $url, $data = false) {
		return self::callAPI ( $method, $url, $data );
	}
	
	// Method: POST, PUT, GET etc
	// Data: array("param" => "value") ==> index.php?param=value
	private static function callAPI($method, $url, $data = false) {
		$curl = curl_init ();
		
		switch ($method) {
			case "POST" :
				curl_setopt ( $curl, CURLOPT_POST, 1 );
				
				if ($data)
					curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
				break;
			case "PUT" :
				curl_setopt ( $curl, CURLOPT_PUT, 1 );
				break;
			default :
				if ($data)
					$url = sprintf ( "%s?%s", $url, http_build_query ( $data ) );
		}
		
		// Optional Authentication:
		// curl_setopt ( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		// curl_setopt ( $curl, CURLOPT_USERPWD, "username:password" );
		
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); //no need SSL verifiypeer (for localhost)
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		
		$result = curl_exec ( $curl );
		
		//Json Converter : $result is JSONP, use json_decode to turn it into php array:
		//remove padding
		$result=preg_replace('/.+?({.+}).+/','$1',$result);
		
		// now, process the JSON string
		$response = json_decode($result);

		return $response;
	}
}

// TEST
/*
$data = array (
		"action" => "get_member_by_credentials",
		"username" => "stef",
		"password" => "stef" 
);

$test = CallAPI::sample ( $data );

print_r($test->member->id);
echo "\n".count($test->member);
*/

?>
