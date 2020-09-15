<?php
/*
form editSession
{
    title = "Session settings";
    hidden sessionID "0";
    string[80] title "Title" required{
    hint="The title is the name shown on your list of sessions, and can also be displayed on student\'s lists";
    }
    string[20] courseIdentifier "Course Identifier (to import classlist)"
    {
    hint="A course identifier as used in the Course Catalog, e.g COMPSCI1016";
    }
    boolean allowGuests "Allow guest users (without login)"
    {
    hint="Check this if you want people without GUIDs to be able to take part.";
    }
    boolean visible "Display on user\'s available sessions list"
    {
    hint="If this is checked students who have taken part will see a link to this session when they log into YACRS";
    }
    collapsable advanced "Advanced Settings" collapsed
    {
    group "Question settings"
    {
    select questionMode "Question control mode" {options=(0=>"Teacher led (one question at a time)", 1=>"Student paced");}
    integer defaultQuActiveSecs "Default time limit for active questions (seconds, 0 for no limit).";
    boolean allowQuReview "Allow review/change of answers while response open";
    boolean allowFullReview "Allow students to view their answers after class.";
    select customScoring "Custom scoring" {options=(""=>"None");}
    }
    group "Text/micro blogging settings"
    {
    select ublogRoom "Micro blogging mode" {options=(0=>"None", 1=>"Full class", 2=>"Personal (private)", 3=>"Personal (public)");}
    integer maxMessagelength "Maximum message length (characters)";
    boolean allowTeacherQu "Allow questions for the teacher?";
    }
    group "Additional teachers"
    {
    string[80] teachers "Additional users who can run session (comma delimited list of user IDs)";
    }
    }
    okcancel "Create" "Cancel";
}
*/

if(!defined('FORM_NOTSUBMITTED'))
{
    define('FORM_NOTSUBMITTED',0);
    define('FORM_SUBMITTED_VALID', 1);
    define('FORM_SUBMITTED_INVALID', 2);
    define('FORM_CANCELED',3);
}

define('editSession_magic', md5('editSession'));

