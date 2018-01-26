<?php
/*****************************************************************************
YACRS Copyright 2013-2015, The University of Glasgow.
Written by Niall S F Barr (niall.barr@glasgow.ac.uk, niall@nbsoftware.com)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*****************************************************************************/

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('PAGESIZE', 20);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
include_once('corelib/mobile.php');
$template = new templateMerge($TEMPLATE);
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=mobile'>Use mobile mode</a>";

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php"><i class="fa fa-home"></i>'.$CFG['sitetitle'].'</a></li>';
$template->pageData['breadcrumb'] .= '<li><i class="fa fa-wrench"></i>Administration</li>';
$template->pageData['breadcrumb'] .= '</ul>';

if($uinfo==false)
{
	$template->pageData['headings'] = "<h1  style='text-align:center; padding:10px;'>Login</h1>";
    $template->pageData['loginBox'] = loginBox($uinfo, $loginError);//."<p style='text-align:right;'><a href='join.php'>Or click here for guest/anonymous access</a></p>";
}
elseif(!$uinfo['isAdmin'])
{
    header("Location: index.php");
}
else
{
    $disp = requestStr('disp', '');
    $page = requestInt('page', 0);
    $id = requestInt('id', 0);
    $searchval = requestRaw('searchval', "");
    $template->pageData['mainBody'] = '<h1>YACRS Administration</h1>';
    $template->pageData['mainBody'] .= '<ul class="adminSections">';
    if($disp == 'sessions')
    {
        $pageCount = ceil(session::count()/PAGESIZE);
        $pageDisp = $page+1;
        if($pageDisp > 1)
            $prevLink = adminLink('<i class="fa fa-angle-left"></i> ', array('disp'=>'sessions', 'page'=>$page-1), true);
        else
        	$prevLink = '';
        if($pageDisp < $pageCount)
            $nextLink = adminLink(' <i class="fa fa-angle-right"></i>', array('disp'=>'sessions', 'page'=>$page+1), true);
        else
        	$nextLink = '';
        $template->pageData['mainBody'] .= "<li><b>Sessions</b> ".adminLink('close', array('disp'=>''), true)."<span class='badge'>{$prevLink}Page $pageDisp of $pageCount {$nextLink}</span></li>";
        $template->pageData['mainBody'] .= listSessions($page);
    }
    else
        $template->pageData['mainBody'] .= '<li>'.adminLink('Sessions', array('disp'=>'sessions'), true).' <span class="badge">'.session::count().'</span></li>';
    if($disp == 'users')
    {
        update_from_userSearch($searchval);
        if(($id==0)&&(strlen($searchval)))
        {
            $template->pageData['mainBody'] .= "Search results for '$searchval':<br/>";

	        $pageDisp = $page+1;
	        if($pageDisp > 1)
	            $prevLink = adminLink('<i class="fa fa-angle-left"></i> ', array('disp'=>'users', 'page'=>$page-1, 'searchval'=>$searchval), true);
	        else
	        	$prevLink = '';
	        if($pageDisp < $pageCount)
            	$nextLink = adminLink(' <i class="fa fa-angle-right"></i>', array('disp'=>'users', 'page'=>$page+1, 'searchval'=>$searchval), true);
	        $template->pageData['mainBody'] .= "<li><b>Users</b> <span class='badge'>{$prevLink} Page $pageDisp {$nextLink}</span></li>";

	        $template->pageData['mainBody'] .= listUsers($page, $searchval);


            $template->pageData['mainBody'] .= show_userSearch($disp, $searchval);
        }
        else if(($id==0)||(isset($_REQUEST['userInfo_cancel'])))
        {
	        $pageCount = ceil(userInfo::count()/PAGESIZE);
	        $pageDisp = $page+1;
	        if($pageDisp > 1)
	            $prevLink = adminLink('<i class="fa fa-angle-left"></i> ', array('disp'=>'users', 'page'=>$page-1), true);
	        else
	        	$prevLink = '';
	        if($pageDisp < $pageCount)
	            $nextLink = adminLink(' <i class="fa fa-angle-right"></i>', array('disp'=>'users', 'page'=>$page+1), true);
	        else
	        	$nextLink = '';
	        $template->pageData['mainBody'] .= "<li>Users <span class='badge'>{$prevLink} Page $pageDisp of $pageCount {$nextLink}</li>";

//            exit(__LINE__.' '.__FILE__.' Implement search next');

	        $template->pageData['mainBody'] .= listUsers($page);
            $template->pageData['mainBody'] .= show_userSearch($disp, $searchval);
        }
        else
        {
            $template->pageData['mainBody'] .= '<li>'.adminLink('Users', array('disp'=>'users'), true).' <span class="badge">'.userInfo::count().'</span></li>';
	        $template->pageData['mainBody'] .= displayUser($id);
        }
    }
    else
        $template->pageData['mainBody'] .= '<li>'.adminLink('Users', array('disp'=>'users'), true).' <span class="badge">'.userInfo::count().'</span></li>';
    $template->pageData['mainBody'] .= '</form>';
    if($disp == 'lti')
    {
        $template->pageData['mainBody'] .= '<li><b>LTI Consumers</b> '.adminLink('close', array('disp'=>''), true).'<span class="badge">'.lticonsumer::count().'</span></li>';
        $add = requestInt('add', 0);
        $name = '';
        $consumer_key = '';
        $secret = md5(uniqid());
        if(($add > 0)&&($id > 0))
        {
            $ltiinf = lticonsumer::retrieve_lticonsumer($id);
	        $name = $ltiinf->name;
	        $consumer_key = $ltiinf->consumer_key;
            $secret = $ltiinf->secret;
        }
        if(update_from_ltiConsumerInfo($name, $consumer_key, $secret))
        {
            if(strlen($name) < 1)
            {
                $add = 1;
                $template->pageData['mainBody'] .= "A name is required.<br/>";
            }
            if(strlen($consumer_key) < 1)
            {
                $add = 1;
                $template->pageData['mainBody'] .= "A Consumer key is required.<br/>";
            }
            if(strlen($secret) < 8)
            {
                $add = 1;
                $template->pageData['mainBody'] .= "Secret must be at least 8 characters.<br/>";
            }
            if($add==0)
            {
                if($id == 0)
                    $ltiinf = new lticonsumer();
                else
                	$ltiinf = lticonsumer::retrieve_lticonsumer($id);
                $ltiinf->name = $name;
                $ltiinf->consumer_key = $consumer_key;
                $ltiinf->keyHash = md5($consumer_key);
                $ltiinf->secret = $secret;
                if($id == 0)
                    $ltiinf->insert();
                else
                	$ltiinf->update();
            }
        }
        if($add>0)
        {
             $template->pageData['mainBody'] .= show_ltiConsumerInfo($disp, $id, $name, $consumer_key, $secret);
        }
        else
        {
             $template->pageData['mainBody'] .= '<ul>';
             $ltics = lticonsumer::retrieve_all_lticonsumer(0, -1, "name ASC");
             if($ltics)
             {
                 foreach($ltics as $l)
                     $template->pageData['mainBody'] .= "<li>".adminLink($l->name, array('disp'=>'lti', 'id'=>$l->id, 'add'=>'1'), true)."</li>";
             }
             //# links to existing consumers
             $template->pageData['mainBody'] .= '<li><b>'.adminLink('Add new LTI consumer details', array('disp'=>'lti', 'add'=>'1'), true).'</b></li>';
             $template->pageData['mainBody'] .= '</ul>';
        }
    }
    else
    {
        $template->pageData['mainBody'] .= '<li><b>'.adminLink('LTI Consumers', array('disp'=>'lti'), true).'</b> <span class="badge">'.lticonsumer::count().'</span></li>';
    }
    if($disp == 'qus')
    {
        $delqu = requestInt('delqu');
        $actsys = requestInt('actsys');
        $delsys = requestInt('delsys');
        if($delqu)
        {
            question::delete($delqu);
        }
        if($delsys)
        {
            systemQuestionLookup::deleteFor($delsys);
        }
        if($actsys)
        {
            $qudef = question::retrieve_question($actsys);
            $newsq = new systemQuestionLookup();
            $newsq->name = substr($qudef->title, 0, systemQuestionLookup::nameLengthLimit());
            $newsq->qu_id = $qudef->id;
            $newsq->insert();
        }
	    $template->pageData['mainBody'] .= '<li><b>Default generic questions </b>'.adminLink('close', array('disp'=>''), true).'</li>';
        $possqus = question::retrieve_question_matching('ownerID', false, 'name ASC');
	    //$template->pageData['mainBody'] .= '<li><b>'.adminLink('Pos generic questions', array('disp'=>'qus'), true).'</b> <span class="badge">'.sizeof($possqus).'</span></li>';
        //$template->pageData['mainBody'] .= '<pre>'.print_r($possqus, true).'</pre>';
        $squs = systemQuestionLookup::all();
        $nameLengthLimit = systemQuestionLookup::nameLengthLimit();
        $template->pageData['mainBody'] .=  '<ul>';
        $activeIDs = array();
        foreach($squs as $squ)
        {
            $qudef = question::retrieve_question($squ->qu_id);
            $activeIDs[] = $qudef->id;
            $template->pageData['mainBody'] .=  "<li>{$qudef->title} (".get_class($qudef->definition).") <span style='color:green;'>Active</span>";
            $count = questionInstance::count('theQuestion_id',$qudef->id);
            $template->pageData['mainBody'] .=  " ($count instances)";
            $template->pageData['mainBody'] .=  " <a href='admin.php?disp=qus&delsys={$qudef->id}'>Deactivate</a>";
            $template->pageData['mainBody'] .=  "</li>";
            //$template->pageData['mainBody'] .= '<pre>'.print_r($qudef, true).'</pre>';
        }
        foreach($possqus as $qudef)
        {
            if(!in_array($qudef->id, $activeIDs))
            {
                $template->pageData['mainBody'] .=  "<li>{$qudef->title} (".get_class($qudef->definition).") <span style='color:red;'>Inactive</span>";
                $count = questionInstance::count('theQuestion_id',$qudef->id);
                $template->pageData['mainBody'] .=  " ($count instances)";
	            if($count == 0)
	                $template->pageData['mainBody'] .=  " <a href='admin.php?disp=qus&delqu={$qudef->id}'>Delete</a>";
                $template->pageData['mainBody'] .=  " <a href='admin.php?disp=qus&actsys={$qudef->id}'>Activate</a>";
	            $template->pageData['mainBody'] .=  "</li>";
            }
            //$template->pageData['mainBody'] .= '<pre>'.print_r($qudef, true).'</pre>';
        }
        $template->pageData['mainBody'] .=  '</ul>';
        //$template->pageData['mainBody'] .= '<pre>'.print_r($squs, true).'</pre>';
        $template->pageData['mainBody'] .=  '<hr/><ul>';
        global $questionTypes;
        foreach($questionTypes as $key=>$val)
        {
            $template->pageData['mainBody'] .=  "<li><a href='addsysqu.php?qu={$key}'>Add a new {$val['name']}.</a></li>";
        }
        $template->pageData['mainBody'] .=  '</ul>';
    }
    else
    {
	    $template->pageData['mainBody'] .= '<li><b>'.adminLink('Default generic questions', array('disp'=>'qus'), true).'</b> <span class="badge">'.sizeof(systemQuestionLookup::all()).'</span></li>';
    }
    $template->pageData['mainBody'] .= '</ul>';
    $template->pageData['mainBody'] .= "<p>Student responses in last hour: ".response::countAllInLastHour().'</p>';
	$template->pageData['logoutLink'] = loginBox($uinfo);
}
echo $template->render();

