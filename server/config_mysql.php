<?php

date_default_timezone_set('UTC');
include_once('corelib/force_ssl.php');  // To allow non-SSL use, comment out this line
require_once('corelib/templateMerge.php');
include('lib/login.php');
//include_once('lib/libfuncs.php');

$TEMPLATE = 'html/template.html';
$MOBILETEMPLATE = 'html/mtemplate.html';
$CFG['appname'] = 'bClk';  //Used for cookie name, so no spaces etc.

// cookiehash is used for various codings - It is best to be set to a new random value for each
// new installation to avoid clashes of BBB meeting codes.
$CFG['cookiehash'] = "fgthgkj";
$CFG['cookietimelimit'] = 3600; // seconds

// LDAP server IP
$CFG['ldaphost'] = '130.209.13.173';
// LDAP context or list of contexts
$CFG['ldapcontext'] = 'o=Gla';

// URL where users are returned after exiting a BBB session
$CFG['defaultLogoutURL'] = 'http://www.gla.ac.uk/';

// Database settings
//$DBCFG['type']='SQLite';
//$DBCFG['file']='bclk.s3db';
$DBCFG['type']='MySQL';
$DBCFG['host']="localhost"; // Host name
$DBCFG['username']="basicclick"; // Mysql username
$DBCFG['password']="basicclick"; // Mysql password
$DBCFG['db_name']="basicclick"; // Database name

// Admin users not yet implemented...
//$CFG['adminusers'] = array('admin'=>'bcef7a046258082993759bade995b3ae8bee26c7');

$CFG['rosterservice'] = 'services/coursestudentsCSV.php';
$CFG['rostersecret'] = 'mysecret42';

?>
