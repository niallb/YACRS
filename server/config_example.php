<?php
// The first setting in the configuration file sets the default timezone for your site.
// The possible parameters for this setting can be found in the PHP manual.
// (http://php.Net/manual/en/function.Date-default-timezone-set.Php) 

date_default_timezone_set('UTC');

// If you are using LDAP for authentication it is recomended that you force SSL
// to avoid passwords beeing transmitted in unencrypted form.

include_once('corelib/force_ssl.php');  // To allow non-SSL use, comment out this line

require_once('corelib/templateMerge.php');
include('lib/login.php');

//The template file (default html/template.Html)  along with any CSS  files referenced by it
//control the appearance of your site. You can edit this file and the CSS files to match your requirements.

$TEMPLATE = 'html/template.html';

//appname is used for the cookie that persits login - set to an appropriate value if you're renaming YACRS

$CFG['appname'] = 'yacrs';  //Used for cookie name, so no spaces etc.

// The site name shown in the Browser title bar.
$CFG['sitetitle'] = 'Yet Another Class Response System (YACRS)';

// You can use an external SMS service that forwards messages as an http POST request to allow
// students to vote using SMS rather than the browser. In practice we found this was almost
// unused, and switched it off at the end of our pilot. Messages should be forwarded to smsin.php

$CFG['smsnumber'] = '';   // The phone number students sent txt messages to.
$CFG['sms_phone_field'] = '';    // The form field that the messaging service uses to post the student's phone number.
$CFG['sms_message_field'] = '';  // The form field that the messaging service uses to post the message content.

// cookiehash is used for various codings - as well as for cookie security.
// It is best to be set to a new random value for each new installation
$CFG['cookiehash'] = "a%DsgsdlfgFGweJ@&90oier";
$CFG['cookietimelimit'] =  10800; // seconds

// LDAP is (for now) the preferred way of authentication to YACRS. At some point OpenID Connect will
// be available as an alternative, however it is not yey fully implemented.
// If you do not know the correct settings to put in here you will need to speak to your institution's
// main server administrion team. They may also need to configure firewall settings.
// If you do not have LDAP available you can leave these fields blank, and set an admin user and password
// that allows LTI to be configured for access to YACRS via a VLE/LMS such as Moodle, Blackboard or Canvas.

// LDAP server IP address or name
$CFG['ldaphost'] = '';
// LDAP context or list of contexts
$CFG['ldapcontext'] = '';
// LDAP Bind details
$CFG['ldapbinduser'] = '';
$CFG['ldapbindpass'] = '';

// YACRS uses ldap_sessionCreator_rules to automatically give some users sessionCreator (teacher) status
// based on the values of fields returened from the LDAP lookup. Each rule is an associative array with
// two values, field indicating the ldap filed to be examined, and another (contains|match|regex) which
// contains the value that indicates a sessionCreator. If any of these rules matchs, the user is given
// sessionCreator status. Three examples from the University of Glasgow are shown below.

// LDAP fields and values that result in sessionCreator (teacher) status
$CFG['ldap_sessionCreator_rules'] = array();

// Look at the dn field, and if the text 'ou=staff' is contained in it set sessionCreator
//$CFG['ldap_sessionCreator_rules'][] = array('field'=>'dn', 'contains'=>'ou=staff');

// Look at the homezipcode field, which UofG uses for student type, and if it is exactly 'PGR'
// (which indicates a Post-graduate Research student), set sessionCreator
//$CFG['ldap_sessionCreator_rules'][] = array('field'=>'homezipcode', 'match'=>'PGR');

// Look at the uid (username) field, and do a regular expression match on it. If it looks like a
// standard UofG staff username set sessionCreator. (UofG uses gifferent username conventions for
// staff, students and guest users.
// $CFG['ldap_sessionCreator_rules'][] = array('field'=>'uid', 'regex'=>'/^[a-z]{2,3}[0-9]+[a-z]$/');

// URL where users are returned after exiting a YACRS session
$CFG['defaultLogoutURL'] = '';

// Probably leave this is as it is, but you might want to add a link to your Institution home page inside an <li>
$CFG['breadCrumb'] = '<ul class="breadcrumb">';

// The folder where screenshots from teh teacher app are saved. Must be writable by the web process.
$CFG['screenshotpath'] = "userimages";

// Database settings
$DBCFG['type']='MySQL';
$DBCFG['host']="localhost"; // Host name
$DBCFG['username']="yacrs"; // Mysql username
$DBCFG['password']="yacrs"; // Mysql password
$DBCFG['db_name']="yacrs"; // Database name

// A roster service can be created to allow YACRS to retrieve the list of students expected in a course.
// The service recieves two parameters, actualCourseKey - a course code and
// secret - sha1($_REQUEST['actualCourseKey'].$rostersecret) and should return a CSV file containing a list
// of students.
// Forename,Surname,Email Address,Telephone Number,userID,Course Code,Course,Academic Level,Person ID
// Only Forename, Surname, Email Address and userID fields are used, and the others can be blank.
$CFG['rosterservice'] = '';     // Service URL
$CFG['rostersecret'] = '';      // Shared secret

//There probably needs to be someone who can set up LTI, make users into sessionCreaters etc.
//Set one username to be this - probably the LDAP username of the person setting this up.
$CFG['adminname'] = 'nsb2x';
//Ideally don't set this field - rely on LDAP. If you're not using LDAP you'll need to set
//a password here. It can be plain text, or (prefereably) the value returned by md5($CFG['cookiehash'].'your_password');
//$CFG['adminpwd'] = '';

?>
