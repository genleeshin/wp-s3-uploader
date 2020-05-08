<?php
/**
 * Plugin Name: WP S3 Uploader
 * Description: Upload file directly to Amazon S3, Minio, Sacleway, Google Cloud Storage and Other S3 compatible object storage providers
 * Author: Nasim Mahmud
 */
require __DIR__.'/vendor/autoload.php';

// aws sdk

use App\S3;
use App\Reg;


if(defined('WP_S3_UPLOADER')){
    $params = WP_S3_UPLOADER;
}else{
    $params = get_option('aws_s3_pro_options', []);
}

S3::setClient($params);

//  register settings pages
if(is_admin()){

    // register settings page
    add_action( 'admin_menu', 'aws_s3_pro_option_page' );

    // settings form 
    add_action( 'admin_init', 'aws_s3_pro_options_init' );
}

if(isset($params['access_key'])){

    add_filter('wp_get_attachment_metadata', function($data){
        // _jlog(isset($data['_wps3']));
        Reg::set('_wps3', isset($data['_wps3']));
        
        return $data;
    });

    // change file url
    add_filter('wp_get_attachment_url', function($url){

        if(stripos($url, 'uploads') !== false && Reg::get('_wps3') === true){
            return _s3p_public_url($url);
        }

        return $url;
        
    });

    add_filter('wp_calculate_image_srcset', function($file){
        
        $newArr = [];

        foreach($file as $k=>$v){
            $url = $file[$k]['url'];
            if(stripos($url, 'uploads') !== false && Reg::get('_wps3') === true){
                $file[$k]['url'] = S3::getParams('url') . explode('/uploads', $url)[1];
            }
        }
        
        
        return $file;
    });

    // change file url before it is added to db
    add_filter('wp_handle_upload', function($file){
        $parts = explode('/uploads/', $file['url']);
        $file['url'] = S3::getParams()['url'] . '/' . $parts[1];
        Reg::set('file', $file);
        return $file;
    });

    // on new media upload process upload to s3
    add_filter('wp_generate_attachment_metadata', 'uploadToS3');

    function uploadToS3($metadata){
        
        try {
            // _jlog($metadata);
            $objects = _s3p_get_objects($metadata);

            // _jlog($metadata);
            
            S3::putObjects($objects);

            $metadata['_wps3'] = 1;

            // delete local copies if option set
            if(S3::getParams('delete_local') === 'y'){
                _s3p_delete_local_copies($objects);
            }

        } catch (Exception $e) {
            _s3p_error_notice($e->getMessage());
        }

        return $metadata;


    }

    // on delete attachement
    add_action('delete_attachment', function($attachment_id){
        try {
            $metadata = wp_get_attachment_metadata($attachment_id);
            if(!isset($metadata['_wps3'])) return;
            $objects = _s3p_objects_to_delete($metadata);
            // _jlog($objects);
            S3::deleteObjects($objects);
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            _s3p_error_notice('Failed to delete object');
        }
        
    });
}