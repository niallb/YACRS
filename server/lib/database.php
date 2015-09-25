<?php
require_once('corelib/dataaccess.php');

/*
//Database code generated using http://www.nbwebsites.com/wizards2/dbwiz/index.php 
//Some manual aditions will need restored following regeneration.
#tablePrefix yacrs_
#database MySQL

class lticonsumer
{
   primary key int id;
   unique string[40] keyHash;
   unique string[255] consumer_key;
   string[80] name;
   string[255] secret;
}

class session
{
   primary key int id;
   string[35] ownerID;
   string[80] title;
   datetime created;
   memo questions;
   int currentQuestion;
   int questionMode #0.2.0; // really an enum
   datetime endtime;
   datetime sessionstarttime;
   boolean sessionOpen;  // Open for posts and student paced questions
   subsession activeSubsession;
   datetime sessionendtime;
   boolean visible;
   boolean allowGuests;
   boolean multiSession #0.2.0; // Allows subsessions (i.e. multiple leactures)
   int ublogRoom #0.2.0; // really an enum
   int maxMessagelength #0.2.0; //
   boolean allowQuReview #0.2.0;  // Is this useful?
   boolean allowTeacherQu #0.2.0;
   string[20] courseIdentifier;
   int defaultQuActiveSecs;
   serialized extras;
}

class subsession #0.2.0
{
   primary key int id;
   session session;
   string[80] title;
   datetime starttime;
   datetime endtime;
}

class ltisessionlink
{
   primary key int id;
   lticonsumer client;
   string[255] resource_link_id;
   session session;
}

class userInfo #0.2.0
{
   primary key int id;
   unique string[80] username; // LDAP (or OpenID etc.) Username
   string[45] name;
   string[85] email;  // can be used for LTI association
   string[45] nickname; // used for ublogs
   string[20] phone;
   boolean sessionCreator;
   boolean isAdmin #0.3.1;
   serialized teacherPrefs;
}

class question
{
   primary key int id;
   string[35] ownerID; // May be empty for LTI sessions
   session session;
   string[80] title;
   serialized definition;
   string[20] responsetype; // identifier|integer|float|string
   boolean multiuse;
}

// for finding standard questions (e.g. YACRScontrol MCQs)
class systemQuestionLookup
{
   primary key int id;
   question qu;
   unique string[10] name;
}

class questionInstance
{
   primary key int id;
   string[80] title;
   question theQuestion;
   session inSession;
   subsession subsession;
   datetime starttime;
   datetime endtime;
   string[60] screenshot  #0.2.0;
   serialized extras #0.3.0;
}

class sessionMember
{
   primary key int id;
   session session;
   string[35] userID;  // May be LDAP username, phone number or a system alocated anonymous login
   string[45] name;
   string[45] nickname; // needed for microblog/chat
   string[85] email;
   userInfo user; // 0 if not associated with a real user
   datetime joined;
   datetime lastresponse;  //actually last interaction
   string[20] mobile;
}

class response
{
   primary key int id;
   sessionMember user;
   questionInstance question;
   memo value;
   boolean isPartial #0.3.2;
   datetime time;
}

class message #0.2.0
{
   primary key int id;
   sessionMember user;
   session session;  // Once subsessions exist this may be irrelevant
   subsession subsession;
   boolean isTeacherQu;  // Should this force anonymous?
   boolean private; // flag so private messages don't become public if session settngs changed.
   datetime posted;
   memo message; // Unlimited length in db, but expect a resonable &mu;blog length in settings;
   message replyTo;  // So messages can form conversations
   tag[] tags;
}

class tag #0.2.0
{
   primary key int id;
   string[20] text;
   session session;
   message[] messages;
}
*/

function initializeDataBase_yacrs()
{
	$query = "CREATE TABLE yacrs_lticonsumer(id INTEGER PRIMARY KEY AUTO_INCREMENT, keyHash VARCHAR(40), consumer_key VARCHAR(255), name VARCHAR(80), secret VARCHAR(255));";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_session(id INTEGER PRIMARY KEY AUTO_INCREMENT, ownerID VARCHAR(35), title VARCHAR(80), created DATETIME, questions TEXT, currentQuestion INTEGER, questionMode INTEGER, endtime DATETIME, sessionstarttime DATETIME, sessionOpen INTEGER, activeSubsession_id INTEGER, sessionendtime DATETIME, visible INTEGER, allowGuests INTEGER, multiSession INTEGER, ublogRoom INTEGER, maxMessagelength INTEGER, allowQuReview INTEGER, allowTeacherQu INTEGER, courseIdentifier VARCHAR(20), defaultQuActiveSecs INTEGER, extras TEXT);";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_subsession(id INTEGER PRIMARY KEY AUTO_INCREMENT, session_id INTEGER, title VARCHAR(80), starttime DATETIME, endtime DATETIME);";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_ltisessionlink(id INTEGER PRIMARY KEY AUTO_INCREMENT, client_id INTEGER, resource_link_id VARCHAR(255), session_id INTEGER);";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_userInfo(id INTEGER PRIMARY KEY AUTO_INCREMENT, username VARCHAR(80), name VARCHAR(45), email VARCHAR(85), nickname VARCHAR(45), phone VARCHAR(20), sessionCreator INTEGER, isAdmin INTEGER, teacherPrefs TEXT);";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_question(id INTEGER PRIMARY KEY AUTO_INCREMENT, ownerID VARCHAR(35), session_id INTEGER, title VARCHAR(80), definition TEXT, responsetype VARCHAR(20), multiuse INTEGER);";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_systemQuestionLookup(id INTEGER PRIMARY KEY AUTO_INCREMENT, qu_id INTEGER, name VARCHAR(10));";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_questionInstance(id INTEGER PRIMARY KEY AUTO_INCREMENT, title VARCHAR(80), theQuestion_id INTEGER, inSession_id INTEGER, subsession_id INTEGER, starttime DATETIME, endtime DATETIME, screenshot VARCHAR(60), extras TEXT);";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_sessionMember(id INTEGER PRIMARY KEY AUTO_INCREMENT, session_id INTEGER, userID VARCHAR(35), name VARCHAR(45), nickname VARCHAR(45), email VARCHAR(85), user_id INTEGER, joined DATETIME, lastresponse DATETIME, mobile VARCHAR(20));";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_response(id INTEGER PRIMARY KEY AUTO_INCREMENT, user_id INTEGER, question_id INTEGER, value TEXT, isPartial INTEGER, time DATETIME);";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_message(id INTEGER PRIMARY KEY AUTO_INCREMENT, user_id INTEGER, session_id INTEGER, subsession_id INTEGER, isTeacherQu INTEGER, private INTEGER, posted DATETIME, message TEXT, replyTo_id INTEGER);";
	dataConnection::runQuery($query);
    $query = "CREATE TABLE yacrs_message_tag_link(message_id INTEGER, tag_id INTEGER);";
	dataConnection::runQuery($query);
	$query = "CREATE TABLE yacrs_tag(id INTEGER PRIMARY KEY AUTO_INCREMENT, text VARCHAR(20), session_id INTEGER);";
	dataConnection::runQuery($query);
}

