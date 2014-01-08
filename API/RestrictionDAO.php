 <?php
 class Model_Restriction extends RedBean_SimpleModel {

 	//Table name
 	public static $table = 'restriction';
    public static $tableGroup = 'group';


 	/*
 	*	static CRUD methods. Use Model_Member::method() to call CRUD
	*/

 	//Create or update a restriction
 	public static function put($data){
 		$id = false;

        $restriction = R::findOne(
            self::$table,
            ' resname = :resname',
            array( 
                ':resname' => $data['resname']
            ) 
        );
        if(!empty($restriction)) return false;

 		//create and set the restriction
 		$restriction = R::dispense(self::$table);
 		foreach ($data as $key => $value) {
            $restriction->setAttr($key,$value); 
 		}

 		//do transaction
 		R::begin();
	    try{
			$id = R::store($restriction);
			R::commit();
	    }
	    catch(Exception $e) {
			R::rollback();
			$id = false;
	    }

	    //if success return valid $id else return false
		return $id;
 	}

    //Add member to restriction
    public static function putMember($id, $username){
        $restriction = Model_Restriction::getById($id);
        if($restriction == null) return false;

        $arr = explode(" ", $restriction->members);
        if (in_array($username, $arr)) {
            return false;
        }

        $restriction->setAttr('members', $username . ' ' . $restriction->members); 

        $id = false;
        //do transaction
        R::begin();
        try{
            $id = R::store($restriction);
            R::commit();
        }
        catch(Exception $e) {
            R::rollback();
            $id = false;
        }

        //if success return valid $id else return false
        return $id;
    }

    //Add group to restriction
    public static function putGroup($id, $groupname){
        $restriction = Model_Restriction::getById($id);
        if($restriction == null) return false;

        $arr = explode(" ", $restriction->groups);
        if (in_array($groupname, $arr)) {
            return false;
        }

        $restriction->setAttr('groups', $groupname . ' ' . $restriction->groups); 

        $id = false;
        //do transaction
        R::begin();
        try{
            $id = R::store($restriction);
            R::commit();
        }
        catch(Exception $e) {
            R::rollback();
            $id = false;
        }

        //if success return valid $id else return false
        return $id;
    }

    //Delete a member
    public static function deleteMember($id, $username){

        $members = false;

        $restriction = Model_Restriction::getById($id);
        if($restriction == null) return false;

        $arr = explode(" ", $restriction->members);
        if (in_array($username, $arr)) {
            $i = array_search($username, $arr);
            unset($arr[$i]);
        }
        else{
            return false;
        }

        $join = implode(" ", $arr);

        $restriction->setAttr('members', $join); 

        //do transaction
        R::begin();
        try{
            $id = R::store($restriction);
            R::commit();
            if($id) $members = $join;
        }
        catch(Exception $e) {
            R::rollback();
            $members = false;
        }

        //if success return valid $id else return false
        return $members;
    }

    //Delete a group
    public static function deleteGroup($id, $groupname){

        $groups = false;

        $restriction = Model_Restriction::getById($id);
        if($restriction == null) return false;


        $arr = explode(" ", $restriction->groups);
        if (in_array($groupname, $arr)) {
            $i = array_search($groupname, $arr);
            unset($arr[$i]);
        }
        else{
            return false;
        }

      

        $join = implode(" ", $arr);

        $restriction->setAttr('groups', $join); 

        //do transaction
        R::begin();
        try{
            $id = R::store($restriction);
            R::commit();
            if($id) $groups = $join;
        }
        catch(Exception $e) {
            R::rollback();
            $groups = false;
        }

        //if success return valid $id else return false
        return $groups;
    }

 	//Delete a restriction
 	public static function del($id){
 		$restriction = Model_Restriction::getById($id);
 		if($restriction == null) return false;
    	R::trash($restriction);
    	return true;
 	}

 	//Get a restriction
 	public static function getById($id){
 		$restriction = R::load(self::$table, $id);
		if (!$restriction->id) { return false; } 
		return $restriction;
 	}

    public static function getAll($order, $limit){
        $restrictions = R::findAll(self::$table,
        ' ORDER BY '. $order .' LIMIT '. $limit);
        if(empty($restrictions)) return false;
        else return $restrictions;
    }

    public static function getAllId(){
        $restrictions = R::findAll(self::$table);
        if(empty($restrictions)) return false;
        else {
            $arr = array();
            foreach ($restrictions as $restriction) {
                $arr[] = $restriction->export()['id'];
            }
            return $arr;
        }
    }

    public static function getByUsername($username){
        $arr = array();
        $ids = array();
        $restrictions = R::findAll(self::$table);

        foreach ($restrictions as $restriction) {
            $members = explode(" ", $restriction->members);
            $groups = explode(" ", $restriction->groups);

            if (in_array($username, $members)) {
                $arr[] = $restriction;
                $ids[] = $restriction->id;
            }

            foreach ($groups as $groupname) {

                $groupsFound = R::find(self::$tableGroup,' groupname = ? ', 
                    array( $groupname )
                );

                foreach ($groupsFound as $groupFound) {
                    $membersFound = explode(" ", $groupFound->members);

                    foreach ($membersFound as $usernameFound) {
                        if($usernameFound == $username) $arr[] = $restriction;

                    }
                   
                }

            }

        }

        return array_unique($arr);
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