<?php

function _s3p_view($file, $data = null){

	if(!$data) $data = [];

	$file = str_replace('.', '/', $file);

	extract($data);

	ob_start();
		require  __DIR__ . '/../views/' . $file  . '.php';
		$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}

function _s3p_error_notice($message){
	error_log(json_encode($message));
	add_action( 'admin_notices', function() use (&$message){
		?>
	    <div class="notice error my-acf-notice is-dismissible" >
	        <p><?php _e( $message ); ?></p>
	    </div>


	    <?php
	});
}

function _s3p_public_url($url){
	$parts = explode('/uploads/', $url);
	if(isset($parts[1])){
		return App\S3::getParams()['url'] . '/' . $parts[1];
	}

	return $url;
   
}

function _s3p_get_mime($ext){

	$exts = [
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
	    'gif' => 'image/gif',
	    'png' => 'image/png',
	    'webp' => 'image/webp',
	    'ogg' => 'application/ogg',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/x-wav',
        'avi' => 'video/x-msvideo',
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'flv' => 'video/x-flv',
	];

	return $exts[$ext] ?? null;
}

function _s3p_generate_key($dirname, $file){
	return $dirname . '/' . $file;
}

function _s3p_get_objects($metadata, $action='put'){

	$medias = [];

    // upload dir
    $upload_dir = WP_CONTENT_DIR . '/uploads/';

    // get file path
    $pathinfo = pathinfo($metadata['file']);

   	$ContentType = $action == 'put' ? App\Reg::get('ContentType') : '';

    $path = $upload_dir . $pathinfo['dirname'];
    array_push($medias, ['Key' => _s3p_generate_key($pathinfo['dirname'], $pathinfo['basename']), 'SourceFile' => $upload_dir . $metadata['file'], 'ContentType' => $ContentType]);

    if($metadata['sizes']){
        foreach($metadata['sizes'] as $k => $v){
            $key = _s3p_generate_key($pathinfo['dirname'], $v['file']);
            $source = $upload_dir . $pathinfo['dirname'] . '/' . $v['file'];
            array_push($medias, ['Key' => $key, 'SourceFile' => $source, 'ContentType' => $ContentType]);
        }
    }

    return $medias;
}

function _s3p_delete_local_copies($objects){
	foreach($objects as $obj){
		if($obj['SourceFile'] && file_exists($obj['SourceFile'])){
			@unlink($obj['SourceFile']);
		}
	}

	return true;
}

function _jlog($arr){
	error_log(json_encode($arr));
}