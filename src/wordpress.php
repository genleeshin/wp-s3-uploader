<?php

function aws_s3_pro_option_page(  ) {
    // @params (page_title, menu_title, capability, menu_slug, render callback, position)
    add_options_page( 'AWS S3', 'AWS S3 Settings', 'manage_options', 'aws-s3-pro-settings', 'aws_s3_pro_option_page_render' );
}

function aws_s3_pro_options_init(  ) {
    // register settings
    // @params (option_group, option_name, args)
    register_setting( 'aws_s3_pro', 'aws_s3_pro_options' );

    // add a section
    add_settings_section(
        'aws_s3_pro_option_section',
        'S3 Settings',
        'aws_s3_pro_option_section_callback',
        'aws_s3_pro'
    );

        // access key
    add_settings_field(
        'ws_s3_pro_option_access_key',
        'Access Key',
        'aws_s3_pro_option_access_key_render',
        'aws_s3_pro',
        'aws_s3_pro_option_section'
    );
    // access key
    add_settings_field(
        'ws_s3_pro_option_secret_key',
        'Secret Key',
        'aws_s3_pro_option_secret_key_render',
        'aws_s3_pro',
        'aws_s3_pro_option_section'
    );

    // region
    add_settings_field(
        'ws_s3_pro_option_region',
        'Region',
        'aws_s3_pro_option_region_render',
        'aws_s3_pro',
        'aws_s3_pro_option_section'
    );

    // endpoint field
    add_settings_field(
        'ws_s3_pro_option_endpoint',
        'Endpoint',
        'aws_s3_pro_option_endpoint_render',
        'aws_s3_pro',
        'aws_s3_pro_option_section'
    );

    // bucket field
    add_settings_field(
        'ws_s3_pro_option_bucket',
        'Bucket Name',
        'aws_s3_pro_option_bucket_render',
        'aws_s3_pro',
        'aws_s3_pro_option_section'
    );

    // url field
    add_settings_field(
        'ws_s3_pro_option_url',
        'Public URL',
        'aws_s3_pro_option_url_render',
        'aws_s3_pro',
        'aws_s3_pro_option_section'
    );

     // url field
    add_settings_field(
        'ws_s3_pro_option_delete_local',
        'Delete Local Copy',
        'aws_s3_pro_option_delete_local_render',
        'aws_s3_pro',
        'aws_s3_pro_option_section'
    );
}

function aws_s3_pro_option_endpoint_render(  ) {
    $options = get_option( 'aws_s3_pro_options' );
    ?>
    <input type='text' name='aws_s3_pro_options[endpoint]' value='<?=$options['endpoint']??''?>' size="60">
    <?php
}

function aws_s3_pro_option_region_render(  ) {
    $options = get_option( 'aws_s3_pro_options' );
    ?>
    <input type='text' name='aws_s3_pro_options[region]' value='<?=$options['region']??''?>' size="60">
    <?php
}

function aws_s3_pro_option_access_key_render(  ) {
    $options = get_option( 'aws_s3_pro_options' );
    ?>
    <input type='text' name='aws_s3_pro_options[access_key]' value='<?=$options['access_key']??''?>' size="60">
    <?php
}

function aws_s3_pro_option_secret_key_render(  ) {
    $options = get_option( 'aws_s3_pro_options' );
    ?>
    <input type='text' name='aws_s3_pro_options[secret_key]' value='<?=$options['secret_key']??''?>' size="60">
    <?php
}

function aws_s3_pro_option_bucket_render(  ) {
    $options = get_option( 'aws_s3_pro_options' );
    ?>
    <input type='text' name='aws_s3_pro_options[bucket]' value='<?=$options['bucket']??''?>' size="60" placeholder="bucket name">
    <?php
}

function aws_s3_pro_option_url_render(  ) {
    $options = get_option( 'aws_s3_pro_options' );
    ?>
    <input type='text' name='aws_s3_pro_options[url]' value='<?=$options['url']??''?>' size="60" placeholder="bucket url">
    <?php
}

function aws_s3_pro_option_delete_local_render(  ) {
    $options = get_option( 'aws_s3_pro_options' );
    $should_delete = isset($options['delete_local']) ? $options['delete_local'] : false;
    ?>
    <input type='checkbox' name='aws_s3_pro_options[delete_local]' value='y' <?=checked( 'y', $should_delete, false )?>> 
    <span style="color: red;">WARNING: If checked local files in uploads folder will be deleted</span>
    <?php
}

function aws_s3_pro_option_section_callback(  ) {
    echo __( 'Enter your details', 'wordpress' );
}

function aws_s3_pro_option_page_render(  ) {
    ?>
    <form action='options.php' method='post'>

        <h2>Sitepoint Settings API Admin Page</h2>

        <?php
        settings_fields( 'aws_s3_pro' );
        do_settings_sections( 'aws_s3_pro' );
        submit_button();
        ?>

    </form>
    <?php
}