function listSessions($page = 0)
{
    $out = '<ul class="session-list">';
    $sessions = session::retrieve_all_sessions($page*PAGESIZE, PAGESIZE, "created desc");
    if($sessions !== false)
    {
	    foreach($sessions as $s)
	    {
	        $out .= "<li><p class='session-title'><a href='runsession.php?sessionID={$s->id}'>{$s->title}</a><span class='user-badge session-id'><i class='fa fa-hashtag'></i> {$s->id}</span></p><p class='session-details'> Owned by {$s->ownerID}</p><p class='session-details'>Created ".strftime("%A %e %B %Y at %H:%M", $s->created)."</p><span class='feature-links'><a href='editsession.php?sessionID={$s->id}'><i class='fa fa-pencil'></i> Edit</a></span></li>";
	        //$template->pageData['mainBody'] .= "<li><p class='session-title'><a href='runsession.php?sessionID={$s->id}'>{$s->title}</a><span class='user-badge session-id'><i class='fa fa-hashtag'></i> {$s->id}</span></p><p class='session-details'> Created $ctime</p><span class='feature-links'><a href='editsession.php?sessionID={$s->id}'><i class='fa fa-pencil'></i> Edit</a> <a href='confirmdelete.php?sessionID={$s->id}'><i class='fa fa-trash-o'></i> Delete</a></span></li>";
	    }
    }
    $out .= '</ul>';
    return $out;
}

