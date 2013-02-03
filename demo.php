<?php
require_once 'vendor/autoload.php';

use henriwatson\PowerAPI as PowerAPI;

$ps = new PowerAPI\Core("http://psserver/", 6);

try {
	$user = $ps->auth("username", "password");
} catch (Exception $e) {
	die('Something went wrong! Press the Back button on your browser and try again.<br />PA said: '.$e->getMessage());
}

echo $user->fetchTranscript();