<?php
/**
 * Plugin Name: Media Upload Limit
 * Description: Set a custom media upload limit.
 * Author: Tiffany Elsten
 * Version: 1.0
 */
;

include( plugin_dir_path( __FILE__ ) . 'class.media-upload-limit.php');

add_action( 'init', array( 'MediaUploadLimit', 'init' ) );
