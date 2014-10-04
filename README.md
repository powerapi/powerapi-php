[![Code Climate](http://img.shields.io/codeclimate/github/powerapi/powerapi-php.svg?style=flat-square)](https://codeclimate.com/github/powerapi/powerapi-php)
[![Packagist Version](http://img.shields.io/packagist/v/powerapi/powerapi-php.svg?style=flat-square)](https://packagist.org/packages/powerapi/powerapi-php)

PowerAPI-php
============
Library for fetching information from PowerSchool SISes.

Requirements
------------
* PHP 5 >= 5.1.2.
* PowerSchool 8.x; PowerSchool >= 7.1.0

Install
-----
Use [Composer](http://getcomposer.org/) to handle including/downloading
the library and its dependencies for you.

```
$ composer require powerapi/powerapi-php:~3.0
```

For more information on how to install dependencies with Composer, see
Composer's documentation on [installing dependencies](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies).

Usage example
-------------
The following snippet logs into a PowerSchool server and prints the name of
each of the student's sections.

```PHP
<?php
require_once 'vendor/autoload.php'; // composer autoloader

try {
    $student = PowerAPI\PowerAPI::authenticate("https://powerschool.example/", "username", "password");
} catch (PowerAPI\Exceptions\Authentication $e) {
    die('Something went wrong! '.$e->getMessage());
}

foreach ($student->sections as $section) {
    echo $section->name."\n";
}
```

License
-------

    Copyright (c) 2014 Henri Watson

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
