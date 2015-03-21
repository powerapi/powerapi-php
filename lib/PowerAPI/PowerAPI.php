<?php

namespace PowerAPI;

/** Handles logging in with PowerSchool and initialising PowerAPI\Data\Student. */
class PowerAPI
{
    /**
     * Attempt to authenticate against the server
     * @param string $url URL for the PowerSchool server to authenticate against
     * @param string $username student's username
     * @param string $password student's password
     * @param boolean $fetch_transcript fetch transcript after successful login?
     * @return PowerAPI\Student
     */
    static public function authenticate($url, $username, $password, $fetch_transcript = true)
    {
        // Ensure the URL ends with a /
        if (substr($url, -1) !== "/") {
            $url = $url."/";
        } else {
            $url = $url;
        }

        $client = new \Zend\Soap\Client();
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
            throw(new Exceptions\Authentication($login->messageVOs->description));
        }

        $session = $login->userSessionVO;

        return new Data\Student($url, $session, $fetch_transcript);
    }

    /**
     * Fetch a URL for a PowerSchool install using a district code
     * @param string $code district code
     * @return string
    */
    static public function districtLookup($code)
    {
        $curlResource = curl_init('https://powersource.pearsonschoolsystems.com/services/rest/remote-device/v2/get-district/'.$code);
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlResource, CURLOPT_HTTPHEADER, array(
            'Accept: application/json'
        ));

        $details = curl_exec($curlResource);

        // Return false if we couldn't connect or if the district doesn't exist.
        if ($details === FALSE || $details === '') {
            return false;
        }

        $details = json_decode($details);

        if ($details->district->server->sslEnabled !== 1) {
            $url = 'https://'.$details->district->server->serverAddress;
        } else {
            $url = 'http://'.$details->district->server->serverAddress;
        }

        if (
            ($details->district->server->sslEnabled == 1 && $details->district->server->portNumber == 443) ||
            ($details->district->server->sslEnabled == 0 && $details->district->server->portNumber == 80)
            ) {
            $url .= '/';
        } else {
            $url .= ':'.$details->district->server->portNumber.'/';
        }

        return $url;
    }
}
