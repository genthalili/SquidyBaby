 <?php
 class Model_Group extends RedBean_SimpleModel {

 	//Table name
 	public static $table = 'group';


 	/*
 	*	static CRUD methods. Use Model_Member::method() to call CRUD
	*/

 	//Create or update a group
 	public static function put($data){
 		$id = false;

        $group = R::findOne(
            self::$table,
            ' groupname = :groupname',
            array( 
                ':groupname' => $data['groupname']
            ) 
        );
        if(!empty($group)) return false;

 		//create and set the group
 		$group = R::dispense(self::$table);
 		foreach ($data as $key => $value) {
            $group->setAttr($key,$value); 
 		}

 		//do transaction
 		R::begin();
	    try{
			$id = R::store($group);
			R::commit();
	    }
	    catch(Exception $e) {
			R::rollback();
			$id = false;
	    }

	    //if success return valid $id else return false
		return $id;
 	}

    //Add member to group
    public static function putMember($id, $username){
        $group = Model_Group::getById($id);
        if($group == null) return false;

        $arr = explode(" ", $group->members);
        if (in_array($username, $arr)) {
            return false;
        }

        $group->setAttr('members', $username . ' ' . $group->members); 

        $id = false;
        //do transaction
        R::begin();
        try{
            $id = R::store($group);
            R::commit();
        }
        catch(Exception $e) {
            R::rollback();
            $id = false;
        }

        //if success return valid $id else return false
        return $id;
    }

    //Delete a group
    public static function deleteMember($id, $username){

        $members = false;

        $group = Model_Group::getById($id);
        if($group == null) return false;

        $arr = explode(" ", $group->members);
        if (in_array($username, $arr)) {
            $i = array_search($username, $arr);
            unset($arr[$i]);
        }
        else{
            return false;
        }

        $join = implode(" ", $arr);

        $group->setAttr('members', $join); 

        //do transaction
        R::begin();
        try{
            $id = R::store($group);
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
 	public static function del($id){
 		$group = Model_Group::getById($id);
 		if($group == null) return false;
    	R::trash($group);
    	return true;
 	}

 	//Get a group
 	public static function getById($id){
 		$group = R::load(self::$table, $id);
		if (!$group->id) { return false; } 
		return $group;
 	}

    public static function getAll($order, $limit){
        $groups = R::findAll(self::$table,
        ' ORDER BY '. $order .' LIMIT '. $limit);
        if(empty($groups)) return false;
        else return $groups;
    }

    public static function getAllId(){
        $groups = R::findAll(self::$table);
        if(empty($groups)) return false;
        else {
            $arr = array();
            foreach ($groups as $group) {
                $arr[] = $group->export()['id'];
            }
            return $arr;
        }
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