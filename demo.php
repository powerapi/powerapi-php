<?php
require_once('PowerAPI.php');

$ps = new PowerAPI("http://psserver/");

$u = (int) 0000;
$p = (int) 000000;

try {
	$home = $ps->auth($u, $p, true);
} catch (Exception $e) {
	die('Something went wrong! Press the Back button on your browser and try again.<br />PA said: '.$e->getMessage());
}

$grades = $ps->parseGrades($home['homeContents']);

print_r($grades);