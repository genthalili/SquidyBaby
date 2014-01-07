<?php 

//load DAO
require_once('MemberDAO.php');
require_once('GroupDAO.php');
require_once('LogDAO.php');

class Resource{

	/*
	* Resources corresponding to group
	*/
	public static function put_group($data){
		return Model_Group::put($data);
	}

	public static function update_group($data){
		return Model_Group::put($data);
	}

	public static function get_group_by_id($id){
		return Model_Group::getById($id);
	}

	public static function get_group_all($order, $limit){
		return Model_Group::getAll($order, $limit);
	}

	public static function get_group_all_id(){
		return Model_Group::getAllId();
	}

	public static function put_group_member($id, $username){
		return Model_Group::putMember($id, $username);
	}

	public static function delete_group_member($id, $username){
		return Model_Group::deleteMember($id, $username);
	}

	public static function delete_group($id){
		return Model_Group::del($id);
	}


	/*
	* Resources corresponding to member
	*/
	public static function put_member($data){
		return Model_Member::put($data);
	}

	public static function update_member($member, $key, $value){
		return Model_Member::updateMember($member, $key, $value);
	}
	
	public static function get_member_all($order, $limit){
		return Model_Member::getAll($order, $limit);
	}

	public static function get_member_by_username($username){
		return Model_Member::getByUsername($username);
	}

	public static function get_member_by_id($id){
		return Model_Member::getById($id);
	}

	public static function get_member_by_credentials($username, $password){
		return Model_Member::getByCredentials($username, $password);
	}

	public static function delete_member($id){
		return Model_Member::del($id);
	}


	/*
	* Resources corresponding to log
	*/
	public static function put_log($data){
		return Model_Log::put($data);
	}

	public static function update_log($data){
		return Model_Log::put($data);
	}

	public static function get_log_by_id($id){
		return Model_Log::getById($id);
	}

	public static function delete_log($id){
		return Model_Log::del($id);
	}

	public static function get_log_newer_than($time, $host, $username, $TCP_codes){
		return Model_Log::getNewerThan($time, $host, $username, $TCP_codes);
	}
	public static function get_volume($username, $start_date, $end_date = null){
		return Model_Log::getVolume($username, $start_date, $end_date);
	}
	public static function get_quota($username, $start_date, $end_date = null){
		return Model_Log::getQuota($username, $start_date, $end_date);
	}
}
?>