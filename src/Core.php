<?php

/**
 * Copyright (c) 2013 Henri Watson
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
 * @package        Core
 * @version        2.3
 * @license        http://opensource.org/licenses/MIT    The MIT License
 */

namespace PowerAPI;

/** Handles the initial token fetch and login */
class Core
{
    private $url, $version, $tmp_fname;
    private $ua = "PowerAPI-php/2.3 (https://github.com/henriwatson/PowerAPI-php)";

    /**
     * Create a PowerAPI object
     * @param string PowerSchool server URL
     * @param int server major version number, not required.
    */
    public function __construct($url, $version = 7)
    {
        if (substr($url, -1) !== "/")
            $this->url = $url."/";
        else
            $this->url = $url;
        $this->version = $version;

        if ($version == 6) {
            throw new \Exception('PowerSchool 6 is no longer supported. Please ask your school to upgrade or revert to an older version.');
        }

        $this->tmp_fname = tempnam("/tmp/","PSCOOKIE");
    }

    /**
     * Set user-agent to a custom value
     * @param string user agent
    */
    public function setUserAgent($ua)
    {
        $this->ua = $ua;
    }

    public function _request($path, $post = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$this->url.$path);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->tmp_fname);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->tmp_fname);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $html = curl_exec($ch);

        curl_close($ch);

        return $html;
    }

    /* Authentication */

    /**
     * Fetch authentication parameters from the server
     * @return array authentication parameters
    */
    private function _getAuthData()
    {
        $html = $this->_request('');

        if (!$html) {
            throw new \Exception('Unable to retrieve authentication tokens from PS server.');
            break;
        }

        preg_match('/<input type="hidden" name="pstoken" value="(.*?)" \/>/s', $html, $pstoken);
        $data['pstoken'] = $pstoken[1];

        preg_match('/<input type="hidden" name="contextData" value="(.*?)" \/>/s', $html, $contextData);
        $data['contextData'] = $contextData[1];

        if (!strpos($html, "<input type=hidden name=ldappassword value=''>")) {
            $data['ldap'] = false;
        } else {
            $data['ldap'] = true;
        }

        return $data;
    }

    /**
     * Attempt to authenticate against the server
     * @param string username
     * @param string password
     * @return User
    */
    public function auth($uid, $pw)
    {
        $authdata = $this->_getAuthData();

        $fields = array(
                    'pstoken' => $authdata['pstoken'],
                    'contextData' => $authdata['contextData'],
                    'dbpw' => hash_hmac("md5", strtolower($pw), $authdata['contextData']),
                    'serviceName' => "PS Parent Portal",
                    'pcasServerUrl' => "/",
                    'credentialType' => "User Id and Password Credential",
                    'account' => $uid,
                    'pw' => hash_hmac("md5", str_replace("=", "", base64_encode(md5($pw, true))), $authdata['contextData'])
                );

        if ($authdata['ldap'])
            $fields['ldappassword'] = $pw;

        $result = $this->_request('guardian/home.html', $fields);

        if (!strpos($result, 'Grades and Attendance')) {            // This should show up instantly after login
            preg_match('/<div class="feedback-alert">(.*?)<\/div>/s', $result, $pserror); // Pearson tell us what's wrong! We should listen to that.
            if (!isset($pserror[1])) { // Well, okay, sometimes they don't
                throw new \Exception('Unable to login to PS server.');
            } else {
                throw new \Exception($pserror[1]);    // But if they do, we should pass that along
            }
            break;
        }

        return new User($this, $result);
    }
}
