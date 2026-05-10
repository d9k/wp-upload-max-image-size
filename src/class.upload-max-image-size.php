<?php

class UploadMaxImageSize {
    // 32 MB
    private const MAX_POSSIBLE_IMAGE_UPLOAD_LIMIT_KB = 32 * 1024;
    private const DEFAULT_IMAGE_UPLOAD_LIMIT_KB = 1600;
    const MAX_IMAGE_SIZE_KB_OPTION_NAME = 'custom_umis_limit_kb';

    static $initiated = false;

    public static function init() {
        if ( ! self::$initiated ) {
            self::add_hooks();
        }
    }

    public static function add_hooks(){
        // add_filter('upload_size_limit', array('UploadMaxImageSize', 'set_upload_size_limit_in_bytes'));
        add_filter('wp_handle_upload_prefilter', array('UploadMaxImageSize', 'upload_prefilter'));
        add_action('admin_init', array('UploadMaxImageSize', 'register_settings'));
        add_action('admin_menu', array('UploadMaxImageSize', 'register_options_page'));
        add_action( 'admin_head', array('UploadMaxImageSize', 'get_html_style'));
    }

    // public function get_current_limit_kb(){
    //     return wp_max_upload_size() / 1024;
    // }
    /**
     * Filter the upload size limit for non-administrators.
     *
     * @param string $size Upload size limit (in bytes).
     * @return int (maybe) Filtered size limit.
     */
    // public static function set_upload_size_limit_in_bytes(){
    //     $optionSizeKb = get_option(self::$maxImageSizeKbOptionName);
    //     if (!$optionSizeKb || !is_numeric($optionSizeKb) || $optionSizeKb <= 0 || $optionSizeKb > (128 * 1024)){
    //         // 1600 kb
    //         return 1600 * 1024;
    //     } else {
    //         // specified KBs
    //         return $optionSizeKb * 1024;
    //     }
    // }

    public static function calc_upload_size_limit_in_kb() {
        $optionSizeKb = get_option(self::MAX_IMAGE_SIZE_KB_OPTION_NAME);
        if (!$optionSizeKb || !is_numeric($optionSizeKb) || $optionSizeKb <= 0 || $optionSizeKb > self::MAX_POSSIBLE_IMAGE_UPLOAD_LIMIT_KB){
            $optionSizeKb = self::DEFAULT_IMAGE_UPLOAD_LIMIT_KB;
        }
        return $optionSizeKb;
    }

    function upload_prefilter($file) {
        error_log('__TEST__ 100: ' . var_export($file, true));

        // Calculate the image size in KB
        $image_size = $file['size'] / 1024;

        // File size limit in KB. You should change to the value you wish
        $limit_kb = self::calc_upload_size_limit_in_kb();

        // Check if it's an image
        $is_image = strpos($file['type'], 'image');

        if ( ( $image_size > $limit_kb ) && ($is_image !== false) ) {
            $file['error'] =  sprintf(__('Your picture is too large. It has to be smaller than %d KB'), $limit_kb);
        }

        return $file;
    }

    public static function register_settings(){
        add_option( self::MAX_IMAGE_SIZE_KB_OPTION_NAME, 25);
        register_setting( 'UploadMaxImageSize_options_group', self::MAX_IMAGE_SIZE_KB_OPTION_NAME, 'UploadMaxImageSize_callback' );
    }

    function register_options_page(){
        add_options_page('Change Image Upload Limit', 'Upload Max Image Size', 'manage_options', 'UploadMaxImageSize', array('UploadMaxImageSize', 'UploadMaxImageSize_option_page'));
    }

    function get_html_style() {
        ?>
        <style>
            .upload-max-image-size table {
                margin-left: -4px;
            }

            .upload-max-image-size th {
                vertical-align: middle;
            }
        </style>
        <?php
    }

    function UploadMaxImageSize_option_page(){
        // content for the options page
        ?>
        <div class="upload-max-image-size">
            <h1></h1>
            <form method="post" action="options.php">
                <?php settings_fields('UploadMaxImageSize_options_group'); ?>
                <h3><?php _e('Change Media Upload Limit'); ?> </h3>
                <p><?php _e('This applies to the Upload New image file size limit.'); ?></p>
                <p><?php
                    printf(
                        __('Setting this value to a wrong input (smaller than 0 or larger than %d KB) will default to %d KB'),
                        self::MAX_POSSIBLE_IMAGE_UPLOAD_LIMIT_KB,
                        self::DEFAULT_IMAGE_UPLOAD_LIMIT_KB
                    ); ?></p>
                <!-- <p>Current limit is <?php /* echo self::get_current_limit_kb();*/ ?> kb</p> -->
                <table>
                    <tr valign="top">
                        <th scope="row"><label for="<?php echo self::MAX_IMAGE_SIZE_KB_OPTION_NAME ?>">Max upload image size (KB):</label></th>
                        <td><input type="number" id="<?php echo self::MAX_IMAGE_SIZE_KB_OPTION_NAME ?>"
                                   name="<?php echo self::MAX_IMAGE_SIZE_KB_OPTION_NAME ?>"
                                   value="<?php echo get_option(self::MAX_IMAGE_SIZE_KB_OPTION_NAME); ?>"/></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}