<?php

/**
 * Copyright (c) 2014 Henri Watson
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
 * @version        3.0
 * @license        http://opensource.org/licenses/MIT    The MIT License
 */

namespace PowerAPI;

/** Handles the basic overrides that data holding classes make use of. */
class BaseObject
{
    /** Details store for the object */
    protected $details;

    /**
     * Populates the internal details store
     * @param array $details the details to be stored
     * @return void
    */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Returns a value from the details store
     * @param string $name key for the value to be returned
     * @return mixed value
    */
    public function __get($name)
    {
        return $this->details[$name];
    }

    /**
     * Checks if a key exists in the details store
     * @param string $name key to be checked
     * @return boolean
    */
    public function __isset($name)
    {
        return isset($this->details[$name]);
    }
}
