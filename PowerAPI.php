<?php
class PowerAPI {
	private $url;
	private $ua = "PowerAPI-php/1.0 (https://github.com/henriwatson/PowerAPI-php)";
	
	public function __construct($url) {
		$this->url = $url;
	}
	
	public function setUserAgent($ua) {
		$this->ua = $ua;
	}
	
	/* Authentication */
	
	private function getAuthTokens() {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL,$this->url);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$html = curl_exec($ch);
		
		curl_close($ch);
		
		if (!$html) {
			throw new Exception('Unable to retrieve authentication tokens from PS server.');
			break;
		}
		
		preg_match('/<input type="hidden" name="pstoken" value="(.*?)" \/>/s', $html, $pstoken);
		$data['pstoken'] = $pstoken[1];
		
		preg_match('/<input type="hidden" name="contextData" value="(.*?)" \/>/s', $html, $contextData);
		$data['contextData'] = $contextData[1];
		
		return $data;
	}
	
	public function auth($uid, $pw) {
		$tokens = $this->getAuthTokens();
		
		$hmacPW = hash_hmac("md5", strtolower($pw), $tokens['contextData']); // Hash the user's password with the auth token
		
		$fields = array(
					'pstoken' => urlencode($tokens['pstoken']),
					'contextData' => urlencode($tokens['contextData']),
					'returnUrl' => urlencode($this->url."guardian/home.html"),
					'serviceName' => urlencode("PS Parent Portal"),
					'serviceTicket' => "",
					'pcasServerUrl' => urlencode("/"),
					'credentialType' => urlencode("User Id and Password Credential"),
					'account' => urlencode($uid),
					'pw' => urlencode($hmacPW)
				);
		
		$fields_string = "";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string,'&');
		
		$ch = curl_init();
		
		$tmp_fname = tempnam("/tmp/","PSCOOKIE");
		
		curl_setopt($ch, CURLOPT_URL,$this->url."guardian/home.html");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $tmp_fname);
		curl_setopt($ch, CURLOPT_REFERER, $this->url."/public/");
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$fields_string);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		if (!strpos($result, "Grades and Attendance")) {			// This should show up instantly after login
			throw new Exception('Unable to login to PS server.');	// So if it doesn't, something went wrong. (normally bad username/password)
			break;
		}
		
		return Array(
				"homeContents" => $result,
				"cookiePath" => $tmp_fname
			);
	}
	
	/* Scraping */
	
	public function parseGrades($result) {
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
			preg_match('/<td align="left">(.*?)<br>/s', $class[2], $name);
			$name = $name[1];
			
			preg_match_all('/<td><a href="scores.html(.*?)">(.*?)<\/a>/s', $class[2], $scores, PREG_SET_ORDER);
			
			$data = Array('name' => $name);
			
			$i = 0;
			
			foreach ($scores as $score) {
				$scoreT = explode("<br>", $score[2]);
				
				if ($scoreT[0] !== "--" && !is_numeric($scoreT[0]))	// This is here to handle special cases with schools using letter grades
					$data['scores'][$slices[$i]] = $scoreT[1];		//  or grades not being posted
				else
					$data['scores'][$slices[$i]] = $scoreT[0];
				
				$i++;
			}
			
			$classesA[] = $data;
		}
		
		return $classesA;
	}
}