function updateDataBase_yacrs_v0_to_v0p2p0()
{
        // Add field questionMode to session
	$query = "ALTER TABLE session ADD COLUMN questionMode INTEGER;";
	dataConnection::runQuery($query);
        // Add field multiSession to session
	$query = "ALTER TABLE session ADD COLUMN multiSession INTEGER;";
	dataConnection::runQuery($query);
        // Add field ublogRoom to session
	$query = "ALTER TABLE session ADD COLUMN ublogRoom INTEGER;";
	dataConnection::runQuery($query);
        // Add field maxMessagelength to session
	$query = "ALTER TABLE session ADD COLUMN maxMessagelength INTEGER;";
	dataConnection::runQuery($query);
        // Add field allowQuReview to session
	$query = "ALTER TABLE session ADD COLUMN allowQuReview INTEGER;";
	dataConnection::runQuery($query);
        // Add field allowTeacherQu to session
	$query = "ALTER TABLE session ADD COLUMN allowTeacherQu INTEGER;";
	dataConnection::runQuery($query);
        // Add table subsession
	$query = "CREATE TABLE yacrs_subsession(id INTEGER PRIMARY KEY AUTO_INCREMENT, session_id INTEGER, title VARCHAR(80), starttime DATETIME, endtime DATETIME);";
	dataConnection::runQuery($query);
        // Add table userInfo
	$query = "CREATE TABLE yacrs_userInfo(id INTEGER PRIMARY KEY AUTO_INCREMENT, username VARCHAR(80), name VARCHAR(45), email VARCHAR(85), nickname VARCHAR(45), phone VARCHAR(20), sessionCreator INTEGER, isAdmin INTEGER, teacherPrefs TEXT);";
	dataConnection::runQuery($query);
        // Add field screenshot to questionInstance
	$query = "ALTER TABLE questionInstance ADD COLUMN screenshot VARCHAR(60);";
	dataConnection::runQuery($query);
        // Add table message
	$query = "CREATE TABLE yacrs_message(id INTEGER PRIMARY KEY AUTO_INCREMENT, user_id INTEGER, session_id INTEGER, subsession_id INTEGER, isTeacherQu INTEGER, private INTEGER, posted DATETIME, message TEXT, replyTo_id INTEGER);

CREATE TABLE yacrs_message_tag_link(message_id INTEGER, tag_id INTEGER);";
	dataConnection::runQuery($query);
        // Add table tag
	$query = "CREATE TABLE yacrs_tag(id INTEGER PRIMARY KEY AUTO_INCREMENT, text VARCHAR(20), session_id INTEGER);";
	dataConnection::runQuery($query);
}

function updateDataBase_yacrs_v0p2p0_to_v0p3p0()
{
        // Add field extras to questionInstance
	$query = "ALTER TABLE questionInstance ADD COLUMN extras TEXT;";
	dataConnection::runQuery($query);
}

function updateDataBase_yacrs_v0p3p0_to_v0p3p1()
{
        // Add field isAdmin to userInfo
	$query = "ALTER TABLE userInfo ADD COLUMN isAdmin INTEGER;";
	dataConnection::runQuery($query);
}

function updateDataBase_yacrs_v0p3p1_to_v0p3p2()
{
        // Add field isPartial to response
	$query = "ALTER TABLE response ADD COLUMN isPartial INTEGER;";
	dataConnection::runQuery($query);
}

//Skeleton PHP classes for data tables

class lticonsumer
{
	var $id; //primary key
	var $keyHash;
	var $consumer_key;
	var $name;
	var $secret;

	function lticonsumer($asArray=null)
	{
		$this->id = null; //primary key
		$this->keyHash = "";
		$this->consumer_key = "";
		$this->name = "";
		$this->secret = "";
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->keyHash = $asArray['keyHash'];
		$this->consumer_key = $asArray['consumer_key'];
		$this->name = $asArray['name'];
		$this->secret = $asArray['secret'];
	}

	static function retrieve_lticonsumer($id)
	{
		$query = "SELECT * FROM yacrs_lticonsumer WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new lticonsumer($result[0]);
		}
		else
			return false;
	}


	static function retrieve_by_keyHash($keyHash)
	{
		$query = "SELECT * FROM yacrs_lticonsumer WHERE keyHash='".dataConnection::safe($keyHash)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new lticonsumer($result[0]);
		}
		else
			return false;
	}


	static function retrieve_by_consumer_key($consumer_key)
	{
		$query = "SELECT * FROM yacrs_lticonsumer WHERE consumer_key='".dataConnection::safe($consumer_key)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new lticonsumer($result[0]);
		}
		else
			return false;
	}

	static function retrieve_lticonsumer_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_lticonsumer WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new lticonsumer($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_lticonsumer(keyHash, consumer_key, name, secret) VALUES(";
		$query .= "'".dataConnection::safe($this->keyHash)."', ";
		$query .= "'".dataConnection::safe($this->consumer_key)."', ";
		$query .= "'".dataConnection::safe($this->name)."', ";
		$query .= "'".dataConnection::safe($this->secret)."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_lticonsumer ";
		$query .= "SET keyHash='".dataConnection::safe($this->keyHash)."' ";
		$query .= ", consumer_key='".dataConnection::safe($this->consumer_key)."' ";
		$query .= ", name='".dataConnection::safe($this->name)."' ";
		$query .= ", secret='".dataConnection::safe($this->secret)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_lticonsumer WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<lticonsumer>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<keyHash>'.htmlentities($this->keyHash)."</keyHash>\n";
		$out .= '<consumer_key>'.htmlentities($this->consumer_key)."</consumer_key>\n";
		$out .= '<name>'.htmlentities($this->name)."</name>\n";
		$out .= '<secret>'.htmlentities($this->secret)."</secret>\n";
		$out .= "</lticonsumer>\n";
		return $out;
	}
	//[[USERCODE_lticonsumer]] Put code for custom class members in this block.

	static function retrieve_all_lticonsumer($from=0, $count=-1, $sort=null)
	{
 	    $query = "SELECT * FROM yacrs_lticonsumer ";
	    if($sort !== null)
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new lticonsumer($r);
	        return $output;
	    }
	    else
	        return false;
	}

	//[[USERCODE_lticonsumer]] WEnd of custom class members.
}

class session
{
	var $id; //primary key
	var $ownerID;
	var $title;
	var $created;
	var $questions;
	var $currentQuestion;
	var $questionMode;
	var $endtime;
	var $sessionstarttime;
	var $sessionOpen;
	var $activeSubsession_id; //foreign key
	var $sessionendtime;
	var $visible;
	var $allowGuests;
	var $multiSession;
	var $ublogRoom;
	var $maxMessagelength;
	var $allowQuReview;
	var $allowTeacherQu;
	var $courseIdentifier;
	var $defaultQuActiveSecs;
	var $extras;

	function session($asArray=null)
	{
		$this->id = null; //primary key
		$this->ownerID = "";
		$this->title = "";
		$this->created = time();
		$this->questions = "";
		$this->currentQuestion = "0";
		$this->questionMode = "0";
		$this->endtime = time();
		$this->sessionstarttime = time();
		$this->sessionOpen = false;
		$this->activeSubsession_id = null; // foreign key, needs dealt with.
		$this->sessionendtime = time();
		$this->visible = false;
		$this->allowGuests = false;
		$this->multiSession = false;
		$this->ublogRoom = "0";
		$this->maxMessagelength = "0";
		$this->allowQuReview = false;
		$this->allowTeacherQu = false;
		$this->courseIdentifier = "";
		$this->defaultQuActiveSecs = "0";
		$this->extras = false;
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->ownerID = $asArray['ownerID'];
		$this->title = $asArray['title'];
		$this->created = dataConnection::db2time($asArray['created']);
		$this->questions = $asArray['questions'];
		$this->currentQuestion = $asArray['currentQuestion'];
		$this->questionMode = $asArray['questionMode'];
		$this->endtime = dataConnection::db2time($asArray['endtime']);
		$this->sessionstarttime = dataConnection::db2time($asArray['sessionstarttime']);
		$this->sessionOpen = ($asArray['sessionOpen']==0)?false:true;
		$this->activeSubsession_id = $asArray['activeSubsession_id']; // foreign key, check code
		$this->sessionendtime = dataConnection::db2time($asArray['sessionendtime']);
		$this->visible = ($asArray['visible']==0)?false:true;
		$this->allowGuests = ($asArray['allowGuests']==0)?false:true;
		$this->multiSession = ($asArray['multiSession']==0)?false:true;
		$this->ublogRoom = $asArray['ublogRoom'];
		$this->maxMessagelength = $asArray['maxMessagelength'];
		$this->allowQuReview = ($asArray['allowQuReview']==0)?false:true;
		$this->allowTeacherQu = ($asArray['allowTeacherQu']==0)?false:true;
		$this->courseIdentifier = $asArray['courseIdentifier'];
		$this->defaultQuActiveSecs = $asArray['defaultQuActiveSecs'];
		$this->extras = unserialize($asArray['extras']);
	}

