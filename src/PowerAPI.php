<?php

require_once 'PowerAPI/BaseObject.php';
require_once 'PowerAPI/Exception.php';
require_once 'PowerAPI/Parser.php';

require_once 'PowerAPI/Assignment.php';
require_once 'PowerAPI/Section.php';
require_once 'PowerAPI/Student.php';

/** Handles the initial token fetch and login */
class PowerAPI
{
    /**
     * Attempt to authenticate against the server
     * @param string $url URL for the PowerSchool server to authenticate against
     * @param string $username student's username
     * @param string $password student's password
     * @param boolean $fetch_transcript fetch transcript after successful login?
     * @return PowerAPI\User
     */
    static public function authenticate($url, $username, $password, $fetch_transcript = true)
    {
        // Ensure the URL ends with a /
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

        $login = $client->__call(
            'login',
            Array(
                'username' => $username,
                'password' => $password,
                'userType' => 2
            )
        );

        // userSessionVO is unset if something went wrong during auth.
        if ($login->userSessionVO === null) {
            throw(new PowerAPI\Exception($login->messageVOs->description));
        }

        $session = $login->userSessionVO;

        return new PowerAPI\Student($url, $session, $fetch_transcript);
    }
}
