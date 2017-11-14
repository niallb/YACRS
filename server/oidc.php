<?php
// This is an incomplete, but working, version of OpenID Connect intended for using Microsoft login
// The missing bits are some somewhat redundant extra security checks

require_once("config.php");

if((isset($_REQUEST['state']))||(isset($_REQUEST['id_token'])))
{
    list($tm, $request_uri, $sig) = explode(" ", $_REQUEST['state']);
    // get tid
    $result = parse_url($request_uri);
    if(isset($result['query']))
    {
	    parse_str($result['query'] , $result);
    }

    if(base64_encode(md5("$tm $request_uri ".$CFG['cookiehash']))==$sig)
    {
        if(isset($_REQUEST['id_token']))
        {
            $segs = explode(".",$_REQUEST['id_token']);
            $hdr = json_decode(base64_decode($segs[0]));
            $id_token = json_decode(base64_decode($segs[1]));
            //exit('<pre>'.print_r($id_token,1).'</pre>');
        }
        else
            $id_token = getMicrosoftIDToken();
    }
    //exit('<pre>'.print_r($id_token, true).'</pre>');
}

if((isset($id_token))&&(strlen($id_token->sub)))
{
    $uinfo = array();
    $uinfo['uname'] = $id_token->sub;
    $uinfo['gn'] = $id_token->name;
    $uinfo['sn'] = '';
    if(isset($id_token->email))
        $uinfo['email'] = $id_token->email;
    else
        $uinfo['email'] = '';
    $uinfo['isAdmin'] = false;
  	setcookie($CFG['appname'].'_login',CreateLoginCookie($uinfo), 0, '', '', false, true);
    header("Location: index.php");
}
else
{
    $loginurl = buildMSOAuth2Request();
    header("Location: $loginurl");
    echo "<a href='$loginurl'>Login with Microsoft</a>";
}

function buildMSOAuth2Request()
{
    global $CFG;
    $request = "https://login.microsoftonline.com/{$CFG['MicrosoftTenant']}/oauth2/v2.0/authorize";
    $request .= "?client_id=" . $CFG['MicrosoftClientID'];
    $request .= "&response_type=id_token";
    $request .= "&scope=" . urlencode("openid email profile");
    $request .= "&redirect_uri=" . urlencode("https://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}");  // This has to be one registered with Microsoft
    $request .= "&response_mode=form_post";
    $tm = time();
    $state = "$tm {$_SERVER['REQUEST_URI']} ";
    $request .= "&state=" . urlencode($state.base64_encode(md5($state.$CFG['cookiehash'])));
    $request .= "&nonce=".time();
    return $request;
}

function getMicrosoftIDToken()
{
    global $CFG;
    $post = array();
    $post['tenant'] = $CFG['MicrosoftTenant'];
    $post['code'] = $_REQUEST['code'];
    $post['client_id'] = $CFG['MicrosoftClientID'];
    $post['client_secret'] = $CFG['MicrosoftClientSecret'];
    $post['redirect_uri'] = "https://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}";
    $post['grant_type'] = "authorization_code";
    $post2 = http_build_query($post);
    $opts = array('http' => array('method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $post2));
    $context  = stream_context_create($opts);

    $json = @file_get_contents('https://login.microsoftonline.com/organizations/oauth2/v2.0/token', false, $context);
    if(strlen($json)==0)
        return false;

    $ret = json_decode($json);
    $segs = explode(".",$ret->id_token);
    $hdr = json_decode(base64_decode($segs[0]));
    $id = json_decode(base64_decode($segs[1]));
    return $id;
}

