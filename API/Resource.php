<?php 

//load DAO
require_once('MemberDAO.php');
require_once('LogDAO.php');

class Resource{
	/*
	* Resources corresponding to member
	*/
	public static function put_member($data){
		return Model_Member::put($data);
	}

	public static function update_member($data){
		return Model_Member::put($data);
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

	public static function get_log_all($order, $limit){
		return Model_Log::getAll($order, $limit);
	}

	public static function delete_log($id){
		return Model_Log::del($id);
	}
	public static function get_log_newer_than($time, $host, $username){
		return Model_Log::getNewerThan($time, $host, $username);
	}
}
?>