function listUsers($page = 0, $searchTerm=null)
{
    $out = '<ul class="session-list">';
    if($searchTerm==null)
	    $users = userInfo::retrieve_all_userInfo($page*PAGESIZE, PAGESIZE, "name asc");
    else
	    $users = userInfo::search_userInfo($searchTerm, $page*PAGESIZE, PAGESIZE);

    if($users !== false)
    {
	    foreach($users as $u)
	    {
            $admin = $u->isAdmin?" <span class='user-badge admin'><i class='fa fa-wrench'></i> Admin</span>":"";
            $sessionCreator = $u->sessionCreator?" <span class='user-badge creator'><i class='fa fa-plus-circle'></i> Session creator</span>":"";
            $link = adminLink($u->name, array('disp'=>'users', 'page'=>$page, 'id'=>$u->id), $reset=false);
	        $out .= "<li>{$link}, {$u->email}{$admin}{$sessionCreator}</li>";
	    }
    }
    $out .= '</ul>';
    return $out;
}

function displayUser($id)
{
    global $disp, $page;
    $user = userInfo::retrieve_userInfo($id);
    if(update_from_userInfo($user->nickname, $user->phone, $user->sessionCreator, $user->isAdmin))
        $user->update();
    return show_userInfo($disp, $page, $id, $user->username, $user->name, $user->email, $user->nickname, $user->phone, $user->sessionCreator, $user->isAdmin);
}

