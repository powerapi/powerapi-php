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
 * @version		2.1
 * @license		http://opensource.org/licenses/MIT	The MIT License
 */

namespace henriwatson\PowerAPI;

/** Handles post-authentication functions. (fetching transcripts, parsing data, etc.) */
class User {
	private $url, $version, $cookiePath, $ua, $homeContents;
	
	
	public function __construct($url, $version, $ua, $cookiePath, $homeContents) {
		$this->url = $url;
		$this->version = $version;
		$this->ua = $ua;
		$this->cookiePath = $cookiePath;
		$this->homeContents = $homeContents;
	}
	
	/**
	 * Pull the authenticated user's PESC HighSchoolTranscript from the server
	 * @return string PESC HighSchoolTranscript
	*/
	public function fetchTranscript() {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL,$this->url."guardian/studentdata.xml?ac=download");
		curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiePath);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiePath);
		curl_setopt($ch, CURLOPT_REFERER, $this->url."/public/");
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		return $result;
	}
	/* Scraping */
	private function stripA($strip) {
		if (substr($strip, 0, 2) == "<a") {
			preg_match('/<a (.*?)>(.*?)<\/a>/s', $strip, $stripped);
			return $stripped[2];
		} else {
			return $strip;
		}
	}
	
	/**
	 * Parse the authenticated user's name from the retrieved home page
	 * @return array several representations of the user's name
	*/
	public function getName() {
		if ($this->version == 7) {
			throw new Exception('Scraping is not supported in PS7.');
		}
		
		preg_match('/<div id="userName">(.*?) <span>/s', $this->homeContents, $userName);
		
		$bits = explode(", ", $userName[1]);
		
		return Array(
			'direct' => $userName[1],
			'split' => $bits,
			'firstname' => $bits[1],
			'lastname' => $bits[0],
			'regular' => $bits[1]." ".$bits[0]
		);
	}
	
	/**
	 * Parse the authenticated user's grades from the retrieved home page
	 * @return array
	*/
	public function parseGrades() {
		if ($this->version == 7) {
			throw new Exception('Scraping is not supported in PS7.');
		}
		$result = $this->homeContents;
		/* Parse different terms */
		preg_match_all('/<tr align="center" bgcolor="#f6f6f6">(.*?)<\/tr>/s', $result, $slices);
		preg_match_all('/<td rowspan="2" class="bold">(.*?)<\/td>/s', $slices[0][0], $slices);
		$slices = $slices[1];
		$slicesCount = count($slices);
		unset($slices[0]);
		unset($slices[1]);
		unset($slices[$slicesCount-2]);
		unset($slices[$slicesCount-1]);
		$slices = array_merge(array(), $slices);
		
		/* Parse classes */
		preg_match('/<table border="1" cellpadding="2" cellspacing="0" align="center" bordercolor="#dcdcdc" width="99%">(.*?)<\/table>/s', $result, $classesdmp);
		$classesdmp = $classesdmp[0];
		
		preg_match_all('/<tr align="center" bgcolor="(.*?)">(.*?)<\/tr>/s', $classesdmp, $classes, PREG_SET_ORDER);
		unset($classes[count($classes)-1]);
		unset($classes[0]);
		unset($classes[1]);
		unset($classes[2]);
		
		foreach ($classes as $class) {
			preg_match('/<td align="left">(.*?)<br>(.*?)<a href="mailto:(.*?)">(.*?)<\/a><\/td>/s', $class[2], $classData);
			$name = $classData[1];
			
			preg_match_all('/<td>(.*?)<\/td>/s', $class[2], $databits, PREG_SET_ORDER);
			
			$data = Array(
				'name' => $name,
				'teacher' => Array(
					'name' => $classData[4],
					'email' => $classData[3]
					),
				'period' => $databits[0][1],
				'absences' => $this->stripA($databits[count($databits)-2][1]),
				'tardies' => $this->stripA($databits[count($databits)-1][1])
			);
			
			$databitsCount = count($databits);
			unset($databits[0]);
			unset($databits[$databitsCount-2]);
			unset($databits[$databitsCount-1]);
			$databits = array_merge(Array(), $databits);
			
			preg_match_all('/<a href="scores.html\?(.*?)">(.*?)<\/a>/s', $class[2], $scores, PREG_SET_ORDER);
			
			$i = 0;
			
			foreach ($scores as $score) {
				preg_match('/frn\=(.*?)\&fg\=(.*)/s', $score[1], $URLbits);
				$scoreT = explode("<br>", $score[2]);
				if ($scoreT[0] !== "--" && !is_numeric($scoreT[0]))	// This is here to handle special cases with schools using letter grades
					$data['scores'][$URLbits[2]] = $scoreT[1];		//  or grades not being posted
				else
					$data['scores'][$URLbits[2]] = $scoreT[0];
				
				$i++;
			}
			
			$classesA[] = $data;
		}
		
		return $classesA;
	}
}