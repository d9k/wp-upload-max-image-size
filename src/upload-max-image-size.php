<?php
/**
 * Plugin Name: Upload Max Image Size
 * Description: Set a custom image upload limit.
 * Author: Komarov Dmitrii  [d9k], Tiffany Elsten
 * Text Domain: upload-max-image-size
 * Domain Path: /languages
 * Version: 0.3
 */

include( plugin_dir_path( __FILE__ ) . 'class.upload-max-image-size.php');

add_action( 'init', array( 'UploadMaxImageSize', 'init' ) );

add_action('plugins_loaded', array('UploadMaxImageSize', 'umis_load_textdomain'));
