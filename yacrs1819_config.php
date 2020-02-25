<?php

date_default_timezone_set('UTC');
if((!isset($noSSLok))||($noSSLok==false)) include_once('corelib/force_ssl.php');  // To allow non-SSL use, comment out this line
require_once('corelib/templateMerge.php');
include('lib/login.php');
//include_once('lib/libfuncs.php');

$TEMPLATE = 'html/template.html';
$MOBILETEMPLATE = 'html/mtemplate.html';
$CFG['appname'] = 'yacrs';  //Used for cookie name, so no spaces etc.
$CFG['sitetitle'] = 'University of Glasgow - Class Response';

$CFG['smsnumber'] = '0141 280 0171';
$CFG['sms_phone_field'] = 'originator';
$CFG['sms_message_field'] = 'message';

// cookiehash is used for various codings - as well as for cookie security.
// It is best to be set to a new random value for each new installation
$CFG['cookiehash'] = "fgthgkj";
$CFG['cookietimelimit'] =  10800; // seconds

// LDAP server IP
$CFG['ldaphost'] = 'taranis.campus.gla.ac.uk';
// LDAP context or list of contexts
$CFG['ldapcontext'] = 'o=Gla';
// LDAP Bind details
$CFG['ldapbinduser'] = 'CN=LDAPYACRS,ou=service,o=gla';
$CFG['ldapbindpass'] = '98fg33jh29am11kj';
// LDAP fields and values that result in sessionCreator (teacher) status
$CFG['ldap_sessionCreator_rules'] = array();
$CFG['ldap_sessionCreator_rules'][] = array('field'=>'dn', 'contains'=>'ou=staff');
$CFG['ldap_sessionCreator_rules'][] = array('field'=>'homezipcode', 'match'=>'PGR');
$CFG['ldap_sessionCreator_rules'][] = array('field'=>'uid', 'regex'=>'/^[a-z]{2,3}[0-9]+[a-z]$/');
//$CFG['ldap_sessionCreator_rules'][] = array('field'=>'mail', 'regex'=>'/[a-zA-Z]+\.[a-zA-Z]+.*?@glasgow\.ac\.uk/');

// URL where users are returned after exiting a YACRS session
$CFG['defaultLogoutURL'] = 'http://www.gla.ac.uk/';
$CFG['breadCrumb'] = "<a href='http://www.gla.ac.uk/'>University of Glasgow</a> | <a href='http://www.gla.ac.uk/services/learningteaching/'>Learning & Teaching Centre</a> ";

$CFG['screenshotpath'] = "userimages";

// Database settings
//$DBCFG['type']='SQLite';
//$DBCFG['file']='yacrs.s3db';
$DBCFG['type']='MySQL';
$DBCFG['host']="localhost"; // Host name
$DBCFG['username']="learnadmin"; // Mysql username
$DBCFG['password']="lrn@29"; // Mysql password
$DBCFG['db_name']="yacrs1718"; // Database name

$CFG['rosterservice'] = 'services/coursestudentsCSV.php';
$CFG['rostersecret'] = 'mysecret42';

//There probably needs to be someone who can set up LTI, make users into sessionCreaters etc.
//Set one username to be this - probably the LDAP username of the person setting this up.
$CFG['adminname'] = 'nsb2x';
//Ideally don't set this field - rely on LDAP. If you're not using LDAP you'll need to set
//a password here. It can be plain text, or (prefereably) the value returned by md5($CFG['cookiehash'].'your_password');
//$CFG['adminpwd'] = '';

?>
