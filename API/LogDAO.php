 <?php
	date_default_timezone_set ( 'Europe/Paris' );
	class Model_Log extends RedBean_SimpleModel {
		
		// Table name
		public static $table = 'log';
		
		/*
		 * static CRUD methods. Use Model_Member::method() to call CRUD
		 */
		
		// Create or update a log
		public static function put($data) {
			$id = false;
			
			// create and set the log
			$log = R::dispense ( self::$table );
			foreach ( $data as $key => $value ) {
				$log->setAttr ( $key, $value );
			}
			
			// do transaction
			R::begin ();
			try {
				$id = R::store ( $log );
				R::commit ();
			} catch ( Exception $e ) {
				R::rollback ();
				$id = false;
			}
			
			// if success return valid $id else return false
			return $id;
		}
		
		// Delete a log
		public static function del($id) {
			$log = Model_Log::getById ( $id );
			if ($log == null)
				return false;
			R::trash ( $log );
			return true;
		}
		
		// Get a log
		public static function getById($id) {
			$log = R::load ( self::$table, $id );
			if (! $log->id) {
				return false;
			}
			return $log;
		}
		
		// Get all logs
		public static function getAll($order, $limit) {
			$logs = R::findAll ( self::$table, ' ORDER BY ' . $order . ' LIMIT ' . $limit );
			if (empty ( $logs ))
				return false;
			else
				return $logs;
		}
		
		// Get a log
		public static function getNewerThan($time, $host, $username) {
			$log = R::findOne ( self::$table, ' time >= :time AND username = :username AND url = :url ', array (
					'time' => $time,
					':username' => $username,
					':url' => $host 
			) );
			if (empty ( $log ))
				return false;
			else
				return $log;
		}
		
		// get volume downloaded by username and period
		public static function getVolume($username, $start_date, $end_date = null) {
			//
			if ($username === "*")
				$moreUser = "";
			else
				$moreUser = "AND username=:username";
				
				//
			
			$sql = 'SELECT username, sum( bytes ) AS val, :start_date as start_date, :end_date as end_date FROM `log` WHERE date( FROM_UNIXTIME( time ) ) >= date( :start_date ) AND  date( FROM_UNIXTIME( time ) ) <= date( :end_date )  ' . $moreUser . ' AND tcp_codes NOT LIKE "%DENIED%"  GROUP BY username';
			
			$end_date = ($end_date == null ? date ( 'Y-m-d' ) : $end_date);
			
			$bindings = array (
					// ':table' => self::$table,
					
					':end_date' => $end_date,
					':start_date' => $start_date 
			);
			if ($username === "*")
				$volume = R::getAll ( $sql, $bindings );
			else {
				$bindings [':username'] = $username;
				$volume = R::getRow ( $sql, $bindings );
			}
			return $volume;
		}
		
		// get volume downloaded by username and period
		public static function getQuota($username, $start_date, $end_date = null) {
			//
			if ($username === "*")
				$moreUser = "";
			else
				$moreUser = "AND username=:username";
				
				//
			
			$sql = 'SELECT l.username , sum(l.time) as quota_time from
  (SELECT  (MAX(time) -MIN(time)) AS time, username, index_id AS groupID FROM log WHERE date( FROM_UNIXTIME( time ) ) >= date( :start_date ) AND  date( FROM_UNIXTIME( time ) ) <= date( :end_date )  ' . $moreUser . ' AND tcp_codes NOT LIKE "%DENIED%" GROUP BY username, index_id ORDER BY index_id)
    as l
  group by l.username';
			
			$end_date = ($end_date == null ? date ( 'Y-m-d' ) : $end_date);
			
			$bindings = array (
					// ':table' => self::$table,
					
					':end_date' => $end_date,
					':start_date' => $start_date 
			);
			if ($username === "*")
				$q_time = R::getAll ( $sql, $bindings );
			else {
				$bindings [':username'] = $username;
				$q_time = R::getRow ( $sql, $bindings );
			}
			return $q_time;
		}
		
		// Observers
		public function open() {
		}
		public function dispense() {
		}
		public function update() {
		}
		
		// notify new logs to clients
		public function after_update() {
			$data = $this->bean->export ();
			$data_string = json_encode ( $data );
			
			$curl = curl_init ();
			
			curl_setopt ( $curl, CURLOPT_URL, "http://localhost:8000/putlog" );
			curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, "POST" );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data_string );
			curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $curl, CURLOPT_HTTPHEADER, array (
					'Content-Type: application/json',
					'Content-Length: ' . strlen ( $data_string ) 
			) );
			
			curl_exec ( $curl );
		}
		public function delete() {
		}
		public function after_delete() {
		}
	}
	?>