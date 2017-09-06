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
	if(isset($CFG['ldapbinduser'])) {
    	ldap_bind($ds, $CFG['ldapbinduser'], $CFG['ldapbindpass']);
    }
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
                return uinfoFromLDAP($record);
			}
			else
				$error = "No Identity vault entry found.<br/>";
			ldap_free_result( $sr );
	    }
	    else
	    {
            while($clrtime < time()) sleep(1); // Paranoid prevention of timing to narrow username/password guesses
            $error = 'Incorrect password';
	    	return false; //Incorrect password
	    }
    }
    else
    {
        while($clrtime < time()) sleep(1); // Paranoid prevention of timing to narrow username/password guesses
        $error = 'Incorrect username';
        return false; //Incorrect username
    }
}

function uinfoFromLDAP($record)
{
    global $CFG;
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
    $uinfo['sessionCreator'] = false;
    if(is_array($CFG['ldap_sessionCreator_rules']))
    {
        foreach($CFG['ldap_sessionCreator_rules'] as $rule)
        {
            if(isset($record[$rule['field']]))
            {
                is_array($record[$rule['field']]) ? $values = $record[$rule['field']] : $values = array($record[$rule['field']]);
                foreach($values as $value)
                {
                    if((isset($rule['match']))&&($rule['match']==$value))
                    {
                        $uinfo['sessionCreator'] = true;
                    }
                    if((isset($rule['regex']))&&(preg_match($rule['regex'],$value)))
                    {
                        $uinfo['sessionCreator'] = true;
                    }
                    if((isset($rule['contains']))&&(strpos($value, $rule['contains'])!==false))
                    {
                        $uinfo['sessionCreator'] = true;
                    }
                }
            }
        }
    }
    return $uinfo;
}