function show_editSession($sessionID, $title, $courseIdentifier, $allowGuests, $visible, $questionMode, $defaultQuActiveSecs, $allowQuReview, $allowFullReview, $customScoring, $ublogRoom, $maxMessagelength, $allowTeacherQu, $teachers, $validateMessages=array())
{
    $out = '<h1>Session settings</h1>';
    $out .= '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" class="form-horizontal">';
    $out .= '<input type="hidden" name="editSession_code" value="'.editSession_magic.'"/>';

    $out .= '<input type="hidden" name="sessionID" value="'.$sessionID.'"';
    $out .= "/>\n";

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="title">Title';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="The title is the name shown on your list of sessions, and can also be displayed on student\'s lists"
                     data-html="true"><span aria-hidden="true" title="Help with Title" aria-label="Help with Title" class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    $out .= ' <span style="color: Red;" class="fa fa-asterisk" aria-label="Required" aria-hidden="true"></span>';
    if(isset($validateMessages['title']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['title'].'</span>';
    $out .= '</label>';
    
    $out .= '<div class="col-sm-8"><input class="form-control" type="text" name="title" id="title" value="'.$title.'" size="80"';
    $out .= "/></div></div>\n";

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="courseIdentifier">Course Identifier (to import classlist)';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="A course identifier as used in the Course Catalog, e.g COMPSCI1016"
                     data-html="true"><span aria-hidden="true" title="Help with Course Identifier (to import classlist)" aria-label="Help with Course Identifier (to import classlist)" class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    if(isset($validateMessages['courseIdentifier']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['courseIdentifier'].'</span>';
    $out .= '</label>';
    $out .= '<div class="col-sm-8"><input class="form-control" type="text" name="courseIdentifier" id="courseIdentifier" value="'.$courseIdentifier.'" size="20"';
    $out .= "/></div></div>\n";

    $out .= '<div class="form-check row">';
    $out .= '<div class="col-sm-8 offset-sm-4">';
    $out .= '<input class="form-check-input" type="checkbox" name="allowGuests" id="allowGuests" value="1"';
    if($allowGuests)
        $out .= ' checked="1"';
    $out .= '/>';
    $out .= '<label for="allowGuests">Allow guest users (without login)';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="Check this if you want people without GUIDs to be able to take part."
                     data-html="true"><span aria-hidden="true" title="Help with Allow guest users (without login)" aria-label="Help with Allow guest users (without login)" class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    if(isset($validateMessages['allowGuests']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['allowGuests'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $out .= '<div class="form-check row">';
    $out .= '<div class="col-sm-8 offset-sm-4">';
    $out .= '<input class="form-check-input" type="checkbox" name="visible" id="visible" value="1"';
    if($visible)
        $out .= ' checked="1"';
    $out .= '/>';
    $out .= '<label for="visible">Display on user\'s available sessions list';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="If this is checked students who have taken part will see a link to this session when they log into YACRS"
                     data-html="true"><span aria-hidden="true" title="Help with Display on user\'s available sessions list" aria-label="Help with Display on user\'s available sessions list" class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    if(isset($validateMessages['visible']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['visible'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $out .= '<fieldset class="clearfix collapsible" id="id_descriptionhdr">';
    $out .= '<legend id="advanced_hide" class="ftoggler"><a href="#" role="button" class="fheader" role="button"><span class="fa fa-arrow-down"></span> Advanced Settings</a></legend>';
    $out .= '<legend id="advanced_show" class="ftoggler" style="display: none;"><a href="#" role="button" class="fheader" role="button"><span class="fa fa-arrow-right"></span> Advanced Settings</a></legend>';
    $out .= "<div id='advanced' style='display:block;' class='fcontainer clearfix'>";
    
    $out .= '<fieldset>';
    $out .= '<legend>Question settings</legend>';
    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="questionMode">Question control mode';
    if(isset($validateMessages['questionMode']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['questionMode'].'</span>';
    $out .= '</label>';
    $questionMode_options = array(0=>"Teacher led (one question at a time)", 1=>"Student paced");
    $out .= '<br/><span class="forminput"><select name="questionMode" id="questionMode">';
    foreach($questionMode_options as $key => $val)
    {
        $out .= "<option";
        if(trim($key)==trim($questionMode))
            $out .= ' selected="1"';
        $out .= " value='$key'>{$val}</option>\n";
    }
    $out .= "</select></span></div>\n";

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="defaultQuActiveSecs">Default time limit for active questions (seconds, 0 for no limit).';
    if(isset($validateMessages['defaultQuActiveSecs']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['defaultQuActiveSecs'].'</span>';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><input type="text" name="defaultQuActiveSecs" id="defaultQuActiveSecs" value="'.$defaultQuActiveSecs.'" size="8"';
    $out .= "/></span></div>\n";

    $out .= '<div class="form-check row">';
    $out .= '<div class="col-sm-8 offset-sm-4">';
    $out .= '<input class="form-check-input" type="checkbox" name="allowQuReview" id="allowQuReview" value="1"';
    if($allowQuReview)
        $out .= ' checked="1"';
    $out .= '/>';
    $out .= '<label for="allowQuReview">Allow review/change of answers while response open';
    if(isset($validateMessages['allowQuReview']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['allowQuReview'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $out .= '<div class="form-check row">';
    $out .= '<div class="col-sm-8 offset-sm-4">';
    $out .= '<input class="form-check-input" type="checkbox" name="allowFullReview" id="allowFullReview" value="1"';
    if($allowFullReview)
        $out .= ' checked="1"';
    $out .= '/>';
    $out .= '<label for="allowFullReview">Allow students to view their answers after class.';
    if(isset($validateMessages['allowFullReview']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['allowFullReview'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $customScoring_options = array(""=>"None");
    if (is_dir('locallib/customscoring'))
    {
        if ($dh = opendir('locallib/customscoring'))
        {
            while (($file = readdir($dh)) !== false)
            {
                if(substr($file, strlen($file)-4) == '.php')
                    $customScoring_options[$file] = substr($file, 0, strlen($file)-4);
            }
            closedir($dh);
        }
    }

    //Only enable if some defined custom scoring
    if(sizeof($customScoring_options) <= 1)
    {
        $disabled = ' disabled="disabled"';
    }
    else
    {
        $disabled = '';
    }

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="customScoring">Custom scoring';
    if(isset($validateMessages['customScoring']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['customScoring'].'</span>';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><select name="customScoring" id="customScoring"'.$disabled.'>';
    foreach($customScoring_options as $key => $val)
    {
        $out .= "<option";
        if(trim($key)==trim($customScoring))
            $out .= ' selected="1"';
        $out .= " value='$key'>{$val}</option>\n";
    }
    $out .= "</select></span></div>\n";

    $out .= "</fieldset>";

    $out .= '<fieldset>';
    $out .= '<legend>Text/micro blogging settings</legend>';
    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="ublogRoom">Micro blogging mode';
    if(isset($validateMessages['ublogRoom']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['ublogRoom'].'</span>';
    $out .= '</label>';
    
    //# modified to limit options
    $ublogRoom_options = array(0=>"None", 1=>"Full class");
    $out .= '<br/><span class="forminput"><select name="ublogRoom" id="ublogRoom">';
    foreach($ublogRoom_options as $key => $val)
    {
        $out .= "<option";
        if(trim($key)==trim($ublogRoom))
            $out .= ' selected="1"';
        $out .= " value='$key'>{$val}</option>\n";
    }
    $out .= "</select></span></div>\n";

    //#Modified to disable unimplemented options...
    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="maxMessagelength">Maximum message length (characters)';
    if(isset($validateMessages['maxMessagelength']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['maxMessagelength'].'</span>';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><input type="text" name="maxMessagelength" id="maxMessagelength" value="'.$maxMessagelength.'" size="8" disabled="disabled"';
    $out .= "/></span></div>\n";

    $out .= '<div class="form-check row">';
    $out .= '<div class="col-sm-8 offset-sm-4">';
    $out .= '<input class="form-check-input" type="checkbox" name="allowTeacherQu" id="allowTeacherQu" value="1"  disabled="disabled"';
    if($allowTeacherQu)
        $out .= ' checked="1"';
    $out .= '/>';
    $out .= '<label for="allowTeacherQu">Allow questions for the teacher?';
    if(isset($validateMessages['allowTeacherQu']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['allowTeacherQu'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $out .= "</fieldset>";

    $out .= '<fieldset>';
    $out .= '<legend>Additional teachers</legend>';
    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="teachers">Additional users who can run session (comma delimited list of user IDs)';
    if(isset($validateMessages['teachers']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['teachers'].'</span>';
    $out .= '</label>';
    $out .= '<div class="col-sm-8"><input class="form-control" type="text" name="teachers" id="teachers" value="'.$teachers.'" size="80"';
    $out .= "/></div></div>\n";

    $out .= "</fieldset>";

    $out .= "</div>";
    $out .= "</fieldset>";

    $out .= '<div class="form-group row">';
    $out .= '<span class="col-sm-4 control-label">&nbsp;</span>';
    $out .= '<div class="col-sm-8">';
    $out .= '<input class="submit" name="editSession_submit" type="submit" value="Create" />';
    $out .= '<input class="submit" name="editSession_cancel" type="submit" value="Cancel" />';
    $out .= "</div></div>";

    $out .= "<script lang='JavaScript'>
document.getElementById(\"advanced_hide\").onclick = function()
{
    document.getElementById(\"advanced_hide\").style.display = \"none\";
    document.getElementById(\"advanced\").style.display = \"none\";
    document.getElementById(\"advanced_show\").style.display = \"block\";
}
document.getElementById(\"advanced_show\").onclick = function()
{
    document.getElementById(\"advanced_hide\").style.display = \"block\";
    document.getElementById(\"advanced\").style.display = \"block\";
    document.getElementById(\"advanced_show\").style.display = \"none\";
}
document.getElementById(\"advanced_hide\").onclick();
</script>";

    $out .= '</form>';
    return $out;
}

function editSession_submitted()
{
    if((isset($_REQUEST['editSession_code']))&&($_REQUEST['editSession_code']==editSession_magic))
        return true;
    else
        return false;
}

function update_from_editSession(&$sessionID, &$title, &$courseIdentifier, &$allowGuests, &$visible, &$questionMode, &$defaultQuActiveSecs, &$allowQuReview, &$allowFullReview, &$customScoring, &$ublogRoom, &$maxMessagelength, &$allowTeacherQu, &$teachers)
{
    if((isset($_REQUEST['editSession_code']))&&($_REQUEST['editSession_code']==editSession_magic))
    {
        if(isset($_REQUEST['editSession_cancel']))
            return false;
        $sessionID = strval($_REQUEST['sessionID']);
        $title = strval($_REQUEST['title']);
        $courseIdentifier = strval($_REQUEST['courseIdentifier']);
        $allowGuests = (isset($_REQUEST['allowGuests'])&&(intval($_REQUEST['allowGuests'])>0));
        $visible = (isset($_REQUEST['visible'])&&(intval($_REQUEST['visible'])>0));
        $questionMode = strval($_REQUEST['questionMode']);
        $defaultQuActiveSecs = intval($_REQUEST['defaultQuActiveSecs']);
        $allowQuReview = (isset($_REQUEST['allowQuReview'])&&(intval($_REQUEST['allowQuReview'])>0));
        $allowFullReview = (isset($_REQUEST['allowFullReview'])&&(intval($_REQUEST['allowFullReview'])>0));
        $customScoring = strval($_REQUEST['customScoring']);
        $ublogRoom = strval($_REQUEST['ublogRoom']);
        $maxMessagelength = intval($_REQUEST['maxMessagelength']);
        $allowTeacherQu = (isset($_REQUEST['allowTeacherQu'])&&(intval($_REQUEST['allowTeacherQu'])>0));
        $teachers = strval($_REQUEST['teachers']);
        return true;
    }
    else
    {
        return false;
    }
}

//Wrapper class for editSession QuickForm functions that emulates a form_lib2::nbform derived class.
class editSession
{
	var $sessionID; //hidden
    var $title; //string
    var $courseIdentifier; //string
    var $allowGuests; //boolean
    var $visible; //boolean
	var $questionMode; //select
	var $defaultQuActiveSecs; //integer
	var $allowQuReview; //boolean
	var $allowFullReview; //boolean
	var $customScoring; //select
	var $ublogRoom; //select
	var $maxMessagelength; //integer
	var $allowTeacherQu; //boolean
	var $teachers; //string
    var $validateMessages;
    
	function __construct($readform=true)
	{
		$this->validateMessages = array();
		if(editSession_submitted())
		{
			$this->readAndValidate();
		}
        else
        {
            $this->formStatus = FORM_NOTSUBMITTED;
        }
	}
	
    function getStatus()
    {
        return $this->formStatus;
    }

    function setData($data)
    {
        $this->sessionID = $data->sessionID;
        $this->title = $data->title;
        $this->courseIdentifier = $data->courseIdentifier;
        $this->allowGuests = $data->allowGuests;
        $this->visible = $data->visible;
		$this->questionMode = $data->questionMode;
		$this->defaultQuActiveSecs = $data->defaultQuActiveSecs;
		$this->allowQuReview = $data->allowQuReview;
		$this->allowFullReview = $data->allowFullReview;
		$this->customScoring = $data->customScoring;
		$this->ublogRoom = $data->ublogRoom;
		$this->maxMessagelength = $data->maxMessagelength;
		$this->allowTeacherQu = $data->allowTeacherQu;
		$this->teachers = $data->teachers;
    }

    function getData(&$data)
    {
        $data->sessionID = $this->sessionID;
        $data->title = $this->title;
        $data->courseIdentifier = $this->courseIdentifier;
        $data->allowGuests = $this->allowGuests;
        $data->visible = $this->visible;
		$data->questionMode = $this->questionMode;
		$data->defaultQuActiveSecs = $this->defaultQuActiveSecs;
		$data->allowQuReview = $this->allowQuReview;
		$data->allowFullReview = $this->allowFullReview;
		$data->customScoring = $this->customScoring;
		$data->ublogRoom = $this->ublogRoom;
		$data->maxMessagelength = $this->maxMessagelength;
		$data->allowTeacherQu = $this->allowTeacherQu;
		$data->teachers = $this->teachers;
        return $data;
    }
    
	private function readAndValidate()
	{
		if(update_from_editSession($this->sessionID, $this->title, $this->courseIdentifier, $this->allowGuests, $this->visible, $this->questionMode, $this->defaultQuActiveSecs, $this->allowQuReview, $this->allowFullReview, $this->customScoring, $this->ublogRoom, $this->maxMessagelength, $this->allowTeacherQu, $this->teachers))
        {
		    $isValid = $this->validate();
			if($isValid)
				$this->formStatus = FORM_SUBMITTED_VALID;
			else
				$this->formStatus = FORM_SUBMITTED_INVALID;
        }
        else
        {
            $this->formStatus = FORM_CANCELED;
        }
	}

	private function validate()
	{
		$this->validateMessages = array();
        //USERCODE-SECTION-editSession-sessionID-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editSession-sessionID-validation

        //USERCODE-SECTION-editSession-title-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editSession-title-validation

        //USERCODE-SECTION-editSession-courseIdentifier-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editSession-courseIdentifier-validation

        //USERCODE-SECTION-editSession-allowGuests-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editSession-allowGuests-validation

        //USERCODE-SECTION-editSession-visible-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editSession-visible-validation

        //USERCODE-SECTION-editSession-fld.name-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editSession-fld.name-validation

        //USERCODE-SECTION-editSession-fld.name-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editSession-fld.name-validation

        //USERCODE-SECTION-editSession-fld.name-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editSession-fld.name-validation

		if(sizeof($this->validateMessages)==0)
			return true;
		else
			return false;
	}

	function getHtml()
	{
        //# Needs to pass in $this->validateMessages, and possibly add a prefix section if $this->validateMessages is not empty;
        $out = show_editSession($this->sessionID, $this->title, $this->courseIdentifier, $this->allowGuests, $this->visible, $this->questionMode, $this->defaultQuActiveSecs, $this->allowQuReview, $this->allowFullReview, $this->customScoring, $this->ublogRoom, $this->maxMessagelength, $this->allowTeacherQu, $this->teachers, $this->validateMessages);
        return $out;
    }
}

/*
//Example of use of form editSession (Still needs work!)
$exampleform = new editSession();
switch($exampleform->getStatus())
{
    case FORM_NOTSUBMITTED:
        //$exampleform->setData($existingdata);
        $output = $exampleform->getHtml();
        break;
    case FORM_SUBMITTED_INVALID:
        $output = $exampleform->getHtml();
        break;
    case FORM_SUBMITTED_VALID:
        $data = new stdClass();
        $exampleform->getData($data);
        // Do stuff with $data
        // A redirect is likely here, e.g. header('Location:document.php?id='.$data->id);
        break;
    case FORM_CANCELED:
        header('Location:index.php');
        break;
}
*/

//USERCODE-SECTION-extra-functions
// Put code here.
//ENDUSERCODE-SECTION-extra-functions
?>
