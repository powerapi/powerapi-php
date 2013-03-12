PowerAPI-php
============
Library for fetching information from PowerSchool SISes.

Requirements
------------
* PHP 5 >= 5.1.2.
* PowerSchool >= 7.0.0; PowerSchool <= 7.6.2

Usage
-----
You should use [Composer](http://getcomposer.org/) to handle including/downloading the library for you. A basic demo is provided in `demo.php`

### Initializing the library ###
	
	$ps = new PowerAPI("http://psserver/", PSVERSION);	// Specify the server's URL and version

### Authenticating as a user ###
	$user = $ps->auth(USERNAME, PASSWORD);

Provide the user's username and password. Returns a PowerAPIUser object.

### Fetching the user's transcript ###
	$user->fetchTranscript();
	
Returns an XML file representing the authenticated user's transcript.

License
-------
Copyright (c) 2013 Henri Watson.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
