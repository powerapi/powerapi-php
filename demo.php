<?php
require_once 'src/henriwatson/PowerAPI/Core.php';
require_once 'src/henriwatson/PowerAPI/User.php';
require_once 'src/henriwatson/PowerAPI/Course.php';

use henriwatson\PowerAPI as PowerAPI;

$ps = new PowerAPI\Core("https://psserver/");

try {
	$user = $ps->auth("username", "password");
} catch (Exception $e) {
	die('Something went wrong! Press the Back button on your browser and try again.<br />PA said: '.$e->getMessage());
}

$courses = $user->getCourses();
$assignments = $courses[0]->getAssignments("Q1");

echo $courses[0]->getName();
print_r($assignments);
