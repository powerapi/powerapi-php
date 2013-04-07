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
 * @author		Henri Watson
 * @package		User
 * @version		2.2
 * @license		http://opensource.org/licenses/MIT	The MIT License
 */

namespace henriwatson\PowerAPI;

/** Handles post-authentication functions. (fetching transcripts, parsing data, etc.) */
class User {
	private $url, $version, $cookiePath, $ua, $homeContents, $courses;
	
	
	public function __construct($url, $version, $ua, $cookiePath, $homeContents) {
		$this->url = $url;
		$this->version = $version;
		$this->ua = $ua;
		$this->cookiePath = $cookiePath;
		$this->homeContents = $homeContents;

		$this->courses = $this->_createCourses();
	}
	
	/**
	 * Pull the authenticated user's PESC HighSchoolTranscript from the server
	 * @return string PESC HighSchoolTranscript
	*/
	public function fetchTranscript() {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL,$this->url.'guardian/studentdata.xml?ac=download');
		curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiePath);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiePath);
		curl_setopt($ch, CURLOPT_REFERER, $this->url.'/public/');
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		return $result;
	}

	/* Scraping */
	/**
	 * Parse the authenticated user's grades from the retrieved home page
	 * @return array
	*/
	private function _createCourses() {
		$result = $this->homeContents;

		/* Parse different terms */
		preg_match_all('/<tr class="center th2">(.*?)<\/tr>/s', $result, $terms);
		preg_match_all('/<th rowspan="2">(.*?)<\/th>/s', $terms[0][0], $terms);
		
		$terms = $terms[1];
		$termsCount = count($terms);
		unset($terms[0]); // Remove Exp
		unset($terms[1]); // Remove Course
		unset($terms[$termsCount-2]); // Remove Abscences
		unset($terms[$termsCount-1]); // Remove Tardies
		$terms = array_merge(array(), $terms); // Reorder

		/* Parse classes */
		preg_match_all('/<tr class="center" bgcolor="(.*?)">(.*?)<\/tr>/s', $result, $classes, PREG_SET_ORDER);

		foreach ($classes as $class) {
			$classesA[] = new Course($this->url, $this->version, $class[2], $terms);
		}
		
		return $classesA;
	}

	/**
	 * Parse the school's name from the retrieved home page
	 * @return string school's name
	*/
	public function getSchoolName() {
		preg_match('/<div id="print-school">(.*?)<br>/s', $this->homeContents, $schoolName);
		
		return trim($schoolName[1]);
	}
	
	/**
	 * Parse the authenticated user's name from the retrieved home page
	 * @return string user's name
	*/
	public function getUserName() {
		preg_match('/<li id="userName" .*?<span>(.*?)<\/span>/s', $this->homeContents, $userName);
		
		return trim($userName[1]);
	}

	/**
	 * Return an array of courses
	 * @return array courses
	*/
	public function getCourses() {
		return $this->courses;
	}
}
