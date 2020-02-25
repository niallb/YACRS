<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('corelib/templateMerge.php');
//require_once('lib/forms.php');
require_once('lib/database.php');
//require_once('lib/questionTypes.php');
require_once('lib/shared_funcs.php');
require_once('lib/textAnalysisLib.php');

$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();
session_start();
if(isset($_REQUEST['sessionID']))
    $_SESSION['sessionID'] = $_REQUEST['sessionID'];
if(isset($_REQUEST['qiID']))
{
    $_SESSION['textAnalysis_qiID'] = $_REQUEST['qiID'];
    unset($_SESSION['selectedPhrases']);
}
$thisSession = isset($_SESSION['sessionID'])? session::retrieve_session($_SESSION['sessionID']):false;
if(!checkPermission($uinfo, $thisSession))
{
    header("Location: index.php");
    exit();
}


$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];

$template->addScript('scripts/ajax.js');
$template->addScript('scripts/d3.min.js');
$template->addScript('scripts/d3.layout.cloud.js');

$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php">YACRS</a></li>';
$template->pageData['breadcrumb'] .= '<li>Responses</li>';
$template->pageData['breadcrumb'] .= '</ul>';

if(isset($_REQUEST['psel']))
    $_SESSION['selectedPhrases'] = $_REQUEST['psel'];
if(isset($_SESSION['selectedPhrases']))
    $selectedPhrases = $_SESSION['selectedPhrases'];
else
    $selectedPhrases = false;

$responses = response::retrieve_response_matching('question_id', $_SESSION['textAnalysis_qiID']);

$phrases = findPhrases($responses);
$phrases2 = array_keys($phrases);

$template->pageData['mainBody'] .= "<h2>Text response analysis assistant</h2>";

if(($selectedPhrases===false)||(isset($_REQUEST['restart'])))
{
    $template->pageData['mainBody'] .= "<div id='instructions'><b>Step one:</b> Select any potentially interesting phrases that should be included in the word cloud from the list below, and click the 'Include selected phrases in cloud' button at the end of the list.</div>";

    $template->pageData['mainBody'] .= wordcloud_ta($responses, array(), '100%', '550px');

    $template->pageData['mainBody'] .= "<h3>Phrases found:</h3>";
    $template->pageData['mainBody'] .= "<div id='phrases'><form target='textAnalysis.php' id='phrasesform'>";
    foreach($phrases as $p=>$count)
    {
        if(is_array($selectedPhrases))
    $checked = in_array($p, $selectedPhrases) ? " checked='checked'" : '';
        else
            $checked = false;
    $template->pageData['mainBody'] .= "<input type='checkbox' name='psel[]' value='{$p}'{$checked}/> $p ($count occurrences)<br/>";
    }
    $template->pageData['mainBody'] .= "<input type='submit' name='submit' value='Include selected phrases in cloud'>";
    $template->pageData['mainBody'] .= "</form></div>";
}
else
{
    $template->pageData['mainBody'] .= "<div id='instructions'><b>Step two:</b> Click on a word or phrase in the first word cloud to generate a second word cloud from just the responses containing that word or phrase.";
    $template->pageData['mainBody'] .= " Click on the second word cloud to see only the responses containing a further word or phrase.</div><div><a href='?restart=1'>Click here to return to step one (select phrases).</a></div>";
    $template->pageData['mainBody'] .= wordcloud_ta($responses, $selectedPhrases, '100%', '550px');
   // $template->pageData['mainBody'] .= " <input type='submit' name='submit' value='Create new cloud with responses that include selected phrases' onclick='secondCloud(); return false;'>";
}
// next should select phrases to 
$template->pageData['mainBody'] .= '<div id="cloud2"></div>';
$template->pageData['mainBody'] .= '<div id="report1"></div>';

echo $template->render();


?>
