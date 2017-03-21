<?php

/**
 * Wordle-like words clouds generator.
 * This work is inspired by http://www.wordle.net/
 * 
 * @author Daniel Barsotti / dan [at] dreamcraft [dot] ch
 */

require dirname(__FILE__).'/box.php';
require dirname(__FILE__).'/mask.php';
require dirname(__FILE__).'/frequency_table.php';
require dirname(__FILE__).'/palette.php';
require dirname(__FILE__).'/word_cloud.php';

if (!isset($argv) || count($argv) != 4) {
	echo "Usage: php -f tagcloud.php <textfile> <imgwidth> <imgheight>";
	exit;
}

$textfile = $argv[1];
$width = $argv[2];
$height = $argv[3];

$text = file_get_contents($textfile);

$font = dirname(__FILE__).'/Arial.ttf';
$cloud = new WordCloud($width, $height, $font, $text);
$palette = Palette::get_palette_from_hex($cloud->get_image(), array('FFA700', 'FFDF00', 'FF4F00', 'FFEE73'));
$cloud->render($palette);

$img = getcwd() . '/ofcloud' . rand(100, 999) . '.png';
imagepng($cloud->get_image(), $img);
echo $img;
?>