<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
$template = new templateMerge($TEMPLATE);

$uinfo = abstractsLogin();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = "breadcrumb goes here";
if($uinfo==false)
{
	$template->pageData['headings'] = "<h1  style='text-align:center; padding:10px;'>GUID login</h1>";
	$template->pageData['loginBox'] = loginBox($uinfo);
    if(file_exists('logininfo.htm'))
	    $template->pageData['mainBody'] = file_get_contents('logininfo.htm').'<br/>';
}
else
{
	$template->pageData['logoutLink'] = loginBox($uinfo);

    $template->pageData['mainBody'] = 'Text here';

}

echo $template->render();


?>
