<?php
namespace App\S3;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Handler{
	private static $s3Client = null;
	private static $params = null;

	public function __construct(){

	}

	public function setClient($params)
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

	public function putObject($obj)
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

	public function putObjects($objects)
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

	public function listBuckets()
	{
		try {
			return static::$s3Client->listBuckets();
		} catch (\Exception $e) {
			throw $e;
		} catch (AwsException $e) {
		    // This catches the more generic AwsException. You can grab information
		    // from the exception using methods of the exception object.
		    // echo $e->getAwsRequestId() . "\n";
		    // echo $e->getAwsErrorType() . "\n";
		    // echo $e->getAwsErrorCode() . "\n";

		    // This dumps any modeled response data, if supported by the service
		    // Specific members can be accessed directly (e.g. $e['MemberName'])
		    // var_dump($e->toArray());

		    throw $e;
		}
	    
	}
}