function adminLink($text, $params, $reset=false)
{
    $basicparams = array('disp', 'page', 'id', 'searchval', 'add');
    $out = '<a href="admin.php?';
    foreach($basicparams as $p)
    {
        if(isset($params[$p]))
        {
            if(($params[$p] !== false)&&($params[$p] != ''))
            {
                $out .= "$p=".urlencode($params[$p])."&";
            }
        }
        elseif((!$reset)&&(requestSet($p)))
        {
            $out .= "$p=".urlencode(requestStr($p))."&";
        }
    }
    $out = substr($out, 0, strlen($out)-1).'">'.$text.'</a>';
    return $out;
}

define('userInfo_magic', md5('userInfo'));
define('userSearch_magic', md5('userSearch'));
define('ltiConsumerInfo_magic', md5('ltiConsumerInfo'));

function show_userInfo($disp, $page, $id, $username, $name, $email, $nickname, $phone, $sessionCreator, $isAdmin)
{
    $out = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" class="form-horizontal">';
    $out .= '<input type="hidden" name="userInfo_code" value="'.userInfo_magic.'"/>';

    $out .= '<input type="hidden" name="disp" value="'.$disp.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="page" value="'.$page.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="id" value="'.$id.'"';
    $out .= "/>\n";

    $out .= '<div class="form-group">';
    $out .= '<label class="col-sm-4 control-label" for="username">Username</label>';
    $out .= '<div class="col-sm-8"><p class="form-control-static" id="username">'.$username.'</p></div></div>';
    
    $out .= '<div class="form-group">';
    $out .= '<label class="col-sm-4 control-label" for="name">Name</label>';
    $out .= '<div class="col-sm-8"><p class="form-control-static" id="name">'.$name.'</p></div></div>';

	$out .= '<div class="form-group">';
    $out .= '<label class="col-sm-4 control-label" for="email">Email</label>';
    $out .= '<div class="col-sm-8"><p class="form-control-static" id="email">'.$email.'</p></div></div>';
    
    $out .= '<div class="form-group">';
    $out .= '<label class="col-sm-4 control-label" for="nickname">Nickname</label>';
    $out .= '<div class="col-sm-8"><input type="text" name="nickname" value="'.$nickname.'" class="form-control" /></div></div>';
    
    $out .= '<div class="form-group">';
    $out .= '<label class="col-sm-4 control-label" for="phone">Phone</label>';
    $out .= '<div class="col-sm-8"><input type="text" name="phone" value="'.$phone.'" class="form-control" /></div></div>';

    $out .= '<div class="form-group"><div class="col-sm-8 col-sm-offset-4"><div class="checkbox">';
    $out .= '<label for="sessionCreator"><input type="checkbox" name="sessionCreator" value="1"';
    if($sessionCreator)
        $out .= ' checked="1" ';
    $out .= '/> Force Allow Session Creation</label>';
    $out .= '</div></div></div>';

    $out .= '<div class="form-group"><div class="col-sm-8 col-sm-offset-4"><div class="checkbox">';
    $out .= '<label for="isAdmin"><input type="checkbox" name="isAdmin" value="1"';
    if($isAdmin)
        $out .= ' checked="1" ';
    $out .= '/> Administrator</label>';
    $out .= '</div></div></div>';

    $out .= '<div class="form-group">';
    $out .= '<div class="col-sm-8 col-sm-offset-4">';
    $out .= '<input class="submit btn btn-primary" name="userInfo_submit" type="submit" value="Save Changes" />';
    $out .= '<input class="submit btn btn-link" name="userInfo_cancel" type="submit" value="Close" />';
    $out .= '</div></div>';

    $out .= '<form>';
    return $out;
}

