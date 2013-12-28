#!/usr/bin/php
<?php
require_once 'CallAPI.php';

/**
 * LogReader Class
 * Read squid log file (access.log), get the fist line, store it to DB and finaly delete it (2nd line became the 1st one ans so on..).
 *
 * @author Gent
 *        
 */
class LogReader {
	private static $marge_timeout = 300.0; // in sec (number type : double)
	private static $caplsul_timeout = 40.0; // in sec (number type : double)
	private static $uri_pid_log = "/var/www/SquidyBaby/tool/log/pid.log";
	private static $file = "/var/log/squid3/access.log"; // Log file location
	private $sizebyte = 0;
	private $size = 0;
	private $username;
	private static $instance = NULL;
	static private function getInstance($username) {
		if (self::$instance === NULL) {
			self::$instance = new LogReader ( $username );
		}
		return self::$instance;
	}
	/**
	 * Default constructer
	 *
	 * @param
	 *        	$username
	 */
	private function __construct($username) {
		$this->username = $username;
		$this->emptyFile ( $username );
	}
	static public function start($username = NULL) {
		// main --
		$pid = self::readPID ( self::$uri_pid_log );
		if (! file_exists ( "/proc/$pid" )) {
			// process with a pid = $pid is running
			if (pcntl_fork () === 0) {
				self::writePID ( self::$uri_pid_log, posix_getpid () );
				// parent
				$read = LogReader::getInstance ( $username );
				$read->run ();
			}
		}
	}
	
	/**
	 * Execute the algo
	 */
	public function run() {
		if (self::$file) {
			$this->follow ( self::$file );
		}
	}
	
	/*
	 *
	 */
	/**
	 * Read fisrt line on $file, store it on DataBase, delete it from $file 2nd line become 1st and so on till $file has no more line...
	 *
	 * @param
	 *        	Logger file name : $file
	 */
	private function follow($file) {
		$index_array= array();
		$last_index = array (
				"i" => 1,
				"time" => null 
		);
		$isFirst = true;
		
		while ( true ) {
			if (($d = $this->read_and_delete_first_line ( $file )) != null) {
				$line_array = explode ( " ", $this->trimUltime ( $d ) );
				
				// TODO : +other restrictions
				if ($line_array [7] != "-") { // if it's an activ user
					/*
					 * echo '<pre>'; print_r($line_array); echo '</pre>';
					 */
					$Cache_Result_Codes = split("/", $line_array [3] );
					//Note, TCP_ refers to requests on the HTTP port (3128).
					$TCP_codes = $Cache_Result_Codes[0];
					
					$logDataTest = array (
							"action" => "get_log_newer_than",
							"time" => ($line_array [0] - self::$caplsul_timeout), // 60 = (1 minutes)
							"host" => parse_url ( $line_array [6] )['host'],
							"username" => $line_array [7],
							"TCP_codes" => $TCP_codes
					);
					
					// if log is in DB newer than n minuts update
					// else create new
					$logTester = CallAPI::sample ( $logDataTest );
					
					if ($logTester->status === "ok") {
						// update
						$logData = array (
								"action" => "update_log",
								"id" => $logTester->log->id,
								"bytes" => $line_array [4] + $logTester->log->bytes 
						);
					} else {
						// empty => create
						//init
						$user = $line_array [7];
						if($isFirst){
							$index_array[$user] = $last_index;
							
						}else{
							
							//print_r($index_array);
							//echo $line_array [0]."\n";
							if(($index_array[$user]["time"] + self::$marge_timeout) <= $line_array [0]){
								$index_array[$user]["i"]++;
							}		
						}
						
						$index_array[$user]["time"] = $line_array [0]; //last time
						
						$logData = array (
								"action" => "put_log",
								"time" => $line_array [0],
								"TCP_codes" => $TCP_codes,
								"remotehost" => $line_array [2],
								"bytes" => $line_array [4],
								"url" => parse_url ( $line_array [6] )['host'],
								"username" => $user,
								"indexID" => $index_array[$user]["i"] 
						);
						$isFirst = false;
						
					}
					
					CallAPI::sample ( $logData );
				}
			}
			usleep ( 100 );
		}
	}
	/**
	 *
	 * @param File $filename        	
	 * @return NULL String to store)
	 */
	public function read_and_delete_first_line($filename) {
		$returnLine = null;
		
		try {
			
			$file = new SplFileObject ( $filename, "r+" );
			
			if ($file->flock ( LOCK_EX )) { // verrou exclusif
				if ($file->eof ()) {
					return $returnLine;
				}
				
				// Rewind to first line
				$file->rewind ();
				$lines = array ();
				
				$k = 0;
				
				foreach ( $file as $line ) {
					array_push ( $lines, $line );
					$k ++;
				}
				
				// Rewind to first line
				$file->rewind ();
				$returnLine = $file->current (); // first line
				
				$linesIterator = new ArrayIterator ( $lines );
				// empty the file
				$this->emptyFile ( $filename );
				
				$file->rewind ();
				
				if ($k > 1) {
					foreach ( $l = new LimitIterator ( $linesIterator, 1 ) as $line ) {
						$file->fwrite ( $line );
					}
				}
				$file->flock ( LOCK_UN ); // libère le verrou
			}
			// echo $returnLine;
			return $returnLine;
		} catch ( Exception $exc ) {
			$file->flock ( LOCK_UN );
			// echo $exc->getTraceAsString ();
			return null;
		}
	}
	
