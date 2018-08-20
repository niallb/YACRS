<?php
define('ROOT_PATH', dirname(__FILE__).'/');
//include_once(ROOT_PATH.'corelib/force_ssl.php');  // To allow non-SSL use, comment out this line
require_once(ROOT_PATH.'corelib/safeRequestFunctions.php');
require_once(ROOT_PATH.'corelib/templateMerge.php');
require_once(ROOT_PATH.'lib/database.php');

if(!file_exists(ROOT_PATH.'../yacrs_config.php'))
    header("Location: configuration.html");
include_once(ROOT_PATH.'../yacrs_config.php');

settings::merge_settings_array($CFG);