	static function retrieve_session($id)
	{
		$query = "SELECT * FROM yacrs_session WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new session($result[0]);
		}
		else
			return false;
	}

	static function retrieve_session_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_session WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new session($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_session(ownerID, title, created, questions, currentQuestion, questionMode, endtime, sessionstarttime, sessionOpen, activeSubsession_id, sessionendtime, visible, allowGuests, multiSession, ublogRoom, maxMessagelength, allowQuReview, allowTeacherQu, courseIdentifier, defaultQuActiveSecs, extras) VALUES(";
		$query .= "'".dataConnection::safe($this->ownerID)."', ";
		$query .= "'".dataConnection::safe($this->title)."', ";
		$query .= "'".dataConnection::time2db($this->created)."', ";
		$query .= "'".dataConnection::safe($this->questions)."', ";
		$query .= "'".dataConnection::safe($this->currentQuestion)."', ";
		$query .= "'".dataConnection::safe($this->questionMode)."', ";
		$query .= "'".dataConnection::time2db($this->endtime)."', ";
		$query .= "'".dataConnection::time2db($this->sessionstarttime)."', ";
		$query .= "'".(($this->sessionOpen===false)?0:1)."', ";
		if($this->activeSubsession_id!==null)
			$query .= "'".dataConnection::safe($this->activeSubsession_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::time2db($this->sessionendtime)."', ";
		$query .= "'".(($this->visible===false)?0:1)."', ";
		$query .= "'".(($this->allowGuests===false)?0:1)."', ";
		$query .= "'".(($this->multiSession===false)?0:1)."', ";
		$query .= "'".dataConnection::safe($this->ublogRoom)."', ";
		$query .= "'".dataConnection::safe($this->maxMessagelength)."', ";
		$query .= "'".(($this->allowQuReview===false)?0:1)."', ";
		$query .= "'".(($this->allowTeacherQu===false)?0:1)."', ";
		$query .= "'".dataConnection::safe($this->courseIdentifier)."', ";
		$query .= "'".dataConnection::safe($this->defaultQuActiveSecs)."', ";
		$query .= "'".dataConnection::safe(serialize($this->extras))."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_session ";
		$query .= "SET ownerID='".dataConnection::safe($this->ownerID)."' ";
		$query .= ", title='".dataConnection::safe($this->title)."' ";
		$query .= ", created='".dataConnection::time2db($this->created)."' ";
		$query .= ", questions='".dataConnection::safe($this->questions)."' ";
		$query .= ", currentQuestion='".dataConnection::safe($this->currentQuestion)."' ";
		$query .= ", questionMode='".dataConnection::safe($this->questionMode)."' ";
		$query .= ", endtime='".dataConnection::time2db($this->endtime)."' ";
		$query .= ", sessionstarttime='".dataConnection::time2db($this->sessionstarttime)."' ";
		$query .= ", sessionOpen='".(($this->sessionOpen===false)?0:1)."' ";
		$query .= ", activeSubsession_id='".dataConnection::safe($this->activeSubsession_id)."' ";
		$query .= ", sessionendtime='".dataConnection::time2db($this->sessionendtime)."' ";
		$query .= ", visible='".(($this->visible===false)?0:1)."' ";
		$query .= ", allowGuests='".(($this->allowGuests===false)?0:1)."' ";
		$query .= ", multiSession='".(($this->multiSession===false)?0:1)."' ";
		$query .= ", ublogRoom='".dataConnection::safe($this->ublogRoom)."' ";
		$query .= ", maxMessagelength='".dataConnection::safe($this->maxMessagelength)."' ";
		$query .= ", allowQuReview='".(($this->allowQuReview===false)?0:1)."' ";
		$query .= ", allowTeacherQu='".(($this->allowTeacherQu===false)?0:1)."' ";
		$query .= ", courseIdentifier='".dataConnection::safe($this->courseIdentifier)."' ";
		$query .= ", defaultQuActiveSecs='".dataConnection::safe($this->defaultQuActiveSecs)."' ";
		$query .= ", extras='".dataConnection::safe(serialize($this->extras))."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_session WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<session>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<ownerID>'.htmlentities($this->ownerID)."</ownerID>\n";
		$out .= '<title>'.htmlentities($this->title)."</title>\n";
		$out .= '<created>'.htmlentities($this->created)."</created>\n";
		$out .= '<questions>'.htmlentities($this->questions)."</questions>\n";
		$out .= '<currentQuestion>'.htmlentities($this->currentQuestion)."</currentQuestion>\n";
		$out .= '<questionMode>'.htmlentities($this->questionMode)."</questionMode>\n";
		$out .= '<endtime>'.htmlentities($this->endtime)."</endtime>\n";
		$out .= '<sessionstarttime>'.htmlentities($this->sessionstarttime)."</sessionstarttime>\n";
		$out .= '<sessionOpen>'.htmlentities($this->sessionOpen)."</sessionOpen>\n";
		$out .= '<activeSubsession>'.htmlentities($this->activeSubsession)."</activeSubsession>\n";
		$out .= '<sessionendtime>'.htmlentities($this->sessionendtime)."</sessionendtime>\n";
		$out .= '<visible>'.htmlentities($this->visible)."</visible>\n";
		$out .= '<allowGuests>'.htmlentities($this->allowGuests)."</allowGuests>\n";
		$out .= '<multiSession>'.htmlentities($this->multiSession)."</multiSession>\n";
		$out .= '<ublogRoom>'.htmlentities($this->ublogRoom)."</ublogRoom>\n";
		$out .= '<maxMessagelength>'.htmlentities($this->maxMessagelength)."</maxMessagelength>\n";
		$out .= '<allowQuReview>'.htmlentities($this->allowQuReview)."</allowQuReview>\n";
		$out .= '<allowTeacherQu>'.htmlentities($this->allowTeacherQu)."</allowTeacherQu>\n";
		$out .= '<courseIdentifier>'.htmlentities($this->courseIdentifier)."</courseIdentifier>\n";
		$out .= '<defaultQuActiveSecs>'.htmlentities($this->defaultQuActiveSecs)."</defaultQuActiveSecs>\n";
		$out .= '<extras>'.htmlentities($this->extras)."</extras>\n";
		$out .= "</session>\n";
		return $out;
	}
	//[[USERCODE_session]] Put code for custom class members in this block.

    static function deleteSession($id)
    {
        //Get the session
        $s = session::retrieve_session($id);
        //Delete each question instance (including images)
        if(strlen(trim($s->questions)))
        {
            $qis = explode(',',$s->questions);
            foreach($qis as $qi)
            {
            	questionInstance::deleteInstance($qi);
            }
        }
        //Delete sessionmember links
        $s->clearSessionMembers();
        //Delete any blog posts
        $s->clearSessionMessages();
        //Delete the session.
		$query = "DELETE FROM yacrs_session WHERE id='{$id}';";
		dataConnection::runQuery($query);
    }

    private function clearSessionMembers()
    {
		$query = "DELETE FROM yacrs_sessionMember WHERE session_id='{$this->id}';";
		dataConnection::runQuery($query);
    }

    private function clearSessionMessages()
    {
        $query = "DELETE FROM yacrs_message_tag_link WHERE `message_id` IN (SELECT id FROM yacrs_message WHERE session_id='$this->id');";
		dataConnection::runQuery($query);
		$query = "DELETE FROM yacrs_message WHERE session_id='{$this->id}';";
		dataConnection::runQuery($query);
    }

    function addQuestion($qu)
    {
       $qi = new questionInstance();
       $qi->theQuestion_id = $qu->id;
	   $qi->inSession_id = $this->id;
       $qi->title = $qu->title;
       $qi->insert();
       $this->questions = trim($this->questions.','.$qi->id," \t\r\n,");
       $this->update();
       return $qi;
    }

    function isStaffInSession($userid)
    {
    	if(trim($userid)==trim($this->ownerID))
            return true;
        else
            return false;
    }

	static function retrieve_all_sessions($from=0, $count=-1, $sort=null)
	{
 	    $query = "SELECT * FROM yacrs_session ";
	    if($sort !== null)
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new session($r);
	        return $output;
	    }
	    else
	        return false;
	}

	//[[USERCODE_session]] WEnd of custom class members.
}

