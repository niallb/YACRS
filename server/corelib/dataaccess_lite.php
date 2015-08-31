<?php

class dataConnection
{
    private static $dbconn=null;

    public static function connect()
    {
    	global $DBCFG;
        self::$dbconn = sqlite_open($DBCFG['file']);
    }

    public static function runQuery($query)
    {
        if(self::$dbconn==null)
            self::connect();
        $err = "";
        $result = sqlite_unbuffered_query(self::$dbconn, $query, SQLITE_ASSOC, $err);
        if ($err != "")
        {
            die($err);
        }
        $output = array();
        while ($row = sqlite_fetch_array($result))
        {
            $output[] = $row;
        }
        return $output;
    }

    public static function close()
    {
        if(self::$dbconn!=null)
            sqlite_close(self::$dbconn);
        self::$dbconn = null;
    }

    public static function safe($in)
    {
		if (self::$dbconn==NULL)
    	{
	    	dataConnection::connect();
		}
	  	return sqlite_escape_string($in);
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
