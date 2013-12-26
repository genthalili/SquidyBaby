 <?php
 class Model_Log extends RedBean_SimpleModel {

 	//Table name
 	public static $table = 'log';


 	/*
 	*	static CRUD methods. Use Model_Member::method() to call CRUD
	*/

 	//Create or update a log
 	public static function put($data){
 		$id = false;

 		//create and set the log
 		$log = R::dispense(self::$table);
 		foreach ($data as $key => $value) {
 			$log->setAttr($key,$value); 
 		}

 		//do transaction
 		R::begin();
	    try{
			$id = R::store($log);
			R::commit();
	    }
	    catch(Exception $e) {
			R::rollback();
			$id = false;
	    }

	    //if success return valid $id else return false
		return $id;
 	}

 	//Delete a log
 	public static function del($id){
 		$log = Model_Log::getById($id);
 		if($log == null) return false;
    	R::trash($log);
    	return true;
 	}

 	//Get a log
 	public static function getById($id){
 		$log = R::load(self::$table, $id);
		if (!$log->id) { return false; } 
		return $log;
 	}

    //Get all logs
    public static function getAll($order, $limit){
        $logs = R::findAll(self::$table,
        ' ORDER BY '. $order .' LIMIT '. $limit);
        if(empty($logs)) return false;
        else return $logs;
    }

    //Get a log
    public static function getNewerThan($time, $host, $username){
    	$log = R::findOne(
    			self::$table,
    			' time >= :time AND username = :username AND url = :url ',
    			array(
    					'time' => $time,
    					':username' => $username,
    					':url' => $host
    			)
    	);
    	if(empty($log)) return false;
    	else return $log;
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