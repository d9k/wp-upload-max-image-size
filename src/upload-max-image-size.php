<?php
/**
 * Plugin Name: Upload Max Image Size
 * Description: Set a custom image upload limit.
 * Author: Komarov Dmitrii  [d9k], Tiffany Elsten
 * Version: 0.1
 */

include( plugin_dir_path( __FILE__ ) . 'class.upload-max-image-size.php');

add_action( 'init', array( 'UploadMaxImageSize', 'init' ) );