function update_from_userInfo(&$nickname, &$phone, &$sessionCreator, &$isAdmin)
{
    if((isset($_REQUEST['userInfo_code']))&&($_REQUEST['userInfo_code']==userInfo_magic))
    {
        if(isset($_REQUEST['userInfo_cancel']))
            return false;
        $nickname = strval($_REQUEST['nickname']);
        $phone = strval($_REQUEST['phone']);
        $sessionCreator = (isset($_REQUEST['sessionCreator'])&&(intval($_REQUEST['sessionCreator'])>0));
        $isAdmin = (isset($_REQUEST['isAdmin'])&&(intval($_REQUEST['isAdmin'])>0));
        return true;
    }
    else
    {
        return false;
    }
}

function show_userSearch($disp, $searchval)
{
    $out = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" class="form-horizontal user-search-form">';
    $out .= '<input type="hidden" name="userSearch_code" value="'.userSearch_magic.'"/>';

    $out .= '<input type="hidden" name="disp" value="'.$disp.'"/>';

    $out .= '<div class="form-group">';
    $out .= '<label for="searchval" class="control-label col-sm-4">Search by Name or ID</label>';
    $out .= '<div class="col-sm-8"><div class="input-group"><input type="text" name="searchval" value="'.$searchval.'" class="form-control"><span class="input-group-btn"><input class="submit btn btn-primary" name="userSearch_submit" type="submit" value="Search" /></span></div></div></div>';

    $out .= '<form>';
    return $out;
}

function update_from_userSearch(&$searchval)
{
    if((isset($_REQUEST['userSearch_code']))&&($_REQUEST['userSearch_code']==userSearch_magic))
    {
        if(isset($_REQUEST['userSearch_cancel']))
            return false;
        $searchval = strval($_REQUEST['searchval']);
        return true;
    }
    else
    {
        return false;
    }
}

function show_ltiConsumerInfo($disp, $id, $name, $consumer_key, $secret)
{
    $out = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    $out .= '<input type="hidden" name="ltiConsumerInfo_code" value="'.ltiConsumerInfo_magic.'"/>';

    $out .= '<input type="hidden" name="disp" value="'.$disp.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="id" value="'.$id.'"';
    $out .= "/>\n";

    $out .= '<div class="formfield">';
    $out .= '<label for="name">Name, typically an institution:';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><input type="text" name="name" value="'.$name.'" size="80"';
    $out .= "/></span></div>\n";

    $out .= '<div class="formfield">';
    $out .= '<label for="consumer_key">Key, typicaly a server name:';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><input type="text" name="consumer_key" value="'.$consumer_key.'" size="80"';
    $out .= "/></span></div>\n";

    $out .= '<div class="formfield">';
    $out .= '<label for="secret">Shared secret:';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><input type="text" name="secret" value="'.$secret.'" size="80"';
    $out .= "/></span></div>\n";

    $out .= '<div class="formfield">';
    $out .= '<input class="submit" name="ltiConsumerInfo_submit" type="submit" value="Update" />';
    $out .= '<input class="submit" name="ltiConsumerInfo_cancel" type="submit" value="Cancel" />';
    $out .= "</div>";

    $out .= '<form>';
    return $out;
}

function update_from_ltiConsumerInfo(&$name, &$consumer_key, &$secret)
{
    if((isset($_REQUEST['ltiConsumerInfo_code']))&&($_REQUEST['ltiConsumerInfo_code']==ltiConsumerInfo_magic))
    {
        if(isset($_REQUEST['ltiConsumerInfo_cancel']))
            return false;
        $name = strval($_REQUEST['name']);
        $consumer_key = strval($_REQUEST['consumer_key']);
        $secret = strval($_REQUEST['secret']);
        return true;
    }
    else
    {
        return false;
    }
}

?>
