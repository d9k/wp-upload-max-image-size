<?php

class UploadMaxImageSize {

    private static $initiated = false;
    private static $maxImageSizeKbOptionName = 'custom_umis_limit_kb';

    public static function init() {
        if ( ! self::$initiated ) {
            self::add_hooks();
        }
    }

    public static function add_hooks(){
        add_filter('upload_size_limit', array('UploadMaxImageSize', 'set_upload_size_limit_in_bytes'));
        add_action('admin_init', array('UploadMaxImageSize', 'register_settings'));
        add_action('admin_menu', array('UploadMaxImageSize', 'register_options_page'));
    }

    public function get_current_limit_kb(){
        return wp_max_upload_size() / 1024;
    }
    /**
     * Filter the upload size limit for non-administrators.
     *
     * @param string $size Upload size limit (in bytes).
     * @return int (maybe) Filtered size limit.
     */
    public static function set_upload_size_limit_in_bytes(){
        $optionSizeKb = get_option(self::$maxImageSizeKbOptionName);
        if (!$optionSizeKb || !is_numeric($optionSizeKb) || $optionSizeKb <= 0 || $optionSizeKb > (128 * 1024)){
            // 1600 kb
            return 1600 * 1024;
        } else {
            // specified KBs
            return $optionSizeKb * 1024;
        }
    }

    public static function register_settings(){
        add_option( self::$maxImageSizeKbOptionName, 25);
        register_setting( 'UploadMaxImageSize_options_group', self::$maxImageSizeKbOptionName, 'UploadMaxImageSize_callback' );
    }

    function register_options_page(){
        add_options_page('Change Image Upload Limit', 'Upload Max Image Size', 'manage_options', 'UploadMaxImageSize', array('UploadMaxImageSize', 'UploadMaxImageSize_option_page'));
    }

    function UploadMaxImageSize_option_page(){
        // content for the options page
        ?>
        <div>
            <h1></h1>
            <form method="post" action="options.php">
                <?php settings_fields('UploadMaxImageSize_options_group'); ?>
                <h3>Change Media Upload Limit</h3>
                <p>This applies to the Upload New image file size limit.</p>
                <p>Setting this value to a wrong input (smaller than 0 or larger than 128) will default to 32MB.</p>
                <p>Current limit is <?php echo self::get_current_limit_kb();?> kb</p>
                <table>
                    <tr valign="top">
                        <th scope="row"><label for="<?php echo self::$maxImageSizeKbOptionName ?>">Max (kb):</label></th>
                        <td><input type="number" id="<?php echo self::$maxImageSizeKbOptionName ?>"
                                   name="<?php echo self::$maxImageSizeKbOptionName ?>"
                                   value="<?php echo get_option(self::$maxImageSizeKbOptionName); ?>"/></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}