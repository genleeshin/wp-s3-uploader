<?php 
namespace App\S3;

/**
 * @package s3
 */
class S3
{

	public static function __callStatic($method, $args)
	{
		return call_user_func_array([new Handler, $method], $args);
	}


}