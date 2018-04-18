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

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
 
$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php">YACRS</a></li>';
$template->pageData['breadcrumb'] .= '<li>Administration</li>';
$template->pageData['breadcrumb'] .= '</ul>';

if(($uinfo==false)||(!$uinfo['isAdmin']))
{
    header("Location: index.php");
}
else
{
    $userDetail = userInfo::retrieve_by_username($uinfo['uname']);
    if(isset($_REQUEST['qu']))
    {
        $userDetail->teacherPrefs->lastQuType = $_REQUEST['qu'];
        $userDetail->update();
    }

	$eqform = new $questionTypes[$userDetail->teacherPrefs->lastQuType]['edit']();
	switch($eqform->getStatus())
	{
	case FORM_NOTSUBMITTED:
        $eqform->sessionID = $sessionID;
	    $template->pageData['mainBody'] .= $eqform->getHtml();
	    break;
	case FORM_SUBMITTED_INVALID:
	    $template->pageData['mainBody'] .= $eqform->getHtml();
	    break;
	case FORM_SUBMITTED_VALID:
        $theQu = new question();
        $theQu->ownerID = false;
     	$theQu->session_id = false;
		$theQu->title = $eqform->title;
		$theQu->multiuse = true;
        $theQu->definition = $eqform->getNewQuestion();
        $theQu->id = $theQu->insert();

	    header('Location:admin.php?disp=qus');
        //$template->pageData['mainBody'] = 'Location:runsession.php?sessionID='.$eqform->sessionID;

	    //header('Location:index.php?id='.$project->id);      */
	    break;
	case FORM_CANCELED:
	    header('Location:admin.php?disp=qus');
	    break;
    }

}
echo $template->render();


?>
