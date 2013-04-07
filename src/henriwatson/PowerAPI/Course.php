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
 * @package		Course
 * @version		2.2
 * @license		http://opensource.org/licenses/MIT	The MIT License
 */

namespace henriwatson\PowerAPI;

/** Handles post-authentication functions. (fetching transcripts, parsing data, etc.) */
class Course {
	private $url, $version, $html; // Passed in variables
	private $name, $teacher, $scores, $period, $attendance; // Scraped variables

	public function __construct($url, $version, $html) {
		$this->url = $url;
		$this->version = $version;
		$this->html = $html;

		$this->_populateCourse();
	}

	/**
	 * Parse an <A> tag
	 * @return array tag's title and destination URL
	*/
	private function _splitA($strip) {
		if (substr($strip, 0, 2) == '<a') {
			preg_match('/<a href="(.*?)">(.*?)<\/a>/s', $strip, $stripped);
			return Array(
				'title' => $stripped[2],
				'url' => $stripped[1]
			);
		} else {
			return Array('title' => $strip);
		}
	}

	/**
	 * Populate the object with the course's information
	 * @return void
	*/
	private function _populateCourse() {
		preg_match('/<td align="left">(.*?)[&nbsp;|&bbsp;]<br>(.*?)<a href="mailto:(.*?)">(.*?)<\/a><\/td>/s', $this->html, $classData);
		$this->name = $classData[1];
		$this->teacher = Array(
			'name' => $classData[4],
			'email' => $classData[3]
		);

		preg_match_all('/<td>(.*?)<\/td>/s', $this->html, $databits, PREG_SET_ORDER);
		$this->period = $databits[0][1];

		$absences = $this->_splitA($databits[count($databits)-2][1]);
		if (!isset($absences['url'])) {
			$this->attendance['absences']['count'] = $absences['title'];
		} else {
			$this->attendance['absences'] = Array(
				'count' => $absences['title'],
				'url' => $absences['url']
			);
		}

		$tardies = $this->_splitA($databits[count($databits)-1][1]);
		if (!isset($tardies['url'])) {
			$this->attendance['tardies']['count'] = $tardies['title'];
		} else {
			$this->attendance['tardies'] = Array(
				'count' => $tardies['title'],
				'url' => $tardies['url']
			);
		}

		preg_match_all('/<a href="scores.html\?(.*?)">(.*?)<\/a>/s', $this->html, $scores, PREG_SET_ORDER);
		
		foreach ($scores as $score) {
			preg_match('/frn\=(.*?)\&fg\=(.*)/s', $score[1], $URLbits);
			$scoreT = explode('<br>', $score[2]);
			if ($score[2] !== '--' && !is_numeric($scoreT[0])) {	// This is here to handle special cases with schools using letter grades
				$this->scores[$URLbits[2]]['score'] = $scoreT[1];		//  or grades not being posted
				$this->scores[$URLbits[2]]['url'] = 'scores.html?'.$score[1];
			} else if ($score[2] !== '--') {
				$this->scores[$URLbits[2]]['score'] = $scoreT[0];
				$this->scores[$URLbits[2]]['url'] = 'scores.html?'.$score[1];
			}
		}
	}

	/**
	 * Return the course's name
	 * @return string course name
	*/
	public function fetchName() {
		return $this->name;
	}

	/**
	 * Return the course's scores in an array
	 * @return array course's scores
	*/
	public function fetchScores() {
		foreach ($this->scores as $term => $data) {
			$return[$term] = $data['score'];
		}
		return $return;
	}
}