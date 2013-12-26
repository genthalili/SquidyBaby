<?php

// load RedBeanPHP and initialize
require_once ('rb.php');
R::setup ( 'mysql:host=localhost;dbname=squid', 'root', 'gent' );

// clear database for testing
// R::nuke();

// load available resources
require_once ('Resource.php');

// Default response
$json = array (
		'status' => 'error',
		'msg' => 'Resource not available' 
);

// Mapping resources
$possible_url = array (
		'put_member',
		'update_member',
		'get_member_by_id',
		'get_member_by_credentials',
		'delete_member',
		'put_log',
		'get_log_all',
		'get_log_newer_than',
		'update_log' 
);

// REST API
if (isset ( $_GET ["action"] ) && in_array ( $_GET ["action"], $possible_url )) {
	
	// mapping with action
	switch ($_GET ["action"]) {
		
		case 'put_member' :
			if (isset ( $_GET ["username"] ) && isset ( $_GET ["password"] ) && isset ( $_GET ["email"] )) {
				
				// get only the necessary parameters
				$data = array (
						'username' => $_GET ["username"],
						'password' => $_GET ["password"],
						'email' => $_GET ["email"] 
				);
				
				$id = Resource::put_member ( $data );
				
				if ($id) {
					$json = array (
							'status' => 'ok',
							'id' => $id 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot add member' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing' 
				);
			}
			break;
		
		case 'update_member' :
			if (isset ( $_GET ["id"] ) && (isset ( $_GET ["username"] ) || isset ( $_GET ["password"] ) || isset ( $_GET ["email"] ))) {
				
				// get only the necessary parameters
				$data = array (
						'id' => $_GET ["id"],
						'email' => $_GET ["email"] 
				);
				
				$id = Resource::put_member ( $data );
				
				if ($id) {
					$json = array (
							'status' => 'ok',
							'id' => $id 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot add member' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing' 
				);
			}
			break;
		
		case 'get_member_by_id' :
			if (isset ( $_GET ["id"] )) {
				$member = Resource::get_member_by_id ( $_GET ["id"] );
				
				if ($member) {
					$json = array (
							'status' => 'ok',
							'member' => $member->export () 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Member not found' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'ID is missing' 
				);
			}
			break;
		
		case 'get_member_by_credentials' :
			if (isset ( $_GET ["username"] ) && isset ( $_GET ["password"] )) {
				$member = Resource::get_member_by_credentials ( $_GET ["username"], $_GET ["password"] );
				
				if ($member) {
					$json = array (
							'status' => 'ok',
							'member' => $member->export () 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Member not found' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'ID is missing' 
				);
			}
			break;
		
		case 'delete_member' :
			if (isset ( $_GET ["id"] )) {
				
				if (Resource::delete_member ( $_GET ["id"] )) {
					$json = array (
							'status' => 'ok' 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Member not found' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'ID is missing' 
				);
			}
			break;
		
		case 'put_log' :
			if (isset ( $_GET ["time"] ) && isset ( $_GET ["remotehost"] ) && isset ( $_GET ["bytes"] ) && isset ( $_GET ["url"] ) && isset ( $_GET ["username"] )) {
				
				// get only the necessary parameters
				$data = array (
						"time" => $_GET ["time"],
						"remotehost" => $_GET ["remotehost"],
						"bytes" => $_GET ["bytes"],
						"url" => $_GET ["url"],
						"username" => $_GET ["username"] 
				);
				
				$id = Resource::put_log ( $data );
				
				if ($id) {
					$json = array (
							'status' => 'ok',
							'id' => $id 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot add log' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing' 
				);
			}
			break;
		
		case 'get_log_all' :
			if (isset ( $_GET ["order"] ) && isset ( $_GET ["limit"] )) {
				
				$logs = Resource::get_log_all ( $_GET ["order"], $_GET ["limit"] );
				
				if ($logs) {
					$formated_logs = array ();
					foreach ( $logs as $log ) {
						$formated_logs [] = $log->export ();
					}
					
					$json = array (
							'status' => 'ok',
							'logs' => $formated_logs 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot get log' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing' 
				);
			}
			break;
		
		case 'get_log_newer_than' :
			if (isset ( $_GET ["time"] ) && isset ( $_GET ["username"] ) && isset ( $_GET ["host"] )) {
				
				$log = Resource::get_log_newer_than ( $_GET ["time"], $_GET ["host"], $_GET ["username"] );
				
				if ($log) {
					
					$json = array (
							'status' => 'ok',
							'log' => $log->export () 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot get log' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing' 
				);
			}
			break;
		
		case 'update_log' :
			if (isset ( $_GET ["id"] ) && (  isset ( $_GET ["time"] ) || isset ( $_GET ["remotehost"] ) || isset ( $_GET ["bytes"] ) || isset ( $_GET ["url"] ) || isset ( $_GET ["username"] ))) {
				
				// get only the necessary parameters
				$data = array (
						"id" => $_GET ["id"],
						
						"bytes" => $_GET ["bytes"]
						
				);
				
				$id = Resource::put_log ( $data );
				
				if ($id) {
					$json = array (
							'status' => 'ok',
							'id' => $id 
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot add log' 
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing' 
				);
			}
			break;
	}
}

header ( "Content-Type: application/json" );
echo getCallback () . '(' . json_encode ( $json ) . ')';
function getCallback() {
	if (isset ( $_GET ['callback'] )) {
		return $_GET ['callback'];
	}
	return null;
}

?>