class subsession
{
	var $id; //primary key
	var $session_id; //foreign key
	var $title;
	var $starttime;
	var $endtime;

	function subsession($asArray=null)
	{
		$this->id = null; //primary key
		$this->session_id = null; // foreign key, needs dealt with.
		$this->title = "";
		$this->starttime = time();
		$this->endtime = time();
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->session_id = $asArray['session_id']; // foreign key, check code
		$this->title = $asArray['title'];
		$this->starttime = dataConnection::db2time($asArray['starttime']);
		$this->endtime = dataConnection::db2time($asArray['endtime']);
	}

	static function retrieve_subsession($id)
	{
		$query = "SELECT * FROM yacrs_subsession WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new subsession($result[0]);
		}
		else
			return false;
	}

	static function retrieve_subsession_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_subsession WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new subsession($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_subsession(session_id, title, starttime, endtime) VALUES(";
		if($this->session_id!==null)
			$query .= "'".dataConnection::safe($this->session_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::safe($this->title)."', ";
		$query .= "'".dataConnection::time2db($this->starttime)."', ";
		$query .= "'".dataConnection::time2db($this->endtime)."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_subsession ";
		$query .= "SET session_id='".dataConnection::safe($this->session_id)."' ";
		$query .= ", title='".dataConnection::safe($this->title)."' ";
		$query .= ", starttime='".dataConnection::time2db($this->starttime)."' ";
		$query .= ", endtime='".dataConnection::time2db($this->endtime)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_subsession WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<subsession>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<session>'.htmlentities($this->session)."</session>\n";
		$out .= '<title>'.htmlentities($this->title)."</title>\n";
		$out .= '<starttime>'.htmlentities($this->starttime)."</starttime>\n";
		$out .= '<endtime>'.htmlentities($this->endtime)."</endtime>\n";
		$out .= "</subsession>\n";
		return $out;
	}
	//[[USERCODE_subsession]] Put code for custom class members in this block.

	//[[USERCODE_subsession]] WEnd of custom class members.
}

class ltisessionlink
{
	var $id; //primary key
	var $client_id; //foreign key
	var $resource_link_id;
	var $session_id; //foreign key

	function ltisessionlink($asArray=null)
	{
		$this->id = null; //primary key
		$this->client_id = null; // foreign key, needs dealt with.
		$this->resource_link_id = "";
		$this->session_id = null; // foreign key, needs dealt with.
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->client_id = $asArray['client_id']; // foreign key, check code
		$this->resource_link_id = $asArray['resource_link_id'];
		$this->session_id = $asArray['session_id']; // foreign key, check code
	}

	static function retrieve_ltisessionlink($id)
	{
		$query = "SELECT * FROM yacrs_ltisessionlink WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new ltisessionlink($result[0]);
		}
		else
			return false;
	}

	static function retrieve_ltisessionlink_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_ltisessionlink WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new ltisessionlink($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_ltisessionlink(client_id, resource_link_id, session_id) VALUES(";
		if($this->client_id!==null)
			$query .= "'".dataConnection::safe($this->client_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::safe($this->resource_link_id)."', ";
		if($this->session_id!==null)
			$query .= "'".dataConnection::safe($this->session_id)."');";
		else
			$query .= "null);";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_ltisessionlink ";
		$query .= "SET client_id='".dataConnection::safe($this->client_id)."' ";
		$query .= ", resource_link_id='".dataConnection::safe($this->resource_link_id)."' ";
		$query .= ", session_id='".dataConnection::safe($this->session_id)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_ltisessionlink WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<ltisessionlink>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<client>'.htmlentities($this->client)."</client>\n";
		$out .= '<resource_link_id>'.htmlentities($this->resource_link_id)."</resource_link_id>\n";
		$out .= '<session>'.htmlentities($this->session)."</session>\n";
		$out .= "</ltisessionlink>\n";
		return $out;
	}
	//[[USERCODE_ltisessionlink]] Put code for custom class members in this block.

	//[[USERCODE_ltisessionlink]] WEnd of custom class members.
}

class userInfo
{
	var $id; //primary key
	var $username;
	var $name;
	var $email;
	var $nickname;
	var $phone;
	var $sessionCreator;
	var $isAdmin;
	var $teacherPrefs;

	function userInfo($asArray=null)
	{
		$this->id = null; //primary key
		$this->username = "";
		$this->name = "";
		$this->email = "";
		$this->nickname = "";
		$this->phone = "";
		$this->sessionCreator = false;
		$this->isAdmin = false;
		$this->teacherPrefs = false;
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->username = $asArray['username'];
		$this->name = $asArray['name'];
		$this->email = $asArray['email'];
		$this->nickname = $asArray['nickname'];
		$this->phone = $asArray['phone'];
		$this->sessionCreator = ($asArray['sessionCreator']==0)?false:true;
		$this->isAdmin = ($asArray['isAdmin']==0)?false:true;
		$this->teacherPrefs = unserialize($asArray['teacherPrefs']);
	}

	static function retrieve_userInfo($id)
	{
		$query = "SELECT * FROM yacrs_userInfo WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new userInfo($result[0]);
		}
		else
			return false;
	}


	static function retrieve_by_username($username)
	{
		$query = "SELECT * FROM yacrs_userInfo WHERE username='".dataConnection::safe($username)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new userInfo($result[0]);
		}
		else
			return false;
	}

	static function retrieve_userInfo_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_userInfo WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new userInfo($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_userInfo(username, name, email, nickname, phone, sessionCreator, isAdmin, teacherPrefs) VALUES(";
		$query .= "'".dataConnection::safe($this->username)."', ";
		$query .= "'".dataConnection::safe($this->name)."', ";
		$query .= "'".dataConnection::safe($this->email)."', ";
		$query .= "'".dataConnection::safe($this->nickname)."', ";
		$query .= "'".dataConnection::safe($this->phone)."', ";
		$query .= "'".(($this->sessionCreator===false)?0:1)."', ";
		$query .= "'".(($this->isAdmin===false)?0:1)."', ";
		$query .= "'".dataConnection::safe(serialize($this->teacherPrefs))."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_userInfo ";
		$query .= "SET username='".dataConnection::safe($this->username)."' ";
		$query .= ", name='".dataConnection::safe($this->name)."' ";
		$query .= ", email='".dataConnection::safe($this->email)."' ";
		$query .= ", nickname='".dataConnection::safe($this->nickname)."' ";
		$query .= ", phone='".dataConnection::safe($this->phone)."' ";
		$query .= ", sessionCreator='".(($this->sessionCreator===false)?0:1)."' ";
		$query .= ", isAdmin='".(($this->isAdmin===false)?0:1)."' ";
		$query .= ", teacherPrefs='".dataConnection::safe(serialize($this->teacherPrefs))."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_userInfo WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<userInfo>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<username>'.htmlentities($this->username)."</username>\n";
		$out .= '<name>'.htmlentities($this->name)."</name>\n";
		$out .= '<email>'.htmlentities($this->email)."</email>\n";
		$out .= '<nickname>'.htmlentities($this->nickname)."</nickname>\n";
		$out .= '<phone>'.htmlentities($this->phone)."</phone>\n";
		$out .= '<sessionCreator>'.htmlentities($this->sessionCreator)."</sessionCreator>\n";
		$out .= '<isAdmin>'.htmlentities($this->isAdmin)."</isAdmin>\n";
		$out .= '<teacherPrefs>'.htmlentities($this->teacherPrefs)."</teacherPrefs>\n";
		$out .= "</userInfo>\n";
		return $out;
	}
	//[[USERCODE_userInfo]] Put code for custom class members in this block.

	static function retrieveByMobileNo($mobileNo)
	{
	    $query = "SELECT * FROM yacrs_userInfo WHERE phone='".dataConnection::safe($mobileNo)."';";
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)==1)    // If duplicated just return none for now...
	    {
	        $output = new userInfo($result[0]);
	        return $output;
	    }
	    else
	        return false;
	}

	static function retrieve_all_userInfo($from=0, $count=-1, $sort=null)
	{
 	    $query = "SELECT * FROM yacrs_userInfo ";
	    if($sort !== null)
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new userInfo($r);
	        return $output;
	    }
	    else
	        return false;
	}

	static function search_userInfo($searchTerm, $from=0, $count=-1)
	{
 	    $query = "SELECT * FROM yacrs_userInfo";
        $query .= " WHERE username LIKE '%".dataConnection::safe($searchTerm)."%'";
        $query .= " OR name LIKE '%".dataConnection::safe($searchTerm)."%'";
        $query .= " ORDER BY name ASC";
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new userInfo($r);
	        return $output;
	    }
	    else
	        return false;
	}

	//[[USERCODE_userInfo]] WEnd of custom class members.
}

