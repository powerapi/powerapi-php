<?php

/**************************************************
*  _    _          _   _  _____      ____  _   _  *
* | |  | |   /\   | \ | |/ ____|    / __ \| \ | | *
* | |__| |  /  \  |  \| | |  __    | |  | |  \| | *
* |  __  | / /\ \ | . ` | | |_ |   | |  | | . ` | *
* | |  | |/ ____ \| |\  | |__| |   | |__| | |\  | *
* |_|  |_/_/    \_\_| \_|\_____|    \____/|_| \_| *
*                                                 *
***************************************************
* Directly including this file in your project is *
* not supported by PowerAPI.                      *
***************************************************
* Consider using https://getcomposer.org/ to      *
* download your project's dependencies instead.   *
**************************************************/

if (!class_exists("Zend\Soap\Client")) {
    throw(new Exception("Zend\Soap\Client does not exist!"));
}

require_once 'PowerAPI/Data/BaseObject.php';
require_once 'PowerAPI/Data/Assignment.php';
require_once 'PowerAPI/Data/Section.php';
require_once 'PowerAPI/Data/Student.php';

require_once 'PowerAPI/Exceptions/Exception.php';
require_once 'PowerAPI/Exceptions/Authentication.php';

require_once 'PowerAPI/Parser.php';

require_once 'PowerAPI/PowerAPI.php';
