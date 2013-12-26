#!/usr/bin/php
<?php
/*
 * Lecture du fichier log(access) du squid
 * recueper chaque ligne (la 1ere) et la stock dans la base de donnée
 * ensuite l'efface et la 2eme ligne la 1ere
 * 
 */

require_once 'CallAPI.php';

class LogReader {

    private $file = "/var/log/squid3/access.log";
    private $sizebyte = 0;
    private $size = 0;
    private $username;
    private static $instance = NULL;

    static public function getInstance($username) {
        if (self::$instance === NULL) {
            self::$instance = new LogReader($username);
        }
        return self::$instance;
    }

    public function __construct($username) {
        $this->username = $username;
        //$this->emptyFile($username);
    }

    public function run() {
        if ($this->file) {
            $this->follow($this->file);
        }
    }

//    private function follow($file) {
//
//
//        $firstDateTime = 0.0;
//        $lastDateTime = 0.0;
//        while (true) {
//
//            clearstatcache();
//            $currentSize = filesize($file);
//            if ($this->size == $currentSize) {
//                usleep(100);
//                continue;
//            }
//
//            $fh = fopen($file, "r");
//            fseek($fh, $this->size);
//            $i = 0;
//
//
//
//            while ($d = fgets($fh)) {
//
//
//                $line_array = explode(" ", $this->trimUltime($d));
//
//                if ($this->size == 0 and $i == 0) {
//                    $firstDateTime = $line_array[0];
//                } else {
//                    $lastDateTime = $line_array[0];
//                }
//
//                if ($line_array[7] == $this->username) {
//
//                    echo '<pre>';
//                    print_r($line_array);
//                    echo '</pre>';
//
//                    //volume calculator
//                    $this->sizebyte = $this->sizebyte + intval($line_array[4]);
//                    // echo $this->formatSizeUnits($sizebyte);
//                    echo "\nVolume total : " . $this->formatSizeUnits($this->sizebyte);
//                    echo "\nTEmp: " . $this->timerFormat($firstDateTime, $lastDateTime);
//                }
//
//                //exit;
//                $i++;
//            }
//
//
//            fclose($fh);
//            $this->size = $currentSize;
//        }
//    }



    /*
     * Read fisrt line on $file, store it on DataBase, delete it from $file
     * 2nd line become 1st and so on till $file has no more line...
     */
    private function follow($file) {

        while (true) {
            if (($d = $this->read_and_delete_first_line($file)) != null) {
                $line_array = explode(" ", $this->trimUltime($d));

                //TODO : +other restrictions
                if ($line_array[7] != "-") { //if it's an activ user
					/*
                    echo '<pre>';
                    print_r($line_array);
                    echo '</pre>';
                    */
                	
                	$logDataTest = array(
                		"action" => "get_log_newer_than",
                		"time" => ($line_array[0]-120),
                		"host" => parse_url($line_array[6])['host'],
                		"username" => $line_array[7]
                	);
                	
                	
                	
                	//if log is in DB newer than 2 minuts update
                	//else create new
                	$logTester = CallAPI::sample($logDataTest);
                	
                	if($logTester){
                		// update
                		$logData = array(
                				"action" => "update_log",
                				"id" => $logTester->log->id,
                				"bytes" => $line_array[4]+$logTester->log->bytes
                		);
                		
                	}else{
                		//empty => create
                		$logData = array(
                				"action" => "put_log",
                				"time" => $line_array[0],
                				"remotehost" => $line_array[2],
                				"bytes" => $line_array[4],
                				"url" => $line_array[6],
                				"username" => $line_array[7]
                		);
                	}
                	
                	echo '<pre>';
                	print_r(CallAPI::sample ( $logData ));
                	
                	
                	
                	
                	
                }
            }
            usleep(100);
        }
    }

    public function read_and_delete_first_line($filename) {
        $returnLine = null;

        try {

            $file = new SplFileObject($filename, "r+");

            if ($file->flock(LOCK_EX)) { // verrou exclusif
                if ($file->eof()) {
                    return $returnLine;
                }

                // Rewind to first line
                $file->rewind();
                $lines = array();

                $k = 0;

                foreach ($file as $line) {
                    array_push($lines, $line);
                    $k++;
                }

                // Rewind to first line
                $file->rewind();
                $returnLine = $file->current(); //first line

                $linesIterator = new ArrayIterator($lines);
                //empty the file
                $this->emptyFile($filename);

                $file->rewind();

                if ($k > 1) {
                    foreach ($l = new LimitIterator($linesIterator, 1) as $line) {
                        $file->fwrite($line);
                    }
                }
                $file->flock(LOCK_UN);   // libère le verrou
            }
            //echo $returnLine;
            return $returnLine;
        } catch (Exception $exc) {
            $file->flock(LOCK_UN);
            echo $exc->getTraceAsString();
            return null;
        }
    }

    public function emptyFile($file) {
        $f = @fopen($file, "r+");
        if ($f !== false) {
            ftruncate($f, 0);
            fclose($f);
            //remet la lecture depuis le debut
            $this->size = 0;
        }
    }

    private function trimUltime($chaine) {
        $chaine = trim($chaine);
        $chaine = str_replace("\t", " ", $chaine);
        $chaine = eregi_replace("[ ]+", " ", $chaine);
        return $chaine;
    }

    private function formatSizeUnits($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    private function timerFormat($start_time, $end_time, $std_format = false) {
        $total_time = $end_time - $start_time;
        $days = floor($total_time / 86400);
        $hours = floor($total_time / 3600);
        $minutes = intval(($total_time / 60) % 60);
        $seconds = intval($total_time % 60);
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
        }
        else {
            if ($days > 0)
                $results = $days . (($days > 1) ? " days " : " day ");
            $results = sprintf("%s%02d:%02d:%02d", $results, $hours, $minutes, $seconds);
        }
        return $results;
    }

    public function getSizebyte() {
        return $this->sizebyte;
    }

}

//main --
$uti_pid_log = "/var/www/squid/log/pid.log";
$pid = readPID($uti_pid_log);
if (!file_exists("/proc/$pid")) {
    //process with a pid = $pid is running
    if (pcntl_fork() === 0) {
        writePID($uti_pid_log, posix_getpid());
        //parent
        $read = LogReader::getInstance("gent");
        $read->run();
        
    }
}

//aux functions
function writePID($filename, $pid) {
    $fh = fopen($filename, 'r+');
    if ($fh)
        fseek($fh, 0);
    else
        return;
    fwrite($fh, $pid);
    fclose($fh);
}

function readPID($filename) {
    $pid = 0;
    $fh = fopen($filename, 'r+');
    if ($d = fgets($fh)) {
        $pid = $d;
    }
    fclose($fh);
    return $pid;
}


?>