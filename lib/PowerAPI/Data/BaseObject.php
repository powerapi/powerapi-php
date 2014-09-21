<?php

namespace PowerAPI\Data;

/** Handles the basic overrides that data holding classes make use of. */
class BaseObject
{
    /** Details store for the object
     * @var array
     */
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
