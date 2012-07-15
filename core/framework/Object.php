<?php
/**
 * @author Matteo Vigoni <mattevigo@gmail.com>
 * @package core.framework	
 * @version 3.0
 * 
 * Class Object
 */
class Object
{	
    /**
     * Costruttore 
     * 
     * @param $db
     * @param $id
     * @return unknown_type
     */
    public function __construct(){}

    public function __destruct(){}

    /**
     * Set a variable in this Object
     * 
     * @param $index
     * @param $value
     */
    public function set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Get a variable in this Object
     * 
     * @param $index
     * @return the var value if var is set, otherwise null
     */
    public function get($key)
    {
        if(isset($this->$key))
            return $this->$key;
        return null;
    }
}

class NotImplementedException extends Exception
{
	
}