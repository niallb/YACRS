<?php

date_default_timezone_set('UTC');
include_once('corelib/force_ssl.php');  // To allow non-SSL use, comment out this line
require_once('corelib/safeRequestFunctions.php');
require_once('corelib/templateMerge.php');
include('lib/login.php');
//include_once('lib/libfuncs.php');

//Get most configuration info from a file outside the web server directory - paranoid security
include_once('../../config.php');
// alternatively, comment that out, uncomment the following and fill in the details below
/*
$TEMPLATE = 'html/template.html';
$MOBILETEMPLATE = 'html/mtemplate.html';
$CFG['appname'] = 'yacrs';  //Used for cookie name, so no spaces etc.
$CFG['sitetitle'] = 'YACRS';

$CFG['smsnumber'] = '';
$CFG['sms_phone_field'] = 'originator';
$CFG['sms_message_field'] = 'message';


// cookiehash is used for various codings - as well as for cookie security.
// It is best to be set to a new random value for each new installation
$CFG['cookiehash'] = "ARandomStringOfCharacters_oaiurqwe";
$CFG['cookietimelimit'] =  10800; // seconds

// LDAP server IP
$CFG['ldaphost'] = '';
// LDAP context or list of contexts
$CFG['ldapcontext'] = '';
// LDAP fields and values that result in sessionCreator (teacher) status
$CFG['ldap_sessionCreator_rules'] = array();

// Alternative login methods

// URL where users are returned after exiting a YACRS session
$CFG['defaultLogoutURL'] = 'https://www.google.com/';
$CFG['breadCrumb'] = "<a href='https://www.google.com/?gws_rd=ssl#q=yacrs'>Search for YACRS</a> ";

$CFG['screenshotpath'] = "userimages";

// Database settings
$DBCFG['type']='MySQL';
$DBCFG['host']="localhost"; // Host name
$DBCFG['username']="yacrs"; // Mysql username
$DBCFG['password']="yacrs"; // Mysql password
$DBCFG['db_name']="yacrs"; // Database name

//$CFG['rosterservice'] = '';  // URL or roster service
//$CFG['rostersecret'] = '';  // secret used to authenticate roster access

//There probably needs to be someone who can set up LTI, make users into sessionCreaters etc.
//Set one username to be this - probably the LDAP username of the person setting this up.
$CFG['adminname'] = 'admin';
//Ideally don't set this field - rely on LDAP. If you're not using LDAP you'll need to set
//a password here. It can be plain text, or (prefereably) the value returned by md5($CFG['cookiehash'].'your_password');
//$CFG['adminpwd'] = '';
//*/
?>
