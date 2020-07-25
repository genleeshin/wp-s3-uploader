<?php

function wp_s3_uploader_option_page(  ) {
    // @params (page_title, menu_title, capability, menu_slug, render callback, position)
    add_options_page( 'WP S3 Uploader', 'WP S3 Uploader', 'manage_options', 'wp-s3-uploader', 'wp_s3_uploader_option_page_render' );

}

function _wp_s3_uploader_uninstall(){
    if(is_multisite()){
        delete_site_option('wp_s3_uploader_options');
    }else{
        delete_option('wp_s3_uploader_options');
    }
}

function wp_s3_uploader_options_init(  ) {
    // register settings
    // @params (option_group, option_name, args)
    register_setting( 'wp_s3_uploader', 'wp_s3_uploader_options' );

    // add a section
    add_settings_section(
        'wp_s3_uploader_option_section',
        'S3 Settings',
        'wp_s3_uploader_option_section_callback',
        'wp_s3_uploader'
    );

        // access key
    add_settings_field(
        'wp_s3_uploader_option_access_key',
        'Access Key*',
        'wp_s3_uploader_option_access_key_render',
        'wp_s3_uploader',
        'wp_s3_uploader_option_section'
    );
    // access key
    add_settings_field(
        'wp_s3_uploader_option_secret_key',
        'Secret Key*',
        'wp_s3_uploader_option_secret_key_render',
        'wp_s3_uploader',
        'wp_s3_uploader_option_section'
    );

    // region
    add_settings_field(
        'wp_s3_uploader_option_region',
        'Region*',
        'wp_s3_uploader_option_region_render',
        'wp_s3_uploader',
        'wp_s3_uploader_option_section'
    );

    // endpoint field
    add_settings_field(
        'wp_s3_uploader_option_endpoint',
        'Endpoint',
        'wp_s3_uploader_option_endpoint_render',
        'wp_s3_uploader',
        'wp_s3_uploader_option_section'
    );

    // bucket field
    add_settings_field(
        'wp_s3_uploader_option_bucket',
        'Bucket Name*',
        'wp_s3_uploader_option_bucket_render',
        'wp_s3_uploader',
        'wp_s3_uploader_option_section'
    );

    // url field
    add_settings_field(
        'wp_s3_uploader_option_url',
        'Bucket URL*',
        'wp_s3_uploader_option_url_render',
        'wp_s3_uploader',
        'wp_s3_uploader_option_section'
    );

     // url field
    add_settings_field(
        'wp_s3_uploader_option_delete_local',
        'Delete Local Copy',
        'wp_s3_uploader_option_delete_local_render',
        'wp_s3_uploader',
        'wp_s3_uploader_option_section'
    );
}

// access key
function wp_s3_uploader_option_access_key_render(  ) {
    $options = get_option( 'wp_s3_uploader_options' );
    ?>
    <input 
        type='text' 
        name='wp_s3_uploader_options[access_key]' 
        value='<?=$options['access_key']??''?>' 
        placeholder="access key" 
        required="required" 
        size="60">
    <?php
}

// secret key
function wp_s3_uploader_option_secret_key_render(  ) {
    $options = get_option( 'wp_s3_uploader_options' );
    ?>
    <input 
        type='text' 
        name='wp_s3_uploader_options[secret_key]' 
        value='<?=$options['secret_key']??''?>' 
        placeholder="secret key" 
        required="required" 
        size="60">
    <?php
}

// region
function wp_s3_uploader_option_region_render(  ) {
    $options = get_option( 'wp_s3_uploader_options' );
    ?>
    <input type='text' 
        name='wp_s3_uploader_options[region]' 
        value='<?=$options['region']??''?>' 
        placeholder="region" 
        required="required" 
        size="60">

    <p>- Required for Amazon, DigitalOcean, Scaleway and Google</p>
    <p>- Put "na" for minio and others that do not require it</p>
    <?php
}

// endpoint
function wp_s3_uploader_option_endpoint_render(  ) {
    $options = get_option( 'wp_s3_uploader_options' );
    ?>
    <input type='text' 
        name='wp_s3_uploader_options[endpoint]' 
        value='<?=$options['endpoint']??''?>' 
        placeholder="endpoint url"  
        size="60">

    <p class="helper">- Leave empty for amazon</p>
    <p class="helper">- Required for other providers</p>
    <?php
}

// bucket name
function wp_s3_uploader_option_bucket_render(  ) {
    $options = get_option( 'wp_s3_uploader_options' );
    ?>
    <input 
        type='text' name='wp_s3_uploader_options[bucket]' 
        value='<?=$options['bucket']??''?>' 
        size="60" 
        placeholder="bucket name" 
        required="required" >
    <?php
}

// bucket url
function wp_s3_uploader_option_url_render(  ) {
    $options = get_option( 'wp_s3_uploader_options' );
    ?>
    <input 
        type='text' 
        name='wp_s3_uploader_options[url]' 
        value='<?=$options['url']??''?>' 
        size="60" 
        required="required" 
        placeholder="bucket url">

    <p>- For amazon it is usually "https://s3.{REGION}.amazonaws.com/{BUCKET_NAME}"</p>
    <?php
}

function wp_s3_uploader_option_delete_local_render(  ) {
    $options = get_option( 'wp_s3_uploader_options' );
    $should_delete = isset($options['delete_local']) ? $options['delete_local'] : false;
    ?>
    <input type='checkbox' name='wp_s3_uploader_options[delete_local]' value='y' <?=checked( 'y', $should_delete, false )?>> 
    <span style="color: red;">WARNING: If checked local files in uploads folder will be deleted</span>
    <?php
}

function wp_s3_uploader_option_section_callback(  ) {
    echo __( 'Enter your details', 'wordpress' );
}

function wp_s3_uploader_option_page_render(  ) {
    ?>
    <h1>WP S3 Uploader Settings</h1>
    <?php if(defined('WP_S3_UPLOADER')){
        echo '<h4 style="color: green; font-size: 1.2rem;">Using paramteters from the config file</h4>';
        return;
    }?>
    <form action='options.php' method='post'>

        <?php
        settings_fields( 'wp_s3_uploader' );
        do_settings_sections( 'wp_s3_uploader' );
        submit_button();
        ?>

    </form>
<?php

}