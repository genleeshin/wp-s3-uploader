<?php
namespace App;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class S3{
	private static $s3Client = null;
	private static $params = null;

	public function __construct(){

	}

	protected function setClient($params)
	{
	    try {

	    	static::$params = $params;

	    	if( empty($params['access_key']) ||  empty($params['secret_key']) || empty($params['region']) ){
	    		throw new \Exception('AWS S3 Pro configuration is not set');
	    	}

	    	static::$s3Client = new S3Client([
			    "endpoint" => $params['endpoint'],
			    "s3ForcePathStyle" => true,
			    "version" => "latest",
			    "region" => $params['region'],
			    'signature_version' => 'v4',
			    "credentials" => [
			        "key" => $params['access_key'],
			        "secret" => $params['secret_key']
			    ]
			]); 
	    	
	    } catch (\Exception $e) {
	    	_s3p_error_notice($e->getMessage());
	    }
	}

	protected function getParams($key=null){
		if($key){
			return static::$params[$key] ?? null;
		}
		
		return static::$params;
	}

	protected function putObject($obj)
	{
	    try {

	    	if(!file_exists($obj['SourceFile']) || empty($obj['Key'] || empty($obj['ContentType']))){
	    		throw new \Exception('Invalid file type');
	    	}

	    	$media = [
                'Bucket' => static::$params['bucket'],
                'Key' => $obj['Key'],
                'SourceFile' => $obj['SourceFile'],
                'ContentType' => $obj['ContentType'],
                'ACL' => 'public-read',
                'Expires' => gmdate("D, d M Y H:i:s T", strtotime("+1 years")),
                'CacheControl' => 'public, max-age=2592000',
            ];

	    	static::$s3Client->putObject($media);

	    	return true;
	    	
	    } catch (\Exception $e) {
	    	throw $e;
	    } catch (AwsException $e) {
	    	throw $e;
		}
	}

	protected function putObjects($objects)
	{
	    try {
	    	foreach($objects as $obj){
	    		$this->putObject($obj);
	    	}

	    	return true;
	    } catch (\Exception $e) {
	    	throw $e;
	    }
	}

	protected function deleteObject($obj)
	{
	    try {

	    	$media = [
                'Bucket' => static::$params['bucket'],
                'Key' => $obj['Key']
            ];

	    	static::$s3Client->deleteObject($media);

	    	return true;
	    	
	    } catch (\Exception $e) {
	    	throw $e;
	    } catch (AwsException $e) {
	    	throw $e;
	    }
	}
		

	protected function deleteObjects($objects){
		try {
	    	foreach($objects as $obj){
	    		$this->deleteObject($obj);
	    	}

	    	return true;
	    } catch (\Exception $e) {
	    	throw $e;
	    }
	}

	protected function listBuckets()
	{
		try {
			return static::$s3Client->listBuckets();
		} catch (\Exception $e) {
			throw $e;
		} catch (AwsException $e) {

		    throw $e;
		}
	    
	}

	public static function __callStatic($method, $args)
	{
		return call_user_func_array([new self, $method], $args);
	}

}