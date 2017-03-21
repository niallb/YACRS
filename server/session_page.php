<style>
    .btn-session{
        text-align: center;
        border: 1px solid;
        background-color: #003865;
        color: white;
        padding: 20px;
        margin: 5px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    }

    .btn-session:hover{
        background-color: #ccc;
        color: #003865;
        cursor: pointer;
        text-decoration: none;
    }

    @media (max-width: 767px) {

        .btn-session{
            text-align: center;
            font-size: 14px !important;
            border: 1px solid #ccc;
            margin-right: 5px;
            margin-left: 5px;
        }

    }
</style>
<?php
/**
 * Created by PhpStorm.
 * User: hamzamalik0123
 * Date: 10/01/2017
 * Time: 16:04
 */

require_once('config.php');
require_once('lib/database.php');
include_once('corelib/mobile.php');
require_once('lib/forms.php');

$template = new templateMerge($TEMPLATE);
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$_REQUEST['sessionID']}&mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$_REQUEST['sessionID']}&mode=mobile'>Use mobile mode</a>";

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php">YACRS</a></li>';


if((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
{
    $serverURL = 'https://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 443)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
else
{
    $serverURL = 'http://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 80)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
$serverURL .= $_SERVER['SCRIPT_NAME'];

if($uinfo==false)
{
    header("Location: index.php");
    // actually should allow join a session as guest...
}
else
{
    $template->pageData['mainBody'] = '';
    $thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
    if($thisSession == false)
    {
        $template->pageData['mainBody'] = "<p><b>Invalid session or missing number.</b></p>".sessionPageInput();
    }
    else
    {
        if(isset($_REQUEST['sessionID']))
            $sessionId = intval($_REQUEST['sessionID']);
        $template->pageData['mainBody'] .= addButtons($thisSession);
    }
    //$template->pageData['mainBody'] .= '<pre>'.print_r($uinfo,1).'</pre>';
    //$template->pageData['mainBody'] .= '<pre>'.print_r($smemb,1).'</pre>';
    $template->pageData['logoutLink'] = loginBox($uinfo);

}

echo $template->render();

function addButtons($session){
    $sessionId      = $session->id;
    $sessionName    = $session->title;
    $out = "<h1>Current Session $sessionName<h1/><br/>
            <div class='row'>
                <div class='col-lg-12' style='text-align: center; font-size: 16px'>
                    <a class='btn btn-primary btn-lg' href='vote.php?sessionID=$sessionId'>
                        <i class='fa fa-bar-chart fa-2x' style='vertical-align: -3px;' aria-hidden='true'></i>
                        Vote
                    </a>
                    <a class='btn btn-primary btn-lg' href='viewQuestions.php?sessionID=$sessionId'>
                        <i class='fa fa-comments fa-2x' style='vertical-align: -3px;' aria-hidden='true'></i>
                        Ask!
                    </a>
                </div>
            </div><br/><br/>";
    return $out;
}
