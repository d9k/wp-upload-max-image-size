<?php

class UploadMaxImageSize {
    // 32 MB
    private const MAX_POSSIBLE_IMAGE_UPLOAD_LIMIT_KB = 32 * 1024;
    private const DEFAULT_IMAGE_UPLOAD_LIMIT_KB = 1600;
    const MAX_IMAGE_SIZE_KB_OPTION_NAME = 'custom_umis_limit_kb';

    static $initiated = false;

    public static function init() {
        if (! self::$initiated) {
            self::add_hooks();
        }
    }

    public static function add_hooks() {
        add_filter('wp_handle_upload_prefilter', array('UploadMaxImageSize', 'upload_prefilter'));
        add_action('admin_init', array('UploadMaxImageSize', 'register_settings'));
        add_action('admin_menu', array('UploadMaxImageSize', 'register_options_page'));
        add_action('admin_head', array('UploadMaxImageSize', 'get_html_style'));
        add_action('admin_action_umis_reset', array('UploadMaxImageSize', 'umis_reset_admin_action'));
    }

    public static function calc_upload_size_limit_in_kb() {
        $optionSizeKb = get_option(self::MAX_IMAGE_SIZE_KB_OPTION_NAME);
        return self::custom_umis_limit_kb_callback($optionSizeKb);
    }

    function upload_prefilter($file) {
        // Skip files with .big before extension (e.g., my-file.big.jpg)
        $filename = $file['name'];
        if (preg_match('/\.big\.[^.]+$/i', $filename)) {
            return $file;
        }

        // Calculate the image size in KB
        $image_size = $file['size'] / 1024;

        // File size limit in KB. You should change to the value you wish
        $limit_kb = self::calc_upload_size_limit_in_kb();

        // Check if it's an image
        $is_image = strpos($file['type'], 'image');

        if (($image_size > $limit_kb) && ($is_image !== false)) {
            $file['error'] =  sprintf(__('Please reduce the size of the uploaded image to less than %d KB. if it is absolutely necessary to upload large image, rename the file to include ".big" right before the extension (for example "my-map.big.jpg").', 'upload-max-image-size'), $limit_kb);
        }

        return $file;
    }

    public static function custom_umis_limit_kb_callback($optionSizeKb) {
        if (!$optionSizeKb || !is_numeric($optionSizeKb) || $optionSizeKb <= 0 || $optionSizeKb > self::MAX_POSSIBLE_IMAGE_UPLOAD_LIMIT_KB) {
            $optionSizeKb = self::DEFAULT_IMAGE_UPLOAD_LIMIT_KB;
        }
        return $optionSizeKb;
    }

    public static function register_settings() {
        add_option(self::MAX_IMAGE_SIZE_KB_OPTION_NAME, self::DEFAULT_IMAGE_UPLOAD_LIMIT_KB);
        register_setting('UploadMaxImageSize_options_group', self::MAX_IMAGE_SIZE_KB_OPTION_NAME, ['sanitize_callback' => ['UploadMaxImageSize', 'custom_umis_limit_kb_callback']]);
    }

    public static function umis_load_textdomain() {
      	load_plugin_textdomain('upload-max-image-size', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
    }

    function register_options_page() {
        add_options_page(
            // $page_title=
            'Change Image Upload Limit',
            // $menu_title=
            'Upload Max Image Size',
            // $capability =
            'manage_options',
            // $menu_slug =
            'UploadMaxImageSize',
            // $callback =
            array('UploadMaxImageSize', 'UploadMaxImageSize_option_page')
        );
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

            .upload-max-image-size .button-danger {
                background: #d63638;
                border-color: #d63638;
                color: #fff;
            }

            .upload-max-image-size .button-danger:hover {
                background: #b32d2f;
                border-color: #b32d2f;
                color: #fff;
            }

            .upload-max-image-size .form-buttons {
                margin-top: 32px;
                display: flex;
                align-items: baseline
            }

            .upload-max-image-size .form-buttons>* {
                display: inline-flex;
            }

            .upload-max-image-size .form-buttons>*+* {
                margin-left: 20px;
            }

            .upload-max-image-size p.submit {
                padding: 0;
                margin: 0;
            }
        </style>
    <?php
    }

    function UploadMaxImageSize_option_page() {
        // content for the options page
    ?>
        <div class="upload-max-image-size">
            <h1></h1>
            <form method="post" action="options.php">
                <?php settings_fields('UploadMaxImageSize_options_group'); ?>
                <h3><?php _e('Change Media Upload Limit', 'upload-max-image-size'); ?> </h3>
                <p><?php _e('This applies to the Upload New image file size limit.', 'upload-max-image-size'); ?></p>
                <p><?php _e('To bypass the size limit for specific images, rename the file to include ".big" before the extension (for example "my-map.big.jpg").', 'upload-max-image-size'); ?></p>
                <p><?php
                    printf(
                        __('Setting this value to a wrong input (smaller than 0 or larger than %d KB) will default to %d KB.', 'upload-max-image-size'),
                        self::MAX_POSSIBLE_IMAGE_UPLOAD_LIMIT_KB,
                        self::DEFAULT_IMAGE_UPLOAD_LIMIT_KB
                    ); ?></p>
                <!-- <p>Current limit is <?php /* echo self::get_current_limit_kb();*/ ?> kb</p> -->
                <table>
                    <tr valign="top">
                        <th scope="row"><label for="<?php echo self::MAX_IMAGE_SIZE_KB_OPTION_NAME ?>">
                            <?php _e('Max upload image size (KB):', 'upload-max-image-size'); ?>
                        </label></th>
                        <td>
                            <input type="number" id="<?php echo self::MAX_IMAGE_SIZE_KB_OPTION_NAME ?>"
                                name="<?php echo self::MAX_IMAGE_SIZE_KB_OPTION_NAME ?>"
                                value="<?php echo get_option(self::MAX_IMAGE_SIZE_KB_OPTION_NAME); ?>" />
                        </td>
                    </tr>
                </table>
                <div class="form-buttons">
                    <?php submit_button(); ?>
                    <input
                        type="submit"
                        value="<?php _e('Reset extension settings', 'upload-max-image-size'); ?>" class="button button-danger"
                        form="form_umis_reset"
                        onclick="return confirm('<?php _e('Are you sure you want to reset the extension settings to default?', 'upload-max-image-size'); ?>');" />
                </div>
            </form>
            <form id="form_umis_reset" method="POST" action="<?php echo admin_url('admin.php'); ?>" style="display: none;">
                <input type="hidden" name="action" value="umis_reset" />
                <?php wp_nonce_field('umis_reset_action', 'umis_reset_nonce'); ?>
            </form>
        </div>
<?php
    }

    public static function umis_reset_admin_action() {
        if (!isset($_POST['umis_reset_nonce']) || !wp_verify_nonce($_POST['umis_reset_nonce'], 'umis_reset_action')) {
            wp_die(__('Security check failed', 'upload-max-image-size'));
        }

        delete_option(self::MAX_IMAGE_SIZE_KB_OPTION_NAME);
        wp_redirect(admin_url('options-general.php?page=UploadMaxImageSize'));
        exit();
    }
}
