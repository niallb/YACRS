<?php
require_once('corelib/ldap_login.php');

function checkLoggedInUser($allowLogin = true, &$error = false)
{
/*# In future I think the cookie should just contain a user ID, and the rest of uinfo
    should be replaced by the userInfo class... Maybe this is quicker for students though...
*/
	global $CFG;
    $uinfo = false;
	if(($allowLogin )&&(isset($_REQUEST['uname']))&&(isset($_REQUEST['pwd'])))
    {
        if(session_id()!=='')
   	        session_destroy(); // Just to clear out old LTI info.
        if((isset($CFG['adminname']))&&($CFG['adminname']==$_REQUEST['uname'])&&(isset($CFG['adminpwd']))&&($CFG['adminpwd']!=''))
            $uinfo = checkSuperLogin($_REQUEST['uname'], $_REQUEST['pwd']);
        else
            $uinfo = checkLogin($_REQUEST['uname'], $_REQUEST['pwd']);
        if($uinfo)
        {
           //# Should also check by e-mail
           //#Some thinking & probably refactoring needed to make sure LTI and OpenID
           //# logins can be supported.
        	$user = userInfo::retrieve_by_username($uinfo['uname']);
            if($user == false)
            {
            	$user = new userInfo();
                $user->username = $uinfo['uname'];
                $user->name = $uinfo['gn'].' '.$uinfo['sn'];
                $user->email = $uinfo['email'];
                if(isset($uinfo['sessionCreator']))
	                $user->sessionCreator = $uinfo['sessionCreator'];
                else
                    $user->sessionCreator = false;
                $user->Insert();
            }
            else
            {
                if(isset($uinfo['sessionCreator']))  // sessionCreator defined by local login, e.g. staff flag in LDAP
                {
                	$uinfo['sessionCreator'] = $user->sessionCreator||$uinfo['sessionCreator'];
                    if($uinfo['sessionCreator'] != $user->sessionCreator)
                    {
                        $user->sessionCreator = $uinfo['sessionCreator'];
                        $user->update();
                    }
                }
                else   // sessionCreator defined in YACRS
                {
                    if($user->sessionCreator)
                        $uinfo['sessionCreator'] = true;
                }
                if($user->isAdmin)
                    $uinfo['isAdmin'] = true;
                elseif((isset($CFG['adminname']))&&($CFG['adminname']==$user->username))
                    $uinfo['isAdmin'] = true;
            }
        }
        elseif($uinfo = checkLTISessionUser($_REQUEST['uname'], $_REQUEST['pwd']))
        {
        }
        else
        {
            $_REQUEST['pwd'] = "";
        	$error = "Incorrect username or password.";
        }
    }
    elseif(isset($_REQUEST['logout']))
    {
        setcookie($CFG['appname'].'_login','');
        return false;
    }
    elseif(isset($_COOKIE[$CFG['appname'].'_login']))
    {
        $uinfo = CheckValidLoginCookie($_COOKIE[$CFG['appname'].'_login']);
    }
    if($uinfo)
    {
      	setcookie($CFG['appname'].'_login',CreateLoginCookie($uinfo));
        return $uinfo;
    }
    else
    {
    	return false;
    }
}

function CreateLoginCookie($uinfo)
{
	global $CFG;
    $cookieinfo = base64_encode(serialize($uinfo));
    $cookie = implode('@', array($cookieinfo,time()+$CFG['cookietimelimit']));
    $cookie = $cookie .'::'.md5($cookie.$CFG['cookiehash']);
    return $cookie;
}

function CheckValidLoginCookie($cookie)
{
	global $CFG;
  	list($cookie,$hash) = explode('::',$cookie,2);
    if(trim(md5($cookie.$CFG['cookiehash']))==trim($hash))
    {
      	list($cookieinfo, $t) = explode('@',$cookie,2);
      	if(intval($t) > time())
        {
            return unserialize(base64_decode($cookieinfo));
        }
    }
   	return false;
}

function loginBox($uinfo, $error = '')
{
	$out ='<div class="loginBox">';
    if((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
        $protocol = 'https';
    else
        $protocol = 'http';
    if($uinfo==false)
    {
		$out .= "<form method='POST' action='$protocol://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."'>";
	    $out .= "<table><tr><td><label for='uname'>Username</label>:</td><td><input type='text' name='uname' id='uname'/></td></tr>";
	    $out .= "<tr><td><label for='pwd'>Password</label>:</td><td><input type='password' name='pwd' id='pwd'/></td></tr>";
        if(strlen($error))
        {
	        $out .= "<tr><td colspan='2'><span style='color:red;'>$error</span></td></tr>";
        }
        foreach($_REQUEST as $k=>$v)
        {
            $out .= "<input type='hidden' name='$k' value='$v'/>";
        }
	    $out .= "<tr><td colspan='2' align='center'><input type='submit' name='submit' value='Log-in'/></td></tr></table></form>";
    }
    else
    	$out .= "You are logged in as {$uinfo['gn']} {$uinfo['sn']} (<a href='{$_SERVER['PHP_SELF']}?logout=1'>Log-out</a>)";
    $out .= '</div>';
    return $out;
}

function checkSuperLogin($username, $password, &$error=false)
{
	global $CFG;
    $error = false;
    $clrtime = time()+5; // For paranoid prevention of timing to narrow username/password guesses
    $testpwd = md5($CFG['cookiehash'].$password);
	if(($CFG['adminname'] == $username)&&(($CFG['adminpwd'] == $testpwd)||($CFG['adminpwd'] == $password)))
    {
	    $uinfo = array();
	    //echo '<pre>'; print_r($record); echo '</pre>';
	    $uinfo['uname'] = $username;
	    $uinfo['gn'] = $username;
	    $uinfo['sn'] = '(Admin user)';
	    $uinfo['email'] = '';
	    $uinfo['isAdmin'] = true;
	    $uinfo['sessionCreator'] = true;
        return $uinfo;
    }
    else
    {
        while($clrtime < time()) sleep(1); // Paranoid prevention of timing to narrow username/password guesses
        $error = 'Incorrect username or password';
        return false; //Incorrect username
    }
}

function checkLTISessionUser($username, $password)
{
    if(preg_match('/\A[1-9][0-9]*\z/s', $username))
    {
        $s = session::retrieve_session($username);
        if(($s !== false)&&($password == substr($s->ownerID, 0, 8)))
        {
            $uinfo = array('uname'=>$s->ownerID, 'gn'=>'', 'sn'=>'(LTI)', 'email'=>'', 'isAdmin'=>false, 'sessionCreator'=>true);
            return $uinfo;
        }
    }
    return false;
}

?>
