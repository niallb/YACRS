<?php
date_default_timezone_set('UTC');
//include_once('corelib/force_ssl.php');  // To allow non-SSL use, comment out this line
require_once('corelib/safeRequestFunctions.php');
require_once('corelib/templateMerge.php');

if(!file_exists('../yacrs_config.php'))
    header("Location: configuration.html");
include_once('../yacrs_config.php');