class question
{
	var $id; //primary key
	var $ownerID;
	var $session_id; //foreign key
	var $title;
	var $definition;
	var $responsetype;
	var $multiuse;

	function question($asArray=null)
	{
		$this->id = null; //primary key
		$this->ownerID = "";
		$this->session_id = null; // foreign key, needs dealt with.
		$this->title = "";
		$this->definition = false;
		$this->responsetype = "";
		$this->multiuse = false;
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->ownerID = $asArray['ownerID'];
		$this->session_id = $asArray['session_id']; // foreign key, check code
		$this->title = $asArray['title'];
		$this->definition = unserialize($asArray['definition']);
		$this->responsetype = $asArray['responsetype'];
		$this->multiuse = ($asArray['multiuse']==0)?false:true;
	}

	static function retrieve_question($id)
	{
		$query = "SELECT * FROM yacrs_question WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new question($result[0]);
		}
		else
			return false;
	}

	static function retrieve_question_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_question WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new question($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_question(ownerID, session_id, title, definition, responsetype, multiuse) VALUES(";
		$query .= "'".dataConnection::safe($this->ownerID)."', ";
		if($this->session_id!==null)
			$query .= "'".dataConnection::safe($this->session_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::safe($this->title)."', ";
		$query .= "'".dataConnection::safe(serialize($this->definition))."', ";
		$query .= "'".dataConnection::safe($this->responsetype)."', ";
		$query .= "'".(($this->multiuse===false)?0:1)."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_question ";
		$query .= "SET ownerID='".dataConnection::safe($this->ownerID)."' ";
		$query .= ", session_id='".dataConnection::safe($this->session_id)."' ";
		$query .= ", title='".dataConnection::safe($this->title)."' ";
		$query .= ", definition='".dataConnection::safe(serialize($this->definition))."' ";
		$query .= ", responsetype='".dataConnection::safe($this->responsetype)."' ";
		$query .= ", multiuse='".(($this->multiuse===false)?0:1)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_question WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<question>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<ownerID>'.htmlentities($this->ownerID)."</ownerID>\n";
		$out .= '<session>'.htmlentities($this->session)."</session>\n";
		$out .= '<title>'.htmlentities($this->title)."</title>\n";
		$out .= '<definition>'.htmlentities($this->definition)."</definition>\n";
		$out .= '<responsetype>'.htmlentities($this->responsetype)."</responsetype>\n";
		$out .= '<multiuse>'.htmlentities($this->multiuse)."</multiuse>\n";
		$out .= "</question>\n";
		return $out;
	}
	//[[USERCODE_question]] Put code for custom class members in this block.

    function getUserReuseList($uid)
    {
	    $query = "SELECT id, title FROM yacrs_question WHERE ownerID='".dataConnection::safe($uid)."'";
	    $query .= " AND multiuse='1'";
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
        $output = array();
	    if(sizeof($result)!=0)
	    {
	        foreach($result as $r)
	            $output[$r['id']] = $r['title'];
	    }
	    return $output;
    }

    function getSessionReuseList($sessionID)
    {
	    $query = "SELECT id, title FROM yacrs_question WHERE session_id='".dataConnection::safe($sessionID)."'";
	    $query .= " AND multiuse='0'";
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
        $output = array();
	    if(sizeof($result)!=0)
	    {
	        foreach($result as $r)
	            $output[$r['id']] = $r['title'];
	    }
	    return $output;
    }

    function getSystemReuseList($sessionID)
    {
	    $query = "SELECT id, title FROM yacrs_question WHERE id in (SELECT qu_id FROM yacrs_systemQuestionLookup WHERE 1);";
	    $result = dataConnection::runQuery($query);
        $output = array();
	    if(sizeof($result)!=0)
	    {
	        foreach($result as $r)
	            $output[$r['id']] = 'Generic '.$r['title'];
	    }
	    return $output;
    }



	//[[USERCODE_question]] WEnd of custom class members.
}

class systemQuestionLookup
{
	var $id; //primary key
	var $qu_id; //foreign key
	var $name;

	function systemQuestionLookup($asArray=null)
	{
		$this->id = null; //primary key
		$this->qu_id = null; // foreign key, needs dealt with.
		$this->name = "";
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->qu_id = $asArray['qu_id']; // foreign key, check code
		$this->name = $asArray['name'];
	}

	static function retrieve_systemQuestionLookup($id)
	{
		$query = "SELECT * FROM yacrs_systemQuestionLookup WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new systemQuestionLookup($result[0]);
		}
		else
			return false;
	}


	static function retrieve_by_name($name)
	{
		$query = "SELECT * FROM yacrs_systemQuestionLookup WHERE name='".dataConnection::safe($name)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new systemQuestionLookup($result[0]);
		}
		else
			return false;
	}

	static function retrieve_systemQuestionLookup_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_systemQuestionLookup WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new systemQuestionLookup($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_systemQuestionLookup(qu_id, name) VALUES(";
		if($this->qu_id!==null)
			$query .= "'".dataConnection::safe($this->qu_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::safe($this->name)."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_systemQuestionLookup ";
		$query .= "SET qu_id='".dataConnection::safe($this->qu_id)."' ";
		$query .= ", name='".dataConnection::safe($this->name)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_systemQuestionLookup WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<systemQuestionLookup>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<qu>'.htmlentities($this->qu)."</qu>\n";
		$out .= '<name>'.htmlentities($this->name)."</name>\n";
		$out .= "</systemQuestionLookup>\n";
		return $out;
	}
	//[[USERCODE_systemQuestionLookup]] Put code for custom class members in this block.
	static function all()
	{
		$query = "SELECT * FROM yacrs_systemQuestionLookup WHERE 1;";
		$result = dataConnection::runQuery($query);
        $output = array();
	    if(sizeof($result)!=0)
	    {
	        foreach($result as $r)
	            $output[] = new systemQuestionLookup($r);
	    }
        return $output;
	}


	//[[USERCODE_systemQuestionLookup]] WEnd of custom class members.
}

class questionInstance
{
	var $id; //primary key
	var $title;
	var $theQuestion_id; //foreign key
	var $inSession_id; //foreign key
	var $subsession_id; //foreign key
	var $starttime;
	var $endtime;
	var $screenshot;
	var $extras;

	function questionInstance($asArray=null)
	{
		$this->id = null; //primary key
		$this->title = "";
		$this->theQuestion_id = null; // foreign key, needs dealt with.
		$this->inSession_id = null; // foreign key, needs dealt with.
		$this->subsession_id = null; // foreign key, needs dealt with.
		$this->starttime = time();
		$this->endtime = time();
		$this->screenshot = "";
		$this->extras = false;
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->title = $asArray['title'];
		$this->theQuestion_id = $asArray['theQuestion_id']; // foreign key, check code
		$this->inSession_id = $asArray['inSession_id']; // foreign key, check code
		$this->subsession_id = $asArray['subsession_id']; // foreign key, check code
		$this->starttime = dataConnection::db2time($asArray['starttime']);
		$this->endtime = dataConnection::db2time($asArray['endtime']);
		$this->screenshot = $asArray['screenshot'];
		$this->extras = unserialize($asArray['extras']);
	}

	static function retrieve_questionInstance($id)
	{
		$query = "SELECT * FROM yacrs_questionInstance WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new questionInstance($result[0]);
		}
		else
			return false;
	}

	static function retrieve_questionInstance_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_questionInstance WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new questionInstance($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_questionInstance(title, theQuestion_id, inSession_id, subsession_id, starttime, endtime, screenshot, extras) VALUES(";
		$query .= "'".dataConnection::safe($this->title)."', ";
		if($this->theQuestion_id!==null)
			$query .= "'".dataConnection::safe($this->theQuestion_id)."', ";
		else
			$query .= "null, ";
		if($this->inSession_id!==null)
			$query .= "'".dataConnection::safe($this->inSession_id)."', ";
		else
			$query .= "null, ";
		if($this->subsession_id!==null)
			$query .= "'".dataConnection::safe($this->subsession_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::time2db($this->starttime)."', ";
		$query .= "'".dataConnection::time2db($this->endtime)."', ";
		$query .= "'".dataConnection::safe($this->screenshot)."', ";
		$query .= "'".dataConnection::safe(serialize($this->extras))."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_questionInstance ";
		$query .= "SET title='".dataConnection::safe($this->title)."' ";
		$query .= ", theQuestion_id='".dataConnection::safe($this->theQuestion_id)."' ";
		$query .= ", inSession_id='".dataConnection::safe($this->inSession_id)."' ";
		$query .= ", subsession_id='".dataConnection::safe($this->subsession_id)."' ";
		$query .= ", starttime='".dataConnection::time2db($this->starttime)."' ";
		$query .= ", endtime='".dataConnection::time2db($this->endtime)."' ";
		$query .= ", screenshot='".dataConnection::safe($this->screenshot)."' ";
		$query .= ", extras='".dataConnection::safe(serialize($this->extras))."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_questionInstance WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<questionInstance>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<title>'.htmlentities($this->title)."</title>\n";
		$out .= '<theQuestion>'.htmlentities($this->theQuestion)."</theQuestion>\n";
		$out .= '<inSession>'.htmlentities($this->inSession)."</inSession>\n";
		$out .= '<subsession>'.htmlentities($this->subsession)."</subsession>\n";
		$out .= '<starttime>'.htmlentities($this->starttime)."</starttime>\n";
		$out .= '<endtime>'.htmlentities($this->endtime)."</endtime>\n";
		$out .= '<screenshot>'.htmlentities($this->screenshot)."</screenshot>\n";
		$out .= '<extras>'.htmlentities($this->extras)."</extras>\n";
		$out .= "</questionInstance>\n";
		return $out;
	}
	//[[USERCODE_questionInstance]] Put code for custom class members in this block.

    static function deleteInstance($id)
    {
		$query = "DELETE FROM yacrs_response WHERE question_id='$id';";
		dataConnection::runQuery($query);
        $qi = questionInstance::retrieve_questionInstance($id);
        if((strlen($qi->screenshot))&&(file_exists($qi->screenshot)))
        {
            unlink($qi->screenshot);
        }
		$query = "DELETE FROM yacrs_questionInstance WHERE id='$id';";
		dataConnection::runQuery($query);
    }

	static function retrieve_questionInstanceIDs_matching($session, $title)
	{
	    $query = "SELECT id FROM yacrs_questionInstance WHERE inSession_id='".dataConnection::safe($session)."'";
	    $query .= " AND LOWER(TRIM(title))='".dataConnection::safe(strtolower($title))."';";
 	    $result = dataConnection::runQuery($query);
        //echo $query.'<pre>'.print_r($result,1).'</pre>';
        $output = array();
	    if(sizeof($result)!=0)
	    {
	        foreach($result as $r)
	            $output[] = $r['id'];
	    }
        return $output;
	}

	//[[USERCODE_questionInstance]] WEnd of custom class members.
}

class sessionMember
{
	var $id; //primary key
	var $session_id; //foreign key
	var $userID;
	var $name;
	var $nickname;
	var $email;
	var $user_id; //foreign key
	var $joined;
	var $lastresponse;
	var $mobile;

	function sessionMember($asArray=null)
	{
		$this->id = null; //primary key
		$this->session_id = null; // foreign key, needs dealt with.
		$this->userID = "";
		$this->name = "";
		$this->nickname = "";
		$this->email = "";
		$this->user_id = null; // foreign key, needs dealt with.
		$this->joined = time();
		$this->lastresponse = time();
		$this->mobile = "";
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->session_id = $asArray['session_id']; // foreign key, check code
		$this->userID = $asArray['userID'];
		$this->name = $asArray['name'];
		$this->nickname = $asArray['nickname'];
		$this->email = $asArray['email'];
		$this->user_id = $asArray['user_id']; // foreign key, check code
		$this->joined = dataConnection::db2time($asArray['joined']);
		$this->lastresponse = dataConnection::db2time($asArray['lastresponse']);
		$this->mobile = $asArray['mobile'];
	}

	static function retrieve_sessionMember($id)
	{
		$query = "SELECT * FROM yacrs_sessionMember WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new sessionMember($result[0]);
		}
		else
			return false;
	}

	static function retrieve_sessionMember_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_sessionMember WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new sessionMember($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_sessionMember(session_id, userID, name, nickname, email, user_id, joined, lastresponse, mobile) VALUES(";
		if($this->session_id!==null)
			$query .= "'".dataConnection::safe($this->session_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::safe($this->userID)."', ";
		$query .= "'".dataConnection::safe($this->name)."', ";
		$query .= "'".dataConnection::safe($this->nickname)."', ";
		$query .= "'".dataConnection::safe($this->email)."', ";
		if($this->user_id!==null)
			$query .= "'".dataConnection::safe($this->user_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::time2db($this->joined)."', ";
		$query .= "'".dataConnection::time2db($this->lastresponse)."', ";
		$query .= "'".dataConnection::safe($this->mobile)."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_sessionMember ";
		$query .= "SET session_id='".dataConnection::safe($this->session_id)."' ";
		$query .= ", userID='".dataConnection::safe($this->userID)."' ";
		$query .= ", name='".dataConnection::safe($this->name)."' ";
		$query .= ", nickname='".dataConnection::safe($this->nickname)."' ";
		$query .= ", email='".dataConnection::safe($this->email)."' ";
		$query .= ", user_id='".dataConnection::safe($this->user_id)."' ";
		$query .= ", joined='".dataConnection::time2db($this->joined)."' ";
		$query .= ", lastresponse='".dataConnection::time2db($this->lastresponse)."' ";
		$query .= ", mobile='".dataConnection::safe($this->mobile)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_sessionMember WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<sessionMember>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<session>'.htmlentities($this->session)."</session>\n";
		$out .= '<userID>'.htmlentities($this->userID)."</userID>\n";
		$out .= '<name>'.htmlentities($this->name)."</name>\n";
		$out .= '<nickname>'.htmlentities($this->nickname)."</nickname>\n";
		$out .= '<email>'.htmlentities($this->email)."</email>\n";
		$out .= '<user>'.htmlentities($this->user)."</user>\n";
		$out .= '<joined>'.htmlentities($this->joined)."</joined>\n";
		$out .= '<lastresponse>'.htmlentities($this->lastresponse)."</lastresponse>\n";
		$out .= '<mobile>'.htmlentities($this->mobile)."</mobile>\n";
		$out .= "</sessionMember>\n";
		return $out;
	}
	//[[USERCODE_sessionMember]] Put code for custom class members in this block.

	static function retrieve($userID, $session_id)
	{
        //userID VARCHAR(35), name VARCHAR(45), email VARCHAR(85), joined DATETIME, lastresponse DATETIME
	    $query = "SELECT * FROM yacrs_sessionMember WHERE userID='".dataConnection::safe($userID)."' AND session_id='".dataConnection::safe($session_id)."';";
	    $result = dataConnection::runQuery($query);
         //exit();
	    if(sizeof($result)!=0)
	    {
			return new sessionMember($result[0]);
	    }
	    else
	        return false;
	}

	static function countActive($sessionID, $lastMin=20)
	{
        $since = dataConnection::time2db(time()-(60*$lastMin));
		$query = "SELECT COUNT(*) AS count FROM yacrs_sessionMember WHERE session_id='".dataConnection::safe($sessionID)."' AND lastresponse > '$since';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	static function retrieveByMobileNo($mobileNo)
	{
	    $query = "SELECT * FROM yacrs_sessionMember WHERE mobile='".dataConnection::safe($mobileNo)."'";
        $query .= ' ORDER BY joined DESC LIMIT 1;';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = new sessionMember($result[0]);
	        return $output;
	    }
	    else
	        return false;
	}

	//[[USERCODE_sessionMember]] WEnd of custom class members.
}

class response
{
	var $id; //primary key
	var $user_id; //foreign key
	var $question_id; //foreign key
	var $value;
	var $isPartial;
	var $time;

	function response($asArray=null)
	{
		$this->id = null; //primary key
		$this->user_id = null; // foreign key, needs dealt with.
		$this->question_id = null; // foreign key, needs dealt with.
		$this->value = "";
		$this->isPartial = false;
		$this->time = time();
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->user_id = $asArray['user_id']; // foreign key, check code
		$this->question_id = $asArray['question_id']; // foreign key, check code
		$this->value = $asArray['value'];
		$this->isPartial = ($asArray['isPartial']==0)?false:true;
		$this->time = dataConnection::db2time($asArray['time']);
	}

	static function retrieve_response($id)
	{
		$query = "SELECT * FROM yacrs_response WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new response($result[0]);
		}
		else
			return false;
	}

	static function retrieve_response_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_response WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new response($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_response(user_id, question_id, value, isPartial, time) VALUES(";
		if($this->user_id!==null)
			$query .= "'".dataConnection::safe($this->user_id)."', ";
		else
			$query .= "null, ";
		if($this->question_id!==null)
			$query .= "'".dataConnection::safe($this->question_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".dataConnection::safe($this->value)."', ";
		$query .= "'".(($this->isPartial===false)?0:1)."', ";
		$query .= "'".dataConnection::time2db($this->time)."');";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_response ";
		$query .= "SET user_id='".dataConnection::safe($this->user_id)."' ";
		$query .= ", question_id='".dataConnection::safe($this->question_id)."' ";
		$query .= ", value='".dataConnection::safe($this->value)."' ";
		$query .= ", isPartial='".(($this->isPartial===false)?0:1)."' ";
		$query .= ", time='".dataConnection::time2db($this->time)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_response WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	function toXML()
	{
		$out = "<response>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<user>'.htmlentities($this->user)."</user>\n";
		$out .= '<question>'.htmlentities($this->question)."</question>\n";
		$out .= '<value>'.htmlentities($this->value)."</value>\n";
		$out .= '<isPartial>'.htmlentities($this->isPartial)."</isPartial>\n";
		$out .= '<time>'.htmlentities($this->time)."</time>\n";
		$out .= "</response>\n";
		return $out;
	}
	//[[USERCODE_response]] Put code for custom class members in this block.
	static function retrieve($user_id, $qi_id)
	{
        //userID VARCHAR(35), name VARCHAR(45), email VARCHAR(85), joined DATETIME, lastresponse DATETIME
	    $query = "SELECT * FROM yacrs_response WHERE user_id='".dataConnection::safe($user_id)."' AND question_id='".dataConnection::safe($qi_id)."';";
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
			return new response($result[0]);
	    }
	    else
	        return false;
	}

	static function countCompleted($quid)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_response WHERE question_id='".dataConnection::safe($quid)."' AND (isPartial='0' OR isPartial is null);";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	static function countAllInLastHour()
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_response WHERE ";
		$query .= "time > '".dataConnection::time2db(time()-3600)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	//[[USERCODE_response]] WEnd of custom class members.
}

class message
{
	var $id; //primary key
	var $user_id; //foreign key
	var $session_id; //foreign key
	var $subsession_id; //foreign key
	var $isTeacherQu;
	var $private;
	var $posted;
	var $message;
	var $replyTo_id; //foreign key

	function message($asArray=null)
	{
		$this->id = null; //primary key
		$this->user_id = null; // foreign key, needs dealt with.
		$this->session_id = null; // foreign key, needs dealt with.
		$this->subsession_id = null; // foreign key, needs dealt with.
		$this->isTeacherQu = false;
		$this->private = false;
		$this->posted = time();
		$this->message = "";
		$this->replyTo_id = null; // foreign key, needs dealt with.
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->user_id = $asArray['user_id']; // foreign key, check code
		$this->session_id = $asArray['session_id']; // foreign key, check code
		$this->subsession_id = $asArray['subsession_id']; // foreign key, check code
		$this->isTeacherQu = ($asArray['isTeacherQu']==0)?false:true;
		$this->private = ($asArray['private']==0)?false:true;
		$this->posted = dataConnection::db2time($asArray['posted']);
		$this->message = $asArray['message'];
		$this->replyTo_id = $asArray['replyTo_id']; // foreign key, check code
	}

	static function retrieve_message($id)
	{
		$query = "SELECT * FROM yacrs_message WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new message($result[0]);
		}
		else
			return false;
	}

	static function retrieve_message_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_message WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new message($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_message(user_id, session_id, subsession_id, isTeacherQu, private, posted, message, replyTo_id) VALUES(";
		if($this->user_id!==null)
			$query .= "'".dataConnection::safe($this->user_id)."', ";
		else
			$query .= "null, ";
		if($this->session_id!==null)
			$query .= "'".dataConnection::safe($this->session_id)."', ";
		else
			$query .= "null, ";
		if($this->subsession_id!==null)
			$query .= "'".dataConnection::safe($this->subsession_id)."', ";
		else
			$query .= "null, ";
		$query .= "'".(($this->isTeacherQu===false)?0:1)."', ";
		$query .= "'".(($this->private===false)?0:1)."', ";
		$query .= "'".dataConnection::time2db($this->posted)."', ";
		$query .= "'".dataConnection::safe($this->message)."', ";
		if($this->replyTo_id!==null)
			$query .= "'".dataConnection::safe($this->replyTo_id)."');";
		else
			$query .= "null);";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_message ";
		$query .= "SET user_id='".dataConnection::safe($this->user_id)."' ";
		$query .= ", session_id='".dataConnection::safe($this->session_id)."' ";
		$query .= ", subsession_id='".dataConnection::safe($this->subsession_id)."' ";
		$query .= ", isTeacherQu='".(($this->isTeacherQu===false)?0:1)."' ";
		$query .= ", private='".(($this->private===false)?0:1)."' ";
		$query .= ", posted='".dataConnection::time2db($this->posted)."' ";
		$query .= ", message='".dataConnection::safe($this->message)."' ";
		$query .= ", replyTo_id='".dataConnection::safe($this->replyTo_id)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_message WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	//n:m relationship to tag
	function get_tags_count()
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_message_tag_link WHERE message_id = {$this->id};";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

    function get_tags($from=0, $count=-1, $sort=null)
    {
        $query = "SELECT * FROM yacrs_tag WHERE id IN (SELECT tag_id FROM yacrs_message_tag_link WHERE message_id='{$this->id}')";
        if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
            $query .= " ORDER BY ".$sort;
        if(($count != -1)&&(is_int($count))&&(is_int($from)))
            $query .= " LIMIT ".$count." OFFSET ".$from;
        $query .= ';';
        $result = dataConnection::runQuery($query);
        if(sizeof($result)!=0)
        {
            $output = array();
            foreach($result as $r)
                $output[] = new tag($r);
            return $output;
        }
        else
            return false;
    }

    function check_tags($tag_id)
    {
        $query = "SELECT COUNT(*) AS count FROM yacrs_message_tag_link WHERE message_id='{$this->id}' AND tag_id='$tag_id';";
        $result = dataConnection::runQuery($query);
        if($result[0]['count'] == 0)
            return false;
        else
            return true;
    }

    function addto_tags($tag_id)
    {
        if($this->check_tags($tag_id)==false)
        {
            $query = "INSERT INTO yacrs_message_tag_link (message_id, tag_id) VALUES ('{$this->id}', '$tag_id');";
            dataConnection::runQuery($query);
        }
    }

    function removefrom_tags($tag_id)
    {
        if($this->check_tags($tag_id))
        {
            $query = "DELETE FROM yacrs_message_tag_link WHERE  message_id='{$this->id}' AND tag_id='$tag_id';";
            dataConnection::runQuery($query);
        }
    }

    function removeall_tags()
    {
        $query = "DELETE FROM yacrs_message_tag_link WHERE  message_id='{$this->id}';";
        dataConnection::runQuery($query);
    }

    	function toXML()
	{
		$out = "<message>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<user>'.htmlentities($this->user)."</user>\n";
		$out .= '<session>'.htmlentities($this->session)."</session>\n";
		$out .= '<subsession>'.htmlentities($this->subsession)."</subsession>\n";
		$out .= '<isTeacherQu>'.htmlentities($this->isTeacherQu)."</isTeacherQu>\n";
		$out .= '<private>'.htmlentities($this->private)."</private>\n";
		$out .= '<posted>'.htmlentities($this->posted)."</posted>\n";
		$out .= '<message>'.htmlentities($this->message)."</message>\n";
		$out .= '<replyTo>'.htmlentities($this->replyTo)."</replyTo>\n";
		$out .= "</message>\n";
		return $out;
	}
	//[[USERCODE_message]] Put code for custom class members in this block.
    function addTag($tagText)
    {
        $tagText = substr($tagText, 0, 20);
		$query = "SELECT * FROM yacrs_tag WHERE text='".dataConnection::safe($tagText)."' AND session_id='{$this->session_id}';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			$thetag = new tag($result[0]);
		}
		else
        {
			$thetag = new tag();
            $thetag->text = $tagText;
            $thetag->session_id = $this->session_id;
            $thetag->insert();
        }
        $this->addto_tags($thetag->id);
    }

    static function getSessionMessages($sessionID, $limit = false)
    {
		$query = "SELECT * FROM yacrs_message WHERE session_id='$sessionID' AND private='0' ORDER BY posted DESC";
        if($limit)
            $query .= " LIMIT $limit";
        $query .= ';';
		$result = dataConnection::runQuery($query);
        $output = array();
	    if(sizeof($result)!=0)
	    {
	        foreach($result as $r)
	            $output[] = new message($r);
	    }
        return $output;
    }

	//[[USERCODE_message]] WEnd of custom class members.
}

class tag
{
	var $id; //primary key
	var $text;
	var $session_id; //foreign key

	function tag($asArray=null)
	{
		$this->id = null; //primary key
		$this->text = "";
		$this->session_id = null; // foreign key, needs dealt with.
		if($asArray!==null)
			$this->fromArray($asArray);
	}

	function fromArray($asArray)
	{
		$this->id = $asArray['id'];
		$this->text = $asArray['text'];
		$this->session_id = $asArray['session_id']; // foreign key, check code
	}

	static function retrieve_tag($id)
	{
		$query = "SELECT * FROM yacrs_tag WHERE id='".dataConnection::safe($id)."';";
		$result = dataConnection::runQuery($query);
		if(sizeof($result)!=0)
		{
			return new tag($result[0]);
		}
		else
			return false;
	}

	static function retrieve_tag_matching($field, $value, $from=0, $count=-1, $sort=null)
	{
	    if(preg_replace('/\W/','',$field)!== $field)
	        return false; // not a permitted field name;
	    $query = "SELECT * FROM yacrs_tag WHERE $field='".dataConnection::safe($value)."'";
	    if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
	        $query .= " ORDER BY ".$sort;
	    if(($count != -1)&&(is_int($count))&&(is_int($from)))
	        $query .= " LIMIT ".$count." OFFSET ".$from;
	    $query .= ';';
	    $result = dataConnection::runQuery($query);
	    if(sizeof($result)!=0)
	    {
	        $output = array();
	        foreach($result as $r)
	            $output[] = new tag($r);
	        return $output;
	    }
	    else
	        return false;
	}

	function insert()
	{
		//#Any required insert methods for foreign keys need to be called here.
		$query = "INSERT INTO yacrs_tag(text, session_id) VALUES(";
		$query .= "'".dataConnection::safe($this->text)."', ";
		if($this->session_id!==null)
			$query .= "'".dataConnection::safe($this->session_id)."');";
		else
			$query .= "null);";
		dataConnection::runQuery("BEGIN;");
		$result = dataConnection::runQuery($query);
		$result2 = dataConnection::runQuery("SELECT LAST_INSERT_ID() AS id;");
		dataConnection::runQuery("COMMIT;");
		$this->id = $result2[0]['id'];
		return $this->id;
	}

	function update()
	{
		$query = "UPDATE yacrs_tag ";
		$query .= "SET text='".dataConnection::safe($this->text)."' ";
		$query .= ", session_id='".dataConnection::safe($this->session_id)."' ";
		$query .= "WHERE id='".dataConnection::safe($this->id)."';";
		return dataConnection::runQuery($query);
	}

	static function count($where_name=null, $equals_value=null)
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_tag WHERE ";
		if($where_name==null)
			$query .= '1;';
		else
			$query .= "$where_name='".dataConnection::safe($equals_value)."';";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

	//n:m relationship to message
	function get_messages_count()
	{
		$query = "SELECT COUNT(*) AS count FROM yacrs_message_tag_link WHERE tag_id = {$this->id};";
		$result = dataConnection::runQuery($query);
		if($result == false)
			return 0;
		else
			return $result['0']['count'];
	}

    function get_messages($from=0, $count=-1, $sort=null)
    {
        $query = "SELECT * FROM yacrs_message WHERE id IN (SELECT message_id FROM yacrs_message_tag_link WHERE tag_id='{$this->id}')";
        if(($sort !== null)&&(preg_replace('/\W/','',$sort)!== $sort))
            $query .= " ORDER BY ".$sort;
        if(($count != -1)&&(is_int($count))&&(is_int($from)))
            $query .= " LIMIT ".$count." OFFSET ".$from;
        $query .= ';';
        $result = dataConnection::runQuery($query);
        if(sizeof($result)!=0)
        {
            $output = array();
            foreach($result as $r)
                $output[] = new message($r);
            return $output;
        }
        else
            return false;
    }

    function check_messages($message_id)
    {
        $query = "SELECT COUNT(*) AS count FROM yacrs_message_tag_link WHERE tag_id='{$this->id}' AND message_id='$message_id';";
        $result = dataConnection::runQuery($query);
        if($result[0]['count'] == 0)
            return false;
        else
            return true;
    }

    function addto_messages($message_id)
    {
        if($this->check_messages($message_id)==false)
        {
            $query = "INSERT INTO yacrs_message_tag_link (tag_id, message_id) VALUES ('{$this->id}', '$message_id');";
            dataConnection::runQuery($query);
        }
    }

    function removefrom_messages($message_id)
    {
        if($this->check_messages($message_id))
        {
            $query = "DELETE FROM yacrs_message_tag_link WHERE  tag_id='{$this->id}' AND message_id='$message_id';";
            dataConnection::runQuery($query);
        }
    }

    function removeall_messages()
    {
        $query = "DELETE FROM yacrs_message_tag_link WHERE  tag_id='{$this->id}';";
        dataConnection::runQuery($query);
    }

    	function toXML()
	{
		$out = "<tag>\n";
		$out .= '<id>'.htmlentities($this->id)."</id>\n";
		$out .= '<text>'.htmlentities($this->text)."</text>\n";
		$out .= '<session>'.htmlentities($this->session)."</session>\n";
		$out .= "</tag>\n";
		return $out;
	}
	//[[USERCODE_tag]] Put code for custom class members in this block.

	//[[USERCODE_tag]] WEnd of custom class members.
}

