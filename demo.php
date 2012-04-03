<?php
require_once('PowerAPI.php');

$ps = new PowerAPI("http://psserver/", 6);

$u = "";
$p = "";

try {
	$user = $ps->auth($u, $p);
} catch (Exception $e) {
	die('Something went wrong! Press the Back button on your browser and try again.<br />PA said: '.$e->getMessage());
}

echo $user->fetchTranscript();