<?php
require_once 'src/henriwatson/PowerAPI/Core.php';
require_once 'src/henriwatson/PowerAPI/User.php';

use henriwatson\PowerAPI as PowerAPI;

$ps = new PowerAPI\Core("http://psserver/", 7);

try {
	$user = $ps->auth("username", "password");
} catch (Exception $e) {
	die('Something went wrong! Press the Back button on your browser and try again.<br />PA said: '.$e->getMessage());
}

echo $user->fetchTranscript();
