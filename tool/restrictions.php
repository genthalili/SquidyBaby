#!/usr/bin/php
<?php
/*
 * global
 */
$TIME_MARGE = 5; //in minuts


if (!defined("STDIN")) {
    define("STDIN", fopen("php://stdin", "r"));
}
date_default_timezone_set('Europe/Paris');
$temp = array();


while ($input = fgets(STDIN)) {
    // Split the output (space delimited) from squid into an array.
    //external_acl_type myhelper %SRC %URI %LOGIN /usr/bin/php /var/www/squid/restrictions.php
    //declarations, inits
    $clientIP = null; //cleint local IP ex: 192.168.0.5
    $url = null; // visited page full url ex: exemple.com/exemple.php?foo=1&...
    $ip = null; //from $url
    $username = null; //client username  

    try {
        $temp = null;
        $temp = split(' ', $input);
        if (sizeof($temp) == 3) {
            $clientIP = $temp[0];
            $url = $temp[1];
            if (array_key_exists('host', parse_url($url))) {
                $ip = gethostbyname(parse_url($url)['host']);
            }
            $username = $temp[2];
        }
        $ERR_MESSAGE = ""; //ERROR MESSAGE note: add %o to error page. For error pages in french use /usr/share/errors/fr/ERR_ACCESS_DENIED

        /*
         * verify : if $username has restrictions
         * if $clientIP has restrictions
         * if $hostname has restrictions
         * if $url has restrictions
         * etc...
         * 
         * result: 
         * if true, result should be  OK [message=Your%20restriction...]
         * else result should be  ERR
         * rsults must end with "\n"
         * 
         * type of restrictions:
         *  - quota time per day/week
         *  - downloaded volume per day/week
         */
        
        //CallAPI class <-----

        $ERR_MESSAGE = $clientIP . " " . $url . " " . $ip . " " . $username;
        //fwrite(STDOUT, "OK message=" . rawurlencode($ERR_MESSAGE) . "\n"); //deny access

        fwrite(STDOUT, "ERR\n"); //allow access


        //test
//        $myFile = "/var/www/squid/404.txt";
//
//        $fh = fopen($myFile, 'a');
//        $stringData = date('d/m/Y H:i:s', time()) . " " . $username;
//        fwrite($fh, $stringData);
//        fclose($fh);
        

        //TEST
       
    } catch (Exception $exc) {
        fwrite(STDOUT, "BH\n");
    }
}

/*
 * add a number of $bytes to a specific $username for the day in DB
 * before adding tests if new day
 */

function addDownloadedVolumeForTheDay($username, $bytes) {
    //TODO
    //++modif timestrap in lastmodif column
}

/*
 * add a number of $bytes to a specific $username for the week in DB
 * before adding tests if new week
 */

function addDownloadedVolumeForTheWeek($username, $bytes) {
    //TODO
    //++modif timestrap in lastmodif column
}

/*
 * get actual downloaded volume by $username for this day
 * return volume in bytes
 */

function getDownloadedVolumeForTheDay($username) {
    //TODO
}

/*
 * get actual downloaded volume by $username for this week
 * return volume in bytes
 */

function getDownloadedVolumeForTheWeek($username) {
    //TODO
}

/*
 * get quota time FOR THE DAY by $username
 * return total quota time for special for the day (in minuts)
 */

function getQuotaTimeForTheDay($username) {
    //TODO

    return 0;
}

/*
 * get quota time FOR THE WEEK by $username from DB
 * return total quota time for the week (in minuts)
 */

function getQuotaTimeForTheWeek($username) {
    //TODO

    return 0;
}

/*
 * add a quota time to a specific user
 * before adding tests if not new day, else reset
 */

function addQuotaTimeForTheDay($username, $minuts) {
    //TODO
    //++modif timestrap in lastmodif column
    $date = new DateTime();

    echo $date->getTimestamp();
}

/*
 * add a quota time to a specific user
 * before adding tests if not new week, else reset
 */

function addQuotaTimeForTheWeek($username, $minuts) {
    //TODO
    //++modif timestrap in lastmodif column
    $date = new DateTime();
    echo $date->getTimestamp();
    
}
?>