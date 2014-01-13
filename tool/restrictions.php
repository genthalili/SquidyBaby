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
		
		
		$reqRestrictionsVol = array(			
				"action"=> "get_restrictions_by_username_and_restype",
				"username" => $username,
				"restype" => 'volume'
		);
		$reqRestrictionsQ = array(				
				"action"=> "get_restrictions_by_username_and_restype",
				"username" => $username,
				"restype" => 'quota'
		);
		
		$restrictionsVol = CallAPI::sample($reqRestrictionsVol);
		$restrictionsQ = CallAPI::sample($reqRestrictionsQ);
		
		$ERR_RESP = 'ERR message=%s'; //deny
		$OK_RESP = "OK"; //allow

		
		if(($restrictionsVol !=NULL && $restrictionsVol->status ==="ok") || ($restrictionsQ !=NULL && $restrictionsQ->status ==="ok")){
			
			$_resp =$OK_RESP;
			
			if($restrictionsVol->status ==="ok"){	
				foreach ($restrictionsVol->restrictions as $restriction) {
					$volumeRes = $restriction->resdata;
					switch ($restriction->resdate) {
						case 'day':
							$getVolume = array(
									"action"=> "get_volume",
									"username" => $username,
									"start_date" => date("Y-m-j")
							);
							$volumeUser = CallAPI::sample($getVolume );
							

							if($volumeUser != NULL && $volumeUser->status ==="ok"){
									
								$volumeActu = $volumeUser->volume->val;
								if($volumeActu>$volumeRes){
									$ERR_MESSAGE = $ERR_MESSAGE ."Volume depassé pour la journée ";
									$_resp = $ERR_RESP;
								}
									
							}
								
							break;
						case 'week':
							
							$custom_date = strtotime( date("Y-m-j") );
							$week_start = date('Y-m-j', strtotime('this week monday', $custom_date));
						
							$getVolume = array(
									"action"=> "get_volume",
									"username" => $username,
									"start_date" => $week_start
							);
							$volumeUser = CallAPI::sample($getVolume );
							if($volumeUser != NULL && $volumeUser->status ==="ok"){
									
								$volumeActu = $volumeUser->volume->val;
								if($volumeActu>$volumeRes){
									$ERR_MESSAGE = $ERR_MESSAGE ."Volume depassé pour la semaine ";
									$_resp = $ERR_RESP;
								}
									
							}
							
							break;				
						default:				
							break;
					}				
				}
			}
			
			
			//Quota
			if($restrictionsQ->status ==="ok"){
				foreach ($restrictionsQ->restrictions as $restriction) {
					$quotaRes = $restriction->resdata;
					switch ($restriction->resdate) {
						case 'day':
							$getQuota = array(
							"action"=> "get_quota",
							"username" => $username,
							"start_date" => date("Y-m-j")
							);
							$quotaUser = CallAPI::sample($getQuota );
								
					
							if($quotaUser != NULL && $quotaUser->status ==="ok"){
									
								$quotaActu = $quotaUser->volume->quota_time;
								if($quotaActu>$quotaRes){
									$ERR_MESSAGE = $ERR_MESSAGE ."Temps d'utilisation depassé pour la journée ";
									$_resp = $ERR_RESP;
								}
									
							}
					
							break;
						case 'week':
								
							$custom_date = strtotime( date("Y-m-j") );
							$week_start = date('Y-m-j', strtotime('this week monday', $custom_date));
							
							$quotaUser = array(
									"action"=> "get_quota",
									"username" => $username,
									"start_date" => $week_start
							);
							$quotaUser = CallAPI::sample($quotaUser );
							if($quotaUser != NULL && $quotaUser->status ==="ok"){
									
								$quotaActu = $quotaUser->volume->quota_time;
								if($quotaActu>$quotaRes){
									$ERR_MESSAGE = $ERR_MESSAGE ."Temps d'utilisation depassé pour la semaine ";
									$_resp = $ERR_RESP;
								}
									
							}
								
							break;
						default:
							break;
					}
				}		
			}
			
			
			
			fwrite ( STDOUT, sprintf($_resp,  rawurlencode ( $ERR_MESSAGE ) )."\n" ); // deny access
			
		}else if(($restrictionsVol !=NULL && 
				$restrictionsVol->status ==="error" &&
				 $restrictionsVol->msg ==="User is unknown")
				 || ($restrictionsQ !=NULL && 
				$restrictionsQ->status ==="error" &&
				 $restrictionsQ->msg ==="User is unknown")){
			fwrite ( STDOUT, $OK_RESP."\n" ); // deny access
		}else{
			//if not un user 
			$ERR_MESSAGE="Ne peut pas acceder à API, veuillez contacter l'adminstrateur!!!";
			fwrite ( STDOUT, "ERR message=" . rawurlencode ( $ERR_MESSAGE ) . "\n" ); // deny access		
		}
		
		
		
		
	}
	
	
}

?>
