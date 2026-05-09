<?php

class MediaUploadLimit {

    private static $initiated = false;
    private static $uploadOptionName = 'custom_media_upload_limit';

    public static function init() {
        if ( ! self::$initiated ) {
            self::add_hooks();
        }
    }

    public static function add_hooks(){
        add_filter('upload_size_limit', array('MediaUploadLimit', 'set_upload_size_limit'));
        add_action('admin_init', array('MediaUploadLimit', 'register_settings'));
        add_action('admin_menu', array('MediaUploadLimit', 'register_options_page'));
    }

    public function get_current_limit(){
        return wp_max_upload_size() / 1024 / 1024;
    }
    /**
     * Filter the upload size limit for non-administrators.
     *
     * @param string $size Upload size limit (in bytes).
     * @return int (maybe) Filtered size limit.
     */
    public static function set_upload_size_limit(){
        $optionSize = get_option(self::$uploadOptionName);
        if (!$optionSize || !is_numeric($optionSize) || $optionSize <= 0 || $optionSize > 128){
            // 32 MB
            return 32 * 1024 * 1024;
        } else {
            // specified MBs
            return $optionSize * 1024 * 1024;
        }
    }

    public static function register_settings(){
        add_option( self::$uploadOptionName, 25);
        register_setting( 'MediaUploadLimit_options_group', self::$uploadOptionName, 'MediaUploadLimit_callback' );
    }

    function register_options_page(){
        add_options_page('Change File Upload Limit', 'Media Upload Limit', 'manage_options', 'MediaUploadLimit', array('MediaUploadLimit', 'MediaUploadLimit_option_page'));
    }

    function MediaUploadLimit_option_page(){
        // content for the options page
        ?>
        <div>
            <h1></h1>
            <form method="post" action="options.php">
                <?php settings_fields('MediaUploadLimit_options_group'); ?>
                <h3>Change Media Upload Limit</h3>
                <p>This applies to the Upload New Media file size limit.</p>
                <p>Setting this value to a wrong input (smaller than 0 or larger than 128) will default to 32MB.</p>
                <p>Current limit is <?php echo self::get_current_limit();?> MB</p>
                <table>
                    <tr valign="top">
                        <th scope="row"><label for="<?php echo self::$uploadOptionName ?>">Max (MB):</label></th>
                        <td><input type="number" id="<?php echo self::$uploadOptionName ?>"
                                   name="<?php echo self::$uploadOptionName ?>"
                                   value="<?php echo get_option(self::$uploadOptionName); ?>"/></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}