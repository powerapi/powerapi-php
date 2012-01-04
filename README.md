PowerAPI-php
============
Library for fetching information from PowerSchool SISes.

Requirements
------------
* PHP 5 >= 5.1.2.
* PowerSchool >= 6.0.0; PowerSchool <= 6.2.1.6

PowerSchool 7.x is **not** yet supported by PowerAPI-php.

Usage
-----
A basic demo is provided in `demo.php`

### Initializing the library ###
	require_once('PowerAPI.php');			// Include the library
	
	$ps = new PowerAPI("http://psserver/");	// Specify the server's URL

**Note:** It's important that you end the server URL with a slash (/)

### Authenticating as a user ###
	$ps->auth(USERNAME, PASSWORD);

Provide the user's username and password. Returns an array containing the path to a file containing the user's cookies and the contents of the home page.

### Parsing classes and grades ###
	$ps->parseGrades($home['homeContents']);

Provide the contents of the home page. Returns an array containing the class name and all of the grades. An example of the output is provided below (passed through print_r)

	Array
	(
		[0] => Array
			(
				[name] => Sample Class
				[scores] => Array
					(
						[Q1] => 95
						[Q2] => 97
						[E1] => 89
						[S1] => 95
					)
			)
	)


License
-------
Copyright (c) 2012 Henri Watson.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.