<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

class dataConnection
{
    private static $dbconn;
    private static $dblink=null;

    public static function connect()
    {
        global $DBCFG;
        //# Move this to config soon!
        $host=$DBCFG['host']; // Host name
        $username=$DBCFG['username']; // Mysql username
        $password=$DBCFG['password']; // Mysql password
        $db_name=$DBCFG['db_name']; // Database name
        self::$dblink = mysqli_connect("$host", "$username", "$password", $db_name)or die("cannot connect");
    }

    public static function runQuery($query)
    {
        global $CFG;
        if((isset($CFG['db_debug_log']))&&($CFG['db_debug_log']==1))
        {
            file_put_contents('db_debug_log.txt', time().': '.$query.PHP_EOL , FILE_APPEND | LOCK_EX);
        }
        if(self::$dblink==null)
            dataConnection::connect();
        $result = mysqli_query(self::$dblink, $query);
        if (!$result)
        {
            $message  = 'Invalid query: ' . mysqli_error(self::$dblink) . "\n";
            $message .= 'Whole query: ' . $query;
            die($message);
        }
        if($result===true)
            $output = true;
        else
        {
            $output = array();
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
            {
               $output[] = $row;
            }
        }
        return $output;
    }

    public static function close()
    {
        if(self::$dblink!=null)
            mysqli_close(self::$dblink);
        self::$dblink = null;
    }

    public static function safe($in)
    {
		if (self::$dblink==NULL)
    	{
	    	dataConnection::connect();
		}
	  	return mysqli_real_escape_string(self::$dblink, $in);
	}

	public static function db2date($in)
	{
	    list($y,$m,$d) = explode("-",$in);
	    return mktime(0,0,0,$m,$d,$y);
	}

	public static function date2db($in)
	{
	    return strftime("%Y-%m-%d", $in);
	}

	public static function db2time($in)
	{
        if(strlen($in)==0)
            return 0;
	    list($dt, $ti) = explode(" ",$in);
	    list($y,$m,$d) = explode("-",$dt);
	    list($hh,$mm,$ss) = explode(":",$ti);
	    return mktime($hh,$mm,$ss,$m,$d,$y);
	}

	public static function time2db($in)
	{
	    return strftime("%Y-%m-%d %H:%M:%S", $in);
	}

};




?>
