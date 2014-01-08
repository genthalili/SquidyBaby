 <?php
 class Model_Member extends RedBean_SimpleModel {

 	//Table name
 	public static $table = 'member';


 	/*
 	*	static CRUD methods. Use Model_Member::method() to call CRUD
	*/

 	//Create or update a member
 	public static function put($data){
 		$id = false;

        $member = R::findOne(
            self::$table,
            ' username = :username',
            array( 
                ':username' => $data['username']
            ) 
        );
        if(!empty($member)) return false;

 		//create and set the member
 		$member = R::dispense(self::$table);
 		foreach ($data as $key => $value) {
 			$member->setAttr($key,$value); 
 		}

 		//do transaction
 		R::begin();
	    try{
			$id = R::store($member);
			R::commit();
	    }
	    catch(Exception $e) {
			R::rollback();
			$id = false;
	    }

	    //if success return valid $id else return false
		return $id;
 	}

    public static function updateMember($member, $key, $value){
        $id = false;

        $member->setAttr($key,$value);

        R::begin();
        try{
            $id = R::store($member);
            R::commit();
        }
        catch(Exception $e) {
            R::rollback();
            $id = false;
        }

        return $id;
    }

 	//Delete a member
 	public static function del($id){
 		$member = Model_Member::getById($id);
 		if($member == null) return false;

        $username = $member->username;
    	R::trash($member);
    	return $username;
 	}

 	//Get a member
 	public static function getById($id){
 		$member = R::load(self::$table, $id);
		if (!$member->id) { return false; } 
		return $member;
 	}

    public static function getMembersByUsername($username){
        $members = R::find(self::$table,' username like ? ', 
            array( $username )
        );
        return $members;
    }

 	//Get a member
 	public static function getByCredentials($username, $password){
 		$member = R::findOne(
 			self::$table,
        	' username = :username AND password = :password ',
        	array( 
                ':username' => $username, 
                ':password' => sha1($password)
            ) 
    	);
    	if(empty($member)) return false;
    	else return $member;
 	}

    public static function getAll($order, $limit){
        $members = R::findAll(self::$table,
        ' ORDER BY '. $order .' LIMIT '. $limit);
        if(empty($members)) return false;
        else return $members;
    }

 	//Observers
	public function open() {}
    public function dispense(){}
    public function update() {}
    public function after_update(){}
    public function delete() {}
    public function after_delete() {}
}
?>