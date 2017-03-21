<?php

function checkLogin($username, $password, &$error=false)
{
	global $CFG;
    if(strlen(trim($password))==0)
        return false;
    $error = false;
    $clrtime = time()+5; // For paranoid prevention of timing to narrow username/password guesses
	$cookiehash = $CFG['cookiehash'];
	$ldap_host = $CFG['ldaphost'];
	$ds = @ldap_connect($ldap_host);
   	if(!$ds)
    {
           $error = 'failed to contact LDAP server';
           return false;
    }
	$sr = @ldap_search($ds, $CFG['ldapcontext'], 'cn='.$username);
   	if(!$sr)
    {
           $error = 'failed to contact LDAP server';
           return false;
    }
    $entry = ldap_first_entry($ds, $sr);
	if($entry)
    {
        $user_dn = ldap_get_dn($ds, $entry);
		$ok = @ldap_bind( $ds, $user_dn, $password);
	    //ldap_free_result( $sr );
	    if($ok)
	    {
			$sr = ldap_search($ds, $CFG['ldapcontext'], 'cn='.$username);
			$count = ldap_count_entries( $ds, $sr );
			if($count>0)
			{
			    $records = ldap_get_entries($ds, $sr );
			    $record = $records[0];
                return uinfoFromGULDAP($record);
			}
			else
				$error = "No Identity vault entry found.<br/>";
			ldap_free_result( $sr );
	    }
	    else
	    {
            while($clrtime < time()) sleep(1); // Paranoid prevention of timing to narrow username/password guesses
	    	return false; //Incorrect password
	    }
    }
    else
    {
        while($clrtime < time()) sleep(1); // Paranoid prevention of timing to narrow username/password guesses
        return false; //Incorrect username
    }
}

function uinfoFromGULDAP($record)
{
    $uinfo = array();
    //echo '<pre>'; print_r($record); echo '</pre>';
    $uinfo['uname'] = $record['uid'][0];
    $uinfo['gn'] = $record['givenname'][0];
    $uinfo['sn'] = $record['sn'][0];
    if(isset($record['mail'][0]))
    	$uinfo['email'] = $record['mail'][0];
    elseif(isset($record['emailaddress'][0]))
    	$uinfo['email'] = $record['emailaddress'][0];
    else
        $uinfo['email'] = '';
    $uinfo['isAdmin'] = false;
    if(isset($record['homezipcode'][0]))
        $uinfo['category'] = $record['homezipcode'][0];
    elseif(strpos($record['dn'], 'ou=staff') !== false)
        $uinfo['category'] = 'staff';
    else
        $uinfo['category'] = 'guest';
    return $uinfo;
}

function checkLoginOld()
{
	global $CFG;
	$cookiehash = $CFG['cookiehash'];
	if((isset($_REQUEST['uname']))&&(isset($_REQUEST['pwd'])))
    {
		$iv_host = $CFG['ldaphost'];
		$iv_ds = ldap_connect( $iv_host );
		//$iv_r = ldap_bind( $iv_ds);
		$sr = ldap_search( $iv_ds, $CFG['ldapcontext'], "cn=".$_REQUEST['uname'],  array('cn', 'dn', 'mail', 'fullname', 'givenname', 'sn', 'uid', 'logindisabled') );
		//$sr = ldap_search( $iv_ds, $CFG['ldapcontext'], "cn=".$_REQUEST['uname'],  array('*') );
		$count = ldap_count_entries( $iv_ds, $sr );
		if($count>0)
		{
		    $ivrecord = ldap_get_entries( $iv_ds, $sr );
		    $iv = $ivrecord[0];
		    ldap_free_result( $sr );
            //echo '<pre>'; print_r($ivrecord);echo '</pre>';
			$erl = error_reporting(E_ERROR | E_PARSE);
            // Need to replace ldap_bind with ldap_compare because that's what Moodle uses...
            // $ok = ldap_bind( $iv_ds, $ivrecord[0]['dn'], $_REQUEST['pwd']) && strlen($_REQUEST['pwd']);
            $ok = ldap_compare( $iv_ds, $ivrecord[0]['dn'], 'userPassword', $_REQUEST['pwd']) && strlen($_REQUEST['pwd']);
			error_reporting($erl);
            if($ok)
            {
	        	//set cookie
                $userinfo = array('uname'=>$_REQUEST['uname'], 'gn'=>$iv['givenname'][0], 'sn'=>$iv['sn'][0], 'email'=>$iv['mail'][0]);
                $cookieinfo = base64_encode(serialize($userinfo));
                $cookie = implode('@', array($cookieinfo,time()+3600));
                $cookie = $cookie .'::'.md5($cookie.$cookiehash);
                setcookie('gla_login',$cookie);
            	//echo '<pre>OK:'; print_r($userinfo); echo '</pre>';
            	//echo '<pre>IV:'; print_r($iv); echo '</pre>';
                if($iv['logindisabled'][0]=='FALSE')
	            	return $userinfo;
            }
		}
        // incorrect username and/or password
        return false;
    }
    elseif(isset($_REQUEST['logout']))
    {
        setcookie('gla_login','');
        return false;
    }
    elseif(isset($_COOKIE['gla_login']))
    {
    	list($cookie,$hash) = explode('::',$_COOKIE['gla_login'],2);
        if(trim(md5($cookie.$cookiehash))==trim($hash))
        {
        	list($cookieinfo, $t) = explode('@',$cookie,2);
        	if(intval($t) > time())
            {
                $cookie = implode('@', array($cookieinfo,time()+3600));
                $cookie = $cookie .'::'.md5($cookie.$cookiehash);
                setcookie('gla_login',$cookie);
            	return unserialize(base64_decode($cookieinfo));
            }
            else
            	return false;
        }
        else
        	return false;
    }
    else
    {
    	return false;
    }
}


