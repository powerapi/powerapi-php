<?php

/**
 * Copyright (c) 2014 Henri Watson
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 * @author         Henri Watson
 * @package        PowerAPI
 * @version        3.0
 * @license        http://opensource.org/licenses/MIT    The MIT License
 */


require_once 'PowerAPI/Exception.php';

require_once 'PowerAPI/Course.php';
require_once 'PowerAPI/Student.php';

/** Handles the initial token fetch and login */
class PowerAPI
{
    private $ua = 'PowerAPI-php/3.0 (https://github.com/powerapi/powerapi-php)';

    /**
     * Attempt to authenticate against the server
     * @param string username
     * @param string password
     * @return User
    */
    static public function authenticate($url, $username, $password, $fetch_transcript = true)
    {
        if (substr($url, -1) !== "/") {
            $url = $url."/";
        } else {
            $url = $url;
        }

        $client = new Zend\Soap\Client();
        $client->setOptions(Array(
            'uri' => 'http://publicportal.rest.powerschool.pearson.com/xsd',
            'location' => $url.'pearson-rest/services/PublicPortalServiceJSON',
            'login' => 'pearson',
            'password' => 'm0bApP5',
            'use' => SOAP_LITERAL
        ));

        $login = $client->__call('login', Array('username' => $username, 'password' => $password, 'userType' => 2));

        if ($login->userSessionVO === null) {
            throw(new PowerAPI\Exception($login->messageVOs->description));
        }

        $session = $login->userSessionVO;

        return new PowerAPI\Student($url, $session, $fetch_transcript);
    }
}
