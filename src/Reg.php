<?php
namespace App;

class Reg
{
	protected static $data = [];
	
	public static function set($key, $value)
	{
	
		static::$data[$key] = $value;
	
	}

	public static function get($key)
	{

		if(! array_key_exists($key, static::$data))

			return null;
			

		return static::$data[$key];

	}

	public static function __callStatic($mthod, $args){
		if(empty($args) && array_key_exists($mthod, static::$data))
    		return static::$data[$mthod];

        else if(count($args)>0)
           return static::$data[$mthod] = $args[0];
       
        else
            return null;
	}
}