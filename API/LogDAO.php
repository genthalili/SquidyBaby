 <?php
date_default_timezone_set('UTC');
 
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
    public static function getNewerThan($time, $host, $username, $TCP_codes){
    	$log = R::findOne(
    			self::$table,
    			' time >= :time AND username = :username AND url = :url AND TCP_codes = :TCP_codes LIMIT 1',
    			array(
    					':time' => $time,
    					':username' => $username,
    					':url' => $host,
    					':TCP_codes' => $TCP_codes
    			)
    	);
    	if(empty($log)) return false;
    	else return $log;
    }
    
    //get volume downloaded by username and period
    public static function getVolume($username, $start_date, $end_date = null) {
    	//
    	$sql = 'SELECT username, sum( bytes ) AS val, :start_date as start_date, :end_date as end_date FROM `log` WHERE date( FROM_UNIXTIME( time ) ) >= date( :start_date ) AND  date( FROM_UNIXTIME( time ) ) <= date( :end_date ) AND username=:username AND tcp_codes NOT LIKE "%DENIED%" GROUP BY username LIMIT 1';
    
    	
    	$end_date = ( $end_date == null ? date('Y-m-d') : $end_date );
    		
    	
    	$volume = R::getAll($sql,
    			array(
    				//':table' => self::$table,
    				':username' => $username,
    				':end_date' => $end_date,
    				':start_date' => $start_date
    	)
    	);
    	//$volume = 12;
    	
    	//array_push('sd', $volume);
    	
    	return $volume;
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