<?php
if ( !function_exists( '_s3p_view' ) ) {
    function _s3p_view( $file, $data = null ){

        if(!$data) $data = [];

        $file = str_replace( '.', '/', $file );

        extract($data);

        ob_start();
            require  __DIR__ . '/../views/' . $file  . '.php';
            $output = ob_get_contents();
        ob_end_clean();
        
        return $output;
    }
}

if ( ! function_exists( '_s3p_error_notice' ) ) {
    function _s3p_error_notice( $message ){
        // _jlog($message);
        add_action( 'admin_notices', function() use ( &$message ){
            ?>
            <div class="notice error my-acf-notice is-dismissible" >
                <p><?php _e( $message ); ?></p>
            </div>


            <?php
        });
    }
}

if ( ! function_exists( '_s3p_public_url' ) ) {
    function _s3p_public_url( $url ){
        $file = explode('/uploads', $url);
        if(isset($file[1])) {
            $url = App\S3::getParams( 'url' ) .  $file[1];
        }
        
        return $url;
    
    }
}

if ( ! function_exists( '_s3p_generate_key' ) ) {
    function _s3p_generate_key( $dirname, $file ){
        return $dirname . '/' . $file;
    }
}

if ( ! function_exists( '_s3p_get_objects' ) ) {
    function _s3p_get_objects( &$metadata, $action='put' ){

        $medias = [];

        // upload dir
        $file = App\Reg::get('file');

        // get file path
        $pathinfo = pathinfo($file['file']);
        $upload_dir = $pathinfo['dirname'];
        $key_base = explode('/uploads/', $upload_dir)[1];
        $metadata['_wps3_upload_url'] = str_replace($pathinfo['dirname'], '', wp_upload_dir()['baseurl']);
        $metadata['key_base'] = $key_base;

        $ContentType = $action == 'put' ? $file['type'] : '';

        $path = $upload_dir . $pathinfo['dirname'];
        array_push($medias, ['Key' => _s3p_generate_key($key_base, $pathinfo['basename']), 'SourceFile' => $file['file'], 'ContentType' => $ContentType]);

        if($metadata['sizes']){
            foreach($metadata['sizes'] as $k => $v){
                $key = _s3p_generate_key($key_base, $v['file']);
                $source = $upload_dir . '/' . $v['file'];
                array_push($medias, ['Key' => $key, 'SourceFile' => $source, 'ContentType' => $ContentType]);
            }
        }

        return $medias;
    }
}

if ( ! function_exists( '_s3p_objects_to_delete' ) ) {
    function _s3p_objects_to_delete($metadata){
        $objects = [];

        // get file path
        $pathinfo = pathinfo($metadata['file']);

        array_push($objects, ['Key' => _s3p_generate_key($metadata['key_base'], $pathinfo['basename'])]);

        if($metadata['sizes']){
            foreach($metadata['sizes'] as $k => $v){
                $key = _s3p_generate_key($metadata['key_base'], $v['file']);
                array_push($objects, ['Key' => $key]);
            }
        }

        return $objects;
    }
}

if ( ! function_exists( '_s3p_delete_local_copies' ) ) {
    function _s3p_delete_local_copies($objects){
        foreach($objects as $obj){
            if($obj['SourceFile'] && file_exists($obj['SourceFile'])){
                @unlink($obj['SourceFile']);
            }
        }

        return true;
    }
}

if ( ! function_exists( '_jlog' ) ) {
    function _jlog($arr){
        error_log(json_encode($arr));
    }
}