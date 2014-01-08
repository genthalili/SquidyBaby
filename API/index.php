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
		'get_members_by_username',
		'get_member_by_username',
		'get_groups_by_groupname',
		'get_member_by_credentials',
		'get_member_all',
		'delete_member',
		'put_group',
		'put_group_member',
		'delete_group_member',
		'get_group_all',
		'delete_group',
		'put_restriction',
		'put_restriction_member',
		'delete_restriction_member',
		'put_restriction_group',
		'delete_restriction_group',
		'get_restriction_all',
		'delete_restriction',
		'put_log',
		'get_log_newer_than',
		'update_log',
		'get_volume',
		'get_quota',
		'update_index_to_member',
		'get_restrictions_by_username'
);

// REST API
if (isset ( $_GET ["action"] ) && in_array ( $_GET ["action"], $possible_url )) {

	// mapping with action
	switch ($_GET ["action"]) {

		case 'put_member' :
			if (isset ( $_GET ["username"] ) && isset ( $_GET ["password"] )) {

				// get only the necessary parameters
				$data = array (
						'username' => preg_replace('/\s+/', '', strtolower($_GET ["username"]) ),
						'password' => sha1($_GET ["password"]),
						'created' => R::isoDate()
				);

				$id = Resource::put_member ( $data );

				if ($id) {
					$member = Resource::get_member_by_id ( $id );

					$json = array (
							'status' => 'ok',
							'member' => $member->export()
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot add user'
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
			if (isset ( $_GET ["username"] ) && isset ( $_GET ["password"] )) {

				$members = Resource::get_members_by_username($_GET ["username"]);
				foreach ($members as $member) {
					$id = Resource::update_member ( $member, 'password', sha1($_GET ["password"]) );
					break;
				}

				if ($id) {
					$json = array (
							'status' => 'ok'
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot change password'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'get_members_by_username' :
			if (isset ( $_GET ["username"] )) {
				$members = Resource::get_members_by_username ( '%'. $_GET ["username"] .'%' );
				$formated_members = array();
				foreach ($members as $member) {
					$formated_members[] = $member->username;
				}

				if ($formated_members) {
					$json = $formated_members;
				} else {
					$json = array (
							'No matches found'
					);
				}
			} else {
				$json = array (
						'Username is missing'
				);
			}
			break;

		case 'get_member_by_username' :
			if (isset ( $_GET ["username"] )) {
				$members = Resource::get_members_by_username ( $_GET ["username"] );
				$m = null;
				foreach ( $members as $member ) {
					$m = $member;
					break;
				}

				if ($m) {
					$json = array (
							'status' => 'ok',
							'member' => $m->export ()
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'User not found'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Username is missing'
				);
			}
			break;

		case 'get_groups_by_groupname' :
			if (isset ( $_GET ["groupname"] )) {
				$groups = Resource::get_groups_by_groupname ( '%'. $_GET ["groupname"] .'%' );
				$formated_groups = array();
				foreach ($groups as $group) {
					$formated_groups[] = $group->groupname;
				}

				if ($formated_groups) {
					$json = $formated_groups;
				} else {
					$json = array (
							'No matches found'
					);
				}
			} else {
				$json = array (
						'Groupname is missing'
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
							'msg' => 'User not found'
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
							'msg' => 'User not found'
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
				$username = Resource::delete_member ( $_GET ["id"] );
				if ($username) {
					foreach(Resource::get_group_all_id () as $id){
						Resource::delete_group_member($id, $username);
					}

					foreach(Resource::get_restriction_all_id () as $id){
						Resource::delete_restriction_member($id, $username);
					}

					$json = array (
							'status' => 'ok'
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'User not found'
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
			if (isset ( $_GET ["time"] ) && isset ( $_GET ["TCP_codes"] ) && isset ( $_GET ["remotehost"] ) && isset ( $_GET ["bytes"] ) && isset ( $_GET ["url"] ) && isset ( $_GET ["username"] ) && isset ( $_GET ["indexID"] )) {

				// get only the necessary parameters
				$data = array (
						"time" => $_GET ["time"],
						"TCP_codes" => $_GET ["TCP_codes"],
						"remotehost" => $_GET ["remotehost"],
						"bytes" => $_GET ["bytes"],
						"url" => $_GET ["url"],
						"username" => $_GET ["username"],
						"indexID" => $_GET ["indexID"]
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

		case 'get_member_all' :
			if (isset ( $_GET ["order"] ) && isset ( $_GET ["limit"] )) {

				$members = Resource::get_member_all ( $_GET ["order"], $_GET ["limit"] );

				if ($members) {
					$formated_members = array ();
					foreach ( $members as $member ) {
						$formated_members [] = $member->export ();
					}
						
					$json = array (
							'status' => 'ok',
							'members' => $formated_members
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot get users'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'get_group_all' :
			if (isset ( $_GET ["order"] ) && isset ( $_GET ["limit"] )) {

				$groups = Resource::get_group_all ( $_GET ["order"], $_GET ["limit"] );

				if ($groups) {
					$formated_groups = array ();
					foreach ( $groups as $group ) {
						$formated_groups [] = $group->export ();
					}
						
					$json = array (
							'status' => 'ok',
							'groups' => $formated_groups
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot get groups'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'get_restriction_all' :
			if (isset ( $_GET ["order"] ) && isset ( $_GET ["limit"] )) {

				$restrictions = Resource::get_restriction_all ( $_GET ["order"], $_GET ["limit"] );

				if ($restrictions) {
					$formated_restrictions = array ();
					foreach ( $restrictions as $restriction ) {
						$formated_restrictions [] = $restriction->export ();
					}
						
					$json = array (
							'status' => 'ok',
							'restrictions' => $formated_restrictions
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot get restrictions'
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
			if (isset ( $_GET ["time"] ) && isset ( $_GET ["username"] ) && isset ( $_GET ["host"] )   && isset ( $_GET ["TCP_codes"] )) {

				$log = Resource::get_log_newer_than ( $_GET ["time"], $_GET ["host"], $_GET ["username"], $_GET ["TCP_codes"]  );

				if ($log) {
						
					$json = array (
							'status' => 'ok',
							'log' => $log->export()
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

		case 'put_group' :
			if (isset ( $_GET ["groupname"] ) ) {
					
				// get only the necessary parameters
				$data = array (
						'groupname' => preg_replace('/\s+/', '', strtolower($_GET ["groupname"]) ),
				);
					
				$id = Resource::put_group ( $data );
					
				if ($id) {
					$group = Resource::get_group_by_id ( $id );

					$json = array (
							'status' => 'ok',
							'group' => $group->export()
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot add group'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'put_restriction' :

			if (isset ( $_GET ["resname"] ) && isset ( $_GET ["resdate"] ) && isset ( $_GET ["resdata"] ) &&  isset ( $_GET ["restype"] ) ) {
					
				// get only the necessary parameters
				$data = array (
						'resname' => preg_replace('/\s+/', '', strtolower($_GET ["resname"]) ),
						'resdate' => $_GET ["resdate"],
						'resdata' => $_GET ["resdata"],
						'restype' => $_GET ["restype"]
				);
					
				$id = Resource::put_restriction ( $data );
					
				if ($id) {
					$restriction = Resource::get_restriction_by_id ( $id );

					$json = array (
							'status' => 'ok',
							'restriction' => $restriction->export()
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot add restriction'
					);
				}
					
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'put_group_member' :
			if (isset ( $_GET ["id"] ) && isset ( $_GET ["username"] ) ) {

				$members = Resource::get_members_by_username( strtolower($_GET ["username"]));

				if($members){
					$id = Resource::put_group_member($_GET ["id"], strtolower($_GET ["username"]));

					if ($id) {
						$json = array (
								'status' => 'ok',
								'id' => $id
						);
					} else {
						$json = array (
								'status' => 'error',
								'msg' => 'Cannot add user to group'
						);
					}
				}
				else{
					$json = array (
							'status' => 'error',
							'msg' => 'User is unknown'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'put_restriction_member' :
			if (isset ( $_GET ["id"] ) && isset ( $_GET ["username"] ) ) {

				$members = Resource::get_members_by_username( strtolower($_GET ["username"]));

				if($members){
					$id = Resource::put_restriction_member($_GET ["id"], strtolower($_GET ["username"]));

					if ($id) {
						$json = array (
								'status' => 'ok',
								'id' => $id
						);
					} else {
						$json = array (
								'status' => 'error',
								'msg' => 'Cannot add user to restriction'
						);
					}
				}
				else{
					$json = array (
							'status' => 'error',
							'msg' => 'User is unknown'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'put_restriction_group' :
			if (isset ( $_GET ["id"] ) && isset ( $_GET ["groupname"] ) ) {

				$groups = Resource::get_groups_by_groupname( strtolower($_GET ["groupname"]));

				if($groups){
					$id = Resource::put_restriction_group($_GET ["id"], strtolower($_GET ["groupname"]));

					if ($id) {
						$json = array (
								'status' => 'ok',
								'id' => $id
						);
					} else {
						$json = array (
								'status' => 'error',
								'msg' => 'Cannot add group to restriction'
						);
					}
				}
				else{
					$json = array (
							'status' => 'error',
							'msg' => 'User is unknown'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'delete_group_member' :
			if (isset ( $_GET ["id"] ) && isset ( $_GET ["username"] ) ) {

				$members = Resource::get_members_by_username( strtolower($_GET ["username"]));

				if($members){
					$members = Resource::delete_group_member($_GET ["id"], strtolower($_GET ["username"]));

					if ($members) {
						$json = array (
								'status' => 'ok',
								'members' => $members
						);
					} else {
						$json = array (
								'status' => 'error',
								'msg' => 'Cannot remove user from group'
						);
					}
				}
				else{
					$json = array (
							'status' => 'error',
							'msg' => 'User is unknown'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'delete_restriction_member' :
			if (isset ( $_GET ["id"] ) && isset ( $_GET ["username"] ) ) {

				$members = Resource::get_members_by_username( strtolower($_GET ["username"]));

				if($members){
					$members = Resource::delete_restriction_member($_GET ["id"], strtolower($_GET ["username"]));

					if ($members) {
						$json = array (
								'status' => 'ok',
								'members' => $members
						);
					} else {
						$json = array (
								'status' => 'error',
								'msg' => 'Cannot remove user from restriction'
						);
					}
				}
				else{
					$json = array (
							'status' => 'error',
							'msg' => 'User is unknown'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'delete_restriction_group' :
			if (isset ( $_GET ["id"] ) && isset ( $_GET ["groupname"] ) ) {

				$groups = Resource::get_groups_by_groupname( strtolower($_GET ["groupname"]));

				if($groups){
					$groups = Resource::delete_restriction_group($_GET ["id"], strtolower($_GET ["groupname"]));

					if (true) {
						$json = array (
								'status' => 'ok',
								'groups' => $groups
						);
					} else {
						$json = array (
								'status' => 'error',
								'msg' => 'Cannot remove group from restriction'
						);
					}
				}
				else{
					$json = array (
							'status' => 'error',
							'msg' => 'Group is unknown'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'delete_group' :
			if (isset ( $_GET ["id"] )) {

				$groupname = Resource::delete_group ( $_GET ["id"] );

				if ($groupname) {

					foreach(Resource::get_restriction_all_id () as $id){
						Resource::delete_restriction_group($id, $groupname);
					}

					$json = array (
							'status' => 'ok'
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Group not found'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'ID is missing'
				);
			}
			break;

		case 'delete_restriction' :
			if (isset ( $_GET ["id"] )) {
					
				if (Resource::delete_restriction ( $_GET ["id"] )) {
					$json = array (
							'status' => 'ok'
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Restriction not found'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'ID is missing'
				);
			}
			break;
				
		case 'get_volume' :
			if (isset ( $_GET ["username"] ) && isset ( $_GET ["start_date"] )) {
					
				if(isset ( $_GET ["end_date"] )){

					$volume = Resource::get_volume($_GET ["username"], $_GET ["start_date"],$_GET ["end_date"] );
				}else{
					$volume = Resource::get_volume($_GET ["username"], $_GET ["start_date"]);
				}
					
					
					
				if ($volume) {
					$json = array (
							'status' => 'ok',
							'volume' => $volume
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot get volume'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'get_quota' :
			if (isset ( $_GET ["username"] ) && isset ( $_GET ["start_date"] )) {
					
				if(isset ( $_GET ["end_date"] )){

					$qupta_time = Resource::get_quota($_GET ["username"], $_GET ["start_date"], $_GET ["end_date"] );
				}else{
					$qupta_time = Resource::get_quota($_GET ["username"], $_GET ["start_date"]);
				}
					
					
					
				if ($qupta_time) {
					$json = array (
							'status' => 'ok',
							'volume' => $qupta_time
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot get quota time'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'update_index_to_member' :
			if (isset ( $_GET ["username"] ) && isset ( $_GET ["current_index"] )) {
					
				$members = Resource::get_members_by_username ( $_GET ["username"] );
				$m = null;
				foreach ( $members as $member ) {
					$m = $member;
					break;
				}

					
				if ($m) {
						
					$id = Resource::update_member($m, 'current_index', $_GET ["current_index"]);
					if($id){
						$json = array (
								'status' => 'ok',
								'id' => $id
						);
					}else{
						$json = array (
								'status' => 'error',
								'msg' => 'Cannot update index #1'
						);
					}
					$json = array (
							'status' => 'ok',
							'id' => $id
					);
				} else {
					$json = array (
							'status' => 'error',
							'msg' => 'Cannot update index #2'
					);
				}
			} else {
				$json = array (
						'status' => 'error',
						'msg' => 'Parameters are missing'
				);
			}
			break;

		case 'get_restrictions_by_username' :
			if (isset ( $_GET ["username"] )) {
					
				$members = Resource::get_members_by_username( strtolower($_GET ["username"]));

				if($members){
					$restrictions = Resource::get_restrictions_by_username($_GET ["username"]);
						
					if ($restrictions) {
						$formated_restrictions = array();
						foreach ($restrictions as $restriction) {
							$formated_restrictions[] = $restriction->export();
						}

						$json = array (
								'status' => 'ok',
								'restrictions' => $formated_restrictions
						);
					} else {
						$json = array (
								'status' => 'error',
								'msg' => 'Cannot restrictions'
						);
					}
				}
				else{
					$json = array (
							'status' => 'error',
							'msg' => 'User is unknown'
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