	/**
	 * Empty the file
	 *
	 * @param
	 *        	File name $file
	 */
	public function emptyFile($file) {
		$f = @fopen ( $file, "r+" );
		if ($f !== false) {
			ftruncate ( $f, 0 );
			fclose ( $f );
			// remet la lecture depuis le debut
			$this->size = 0;
		}
	}
	
	/**
	 *
	 * @param string $chaine        	
	 * @return string
	 */
	private function trimUltime($chaine) {
		$chaine = trim ( $chaine );
		$chaine = str_replace ( "\t", " ", $chaine );
		$chaine = eregi_replace ( "[ ]+", " ", $chaine );
		return $chaine;
	}
	/**
	 *
	 * @param
	 *        	Number in byte $bytes
	 * @return string
	 */
	private function formatSizeUnits($bytes) {
		if ($bytes >= 1073741824) {
			$bytes = number_format ( $bytes / 1073741824, 2 ) . ' GB';
		} elseif ($bytes >= 1048576) {
			$bytes = number_format ( $bytes / 1048576, 2 ) . ' MB';
		} elseif ($bytes >= 1024) {
			$bytes = number_format ( $bytes / 1024, 2 ) . ' KB';
		} elseif ($bytes > 1) {
			$bytes = $bytes . ' bytes';
		} elseif ($bytes == 1) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}
		
		return $bytes;
	}
	/**
	 *
	 * @param TimpeStamp $start_time        	
	 * @param TimpeStamp $end_time        	
	 * @param string $std_format
	 *        	(optional)
	 * @return string
	 */
	private function timerFormat($start_time, $end_time, $std_format = false) {
		$total_time = $end_time - $start_time;
		$days = floor ( $total_time / 86400 );
		$hours = floor ( $total_time / 3600 );
		$minutes = intval ( ($total_time / 60) % 60 );
		$seconds = intval ( $total_time % 60 );
		$results = "";
		if ($std_format == false) {
			if ($days > 0)
				$results .= $days . (($days > 1) ? " days " : " day ");
			if ($hours > 0)
				$results .= $hours . (($hours > 1) ? " hours " : " hour ");
			if ($minutes > 0)
				$results .= $minutes . (($minutes > 1) ? " minutes " : " minute ");
			if ($seconds > 0)
				$results .= $seconds . (($seconds > 1) ? " seconds " : " second ");
		} else {
			if ($days > 0)
				$results = $days . (($days > 1) ? " days " : " day ");
			$results = sprintf ( "%s%02d:%02d:%02d", $results, $hours, $minutes, $seconds );
		}
		return $results;
	}
	public function getSizebyte() {
		return $this->sizebyte;
	}
	
	// aux functions
	/**
	 *
	 * @param $filename to
	 *        	write
	 * @param
	 *        	number of $pid
	 */
	static private function writePID($filename, $pid) {
		$fh = fopen ( $filename, 'r+' );
		if ($fh)
			fseek ( $fh, 0 );
		else
			return;
		fwrite ( $fh, $pid );
		fclose ( $fh );
	}
	
	/**
	 *
	 * @param $filename to
	 *        	read
	 * @return $pid = 0 if $filename redeable else number of active PID
	 */
	static private function readPID($filename) {
		$pid = 0;
		$fh = fopen ( $filename, 'r+' );
		if ($d = fgets ( $fh )) {
			$pid = $d;
		}
		fclose ( $fh );
		return $pid;
	}
}

// main

//$reader = LogReader::start ();

?>