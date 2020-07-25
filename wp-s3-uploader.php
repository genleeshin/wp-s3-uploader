<?php
/**
 * Plugin Name: WP S3 Uploader
 * Description: Upload files directly to Amazon S3, Minio, Sacleway, Google Cloud Storage and Other S3 compatible object storage providers
 * Author: Nasim Mahmud
 * Version: 0.01
 * Requires at least: Wordpress 5.x
 * Requires PHP: 7.x
 * Network: true
 */

require __DIR__.'/vendor/autoload.php';

// aws sdk

use App\S3;
use App\Reg;

if(defined('WP_S3_UPLOADER')){
    $params = WP_S3_UPLOADER;
}else{
    $params = get_option('wp_s3_uploader_options', []);
}
try {
    S3::setClient($params);
} catch (\Exception $e) {
    _s3p_error_notice('WP S3 Uploader is not configured, <a href="/wp-admin/options-general.php?page=wp-s3-uploader"> Click here</a> to configure');
}


//  register settings pages
if(is_admin()){

    // register settings page
    add_action( 'admin_menu', 'wp_s3_uploader_option_page' );

    // settings form 
    add_action( 'admin_init', 'wp_s3_uploader_options_init' );

    // links to plugin settings page
    add_filter( "plugin_action_links_" . plugin_basename(__FILE__), function( $links ){
        $settings_link = '<a href="/wp-admin/options-general.php?page=wp-s3-uploader">' . __( 'Settings' ) . '</a>';

        array_push( $links, $settings_link );
        return $links;
    } );

    // on uninstall
    register_uninstall_hook(__FILE__, '_wp_s3_uploader_uninstall');
}

if( isset( $params['access_key'] ) ){

    add_filter('wp_get_attachment_metadata', function( $data ) {
        
        Reg::set('_wps3', isset($data['_wps3']));

        if(isset($data['_wps3'])){
            $url = wp_upload_dir()['baseurl'] . '/' . $data['file'];
            Reg::set( 'url', $url );
        }else{
            Reg::set( 'url', null );
        }
 
        
        return $data;

    } );


    // change file url
    add_filter('wp_get_attachment_url', function($url){
        
        if(Reg::get('url') === $url){
            return _s3p_public_url($url);
        }

        return $url;
        
    });

    add_filter('wp_get_attachment_image_attributes', function( $attr ) {
        // _jlog($attr['src']);
        if(Reg::get('_wps3') === true){
            
            $attr['src'] = _s3p_public_url( $attr['src']) ;
            $attr['srcset'] = _s3p_public_url( $attr['srcset'] );
        }
        return $attr;
    });

    // change file url before it is added to db
    add_filter('wp_handle_upload', function($file){
        // $parts = explode('/uploads/', $file['url']);
        // $file['url'] = S3::getParams()['url'] . '/' . $parts[1];
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