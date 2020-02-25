<?php
define('ROOT_PATH', dirname(__FILE__).'/');
//include_once(ROOT_PATH.'corelib/force_ssl.php');  // To allow non-SSL use, comment out this line
require_once(ROOT_PATH.'corelib/safeRequestFunctions.php');
require_once(ROOT_PATH.'corelib/templateMerge.php');
require_once(ROOT_PATH.'lib/database.php');

if(!file_exists(ROOT_PATH.'../../yacrs_config.php'))
    header("Location: configuration.html");
include_once(ROOT_PATH.'../../yacrs_config.php');

settings::merge_settings_array($CFG);

if((isset($CFG['access_log']))&&($CFG['access_log']==1))
{
    $uinfo = checkLoggedInUser(false);
    if($uinfo==false)
        $uinfo = array('uname'=>'NotLoggedIn');
    file_put_contents(ROOT_PATH.'log/access_log.txt', time().':'.$uinfo['uname'].": {$_SERVER['REMOTE_ADDR']} : {$_SERVER['REQUEST_URI']}".PHP_EOL, FILE_APPEND | LOCK_EX);
    unset($uinfo);
}

