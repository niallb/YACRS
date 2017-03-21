<?php
include "phpqrcode/phpqrcode.php";
header("Content-type: image/png");

QRcode::png($_REQUEST['url'], false, QR_ECLEVEL_L, 9, 4);
