<?php
require_once(ROOT_PATH.'corelib/ldap_login.php');

function checkLoggedInUser($allowLogin = true, &$error = false)
{
/*# In future I think the cookie should just contain a user ID, and the rest of uinfo
    should be replaced by the userInfo class... Maybe this is quicker for students though...
*/
	global $CFG;
    $error = false;
    $uinfo = false;
	if(($allowLogin )&&(isset($_REQUEST['uname']))&&(isset($_REQUEST['pwd'])))
    {
        if(session_id()!=='')
   	        session_destroy(); // Just to clear out old LTI info.
        if((isset($CFG['adminname']))&&($CFG['adminname']==$_REQUEST['uname'])&&(isset($CFG['adminpwd']))&&($CFG['adminpwd']!=''))
            $uinfo = checkSuperLogin($_REQUEST['uname'], $_REQUEST['pwd']);
        else
            $uinfo = @checkLogin($_REQUEST['uname'], $_REQUEST['pwd']);

        //This allows YACRS to be used with a simple CSV file of users, typically for development test users
        // but it could also be used for setups with no suitable LDAP, LTI or OpenID Connect autentication availible.
        // The format of the user file is a CSV with username,firstname,lastname,email,password
        // Usernames starting with 'te' are teachers.
        // If not required this can be commented out.
        if(($uinfo==false)&&(file_exists('../localusers.csv')))
        {
            $csv = array_map('str_getcsv', file('../localusers.csv'));
            array_walk($csv, function(&$a) use ($csv) {
                $a = array_combine($csv[0], $a);
            });
            array_shift($csv); # remove column header
            $users = array();
            foreach($csv as $idx=>$userinfo)
                $users[$userinfo['username']] = $idx;
            if((isset($users[$_REQUEST['uname']]))&&($csv[$users[$_REQUEST['uname']]]['password']==$_REQUEST['pwd']))
            {
                $u = $csv[$users[$_REQUEST['uname']]];
                $uinfo = array();
                $uinfo['uname'] = $u['username'];
                $uinfo['gn'] = $u['firstname'];
                $uinfo['sn'] = $u['lastname'];
                $uinfo['email'] = $u['email'];
                $uinfo['isAdmin'] = false;
                $uinfo['sessionCreator'] = substr($u['username'],0,2)=='te';
            }
        }
        //# End of local/test user support

        if($uinfo)
        {
           //# Should also check by e-mail
           //#Some thinking & probably refactoring needed to make sure LTI and OpenID
           //# logins can be supported.
        	$user = userInfo::retrieve_by_username($uinfo['uname']);
            if($user == false)
            {
                $user = CreateUser($uinfo);
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
        //elseif($uinfo = checkLTISessionUser($_REQUEST['uname'], $_REQUEST['pwd']))    {  }   //#Todo
        else
        {
            $_REQUEST['pwd'] = "";
        	$error = "Incorrect username or password.";
        }
    }
    elseif(isset($_REQUEST['logout']))
    {
        setcookie($CFG['appname'].'_login', '', 0, '', '', false, true);
        return false;
    }
    elseif(isset($_COOKIE[$CFG['appname'].'_login']))
    {
        $uinfo = CheckValidLoginCookie($_COOKIE[$CFG['appname'].'_login']);
    }
    if($uinfo)
    {
      	setcookie($CFG['appname'].'_login',CreateLoginCookie($uinfo), 0, '', '', false, true);
        $uinfo['user']=userInfo::retrieve_by_username($uinfo['uname']);
        if($uinfo['user']==false)
            $uinfo['user'] = CreateUser($uinfo); // To support OpenID logins
        //# Also to support OpenID logins, need to refactor to tidy this up.
        if($uinfo['user']->sessionCreator) 
            $uinfo['sessionCreator'] = true;
        return $uinfo;
    }
    else
    {
    	return false;
    }
}

function CreateUser($uinfo)
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
    return $user;
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
		$out .= "<form method='POST' action='$protocol://".$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF']."' class='form-horizontal'>";
        if(strlen($error))
        {
	        $out .= "<div class='form-group'><div class='col-sm-8 col-sm-push-4'><div class='alert alert-danger'>$error</div></div></div>";
        }

        $out .= '<div class="form-group row">';
        $out .= '<label class="col-sm-4 control-label" for="uname">Username';
        $out .= ' <span style="color: Red;" class="fa fa-asterisk" aria-label="Required" aria-hidden="true"></span>';
        $out .= '</label>';
        $out .= '<div class="col-sm-8"><input class="form-control" type="text" id="uname" name="uname" size="20"';
        $out .= "/></div></div>\n";

        $out .= '<div class="form-group row">';
        $out .= '<label class="col-sm-4 control-label" for="pwd">Password';
        $out .= ' <span style="color: Red;" class="fa fa-asterisk" aria-label="Required" aria-hidden="true"></span>';
        $out .= '</label>';
        $out .= '<div class="col-sm-8"><input class="form-control" type="password" id="pwd" name="pwd" size="20"';
        $out .= "/></div></div>\n";
        
        foreach($_REQUEST as $k=>$v)
        {
            if(($k != 'pwd')&&($k != 'uname'))
                $out .= "<input type='hidden' name='$k' value='$v'/>";
        }
        
        $out .= '<div class="form-group row">';
        $out .= '<span class="col-sm-4">&nbsp;</span>';
        $out .= '<div class="col-sm-4">';

		$out .= "<input type='submit' name='submit' value='Log in' class='btn btn-block btn-success'/></div></div>";
        $out .= '<div class="form-group row">';
        $out .= '<span class="col-sm-4">&nbsp;</span>';
        $out .= "<div class='col-sm-4'><a href='join.php' class='btn btn btn-block btn-primary'>Anonymous Guest Access</a></div></div>";
	    $out .= "</form>";
    }
    else
    	$out .= "{$uinfo['gn']} {$uinfo['sn']} <a href='{$_SERVER['PHP_SELF']}?logout=1'><i class='fa fa-lock'></i> Log out</a>";
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
        $lnk = retrieve_ltisessionlink_matching('session_id', $username);
        if(($lnk !== false)&&($s !== false)&&($password == substr($s->ownerID, 0, 8)))
        {
            $uinfo = array('uname'=>$s->ownerID, 'gn'=>'', 'sn'=>'(LTI)', 'email'=>'', 'isAdmin'=>false, 'sessionCreator'=>true);
            return $uinfo;
        }
    }
    return false;
}

?>
