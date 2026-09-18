<?php
/**
 * Admin Ajax Component
 *
 * Handles all AJAX requests for the admin interface
 *
 * @package EXA11Y
 * @subpackage Admin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Admin_Ajax
 *
 * Handles AJAX actions for admin functionalities.
 */
class EXA11Y_Admin_Ajax {

    /**
     * Initialize the class.
     */
    public function __construct() {
        // Register Ajax actions
        add_action('wp_ajax_exa11y_reset_settings', array($this, 'handle_reset_settings'));
        add_action('wp_ajax_exa11y_reset_accessibility_features', array($this, 'handle_reset_accessibility_features'));
        add_action('wp_ajax_exa11y_clear_cache', array($this, 'handle_clear_cache'));
        add_action('wp_ajax_exa11y_regenerate_statement', array($this, 'handle_regenerate_statement'));
        add_action('wp_ajax_exa11y_export_settings', array($this, 'handle_export_settings'));
        add_action('wp_ajax_exa11y_import_settings', array($this, 'handle_import_settings'));
        add_action('wp_ajax_exa11y_reset_all_settings', array($this, 'handle_reset_all_settings'));
    }

    /**
     * Shared request guard for every handler in this class.
     *
     * Verifies the exa11y_admin_ajax nonce (sent as 'security') and the
     * manage_options capability.
     *
     * @throws Exception When the nonce or capability check fails.
     */
    private function verify_request() {
        if (!isset($_POST['security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'exa11y_admin_ajax')) {
            throw new Exception(esc_html__('Security check failed.', 'exa11y-expo-accessibility-kit'));
        }

        if (!current_user_can('manage_options')) {
            throw new Exception(esc_html__('You do not have permission to perform this action.', 'exa11y-expo-accessibility-kit'));
        }
    }

    /**
     * Replace exa11y_settings with the canonical defaults.
     *
     * @param bool $with_backup Store the current settings in
     *                          exa11y_settings_backup first (throws if the old
     *                          option cannot be deleted, matching the original
     *                          reset behavior).
     * @throws Exception When the backup-path delete fails.
     */
    private function reset_to_defaults($with_backup) {
        if ($with_backup) {
            // Backup current settings before reset
            $current_settings = get_option('exa11y_settings', array());
            update_option('exa11y_settings_backup', $current_settings);

            if (!delete_option('exa11y_settings')) {
                throw new Exception(esc_html__('Failed to reset settings.', 'exa11y-expo-accessibility-kit'));
            }
        }

        // Re-add the canonical default options.
        update_option('exa11y_settings', exa11y_get_default_settings());
    }

    /**
     * Reset a list of feature keys inside exa11y_settings.
     *
     * @param array $features    Setting keys to reset.
     * @param bool  $to_defaults When true, restore each key to its canonical
     *                           default; when false, force each key to 0.
     * @return bool Whether update_option() reported a change.
     */
    private function apply_feature_reset(array $features, $to_defaults) {
        $settings = get_option('exa11y_settings', array());
        $defaults = $to_defaults ? exa11y_get_default_settings() : array();

        foreach ($features as $feature) {
            if ($to_defaults) {
                if (isset($defaults[$feature])) {
                    $settings[$feature] = $defaults[$feature];
                }
            } else {
                $settings[$feature] = 0;
            }
        }

        return update_option('exa11y_settings', $settings);
    }

    /**
     * Drop the cached copies of this plugin's options so the next read comes
     * from the database. Scoped to our own option names -- never a sitewide
     * cache flush.
     */
    private function clear_plugin_cache() {
        foreach (array('exa11y_settings', 'exa11y_settings_backup', 'exa11y_accessibility_statement_page_id') as $option) {
            wp_cache_delete($option, 'options');
        }
    }

    /**
     * Handle reset settings Ajax request
     */
    public function handle_reset_settings() {
        try {
            $this->verify_request();

            $this->reset_to_defaults(true);

            wp_send_json_success(array('message' => esc_html__('All settings have been reset to their default values.', 'exa11y-expo-accessibility-kit')));
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    /**
     * Handle clear cache Ajax request
     */
    public function handle_clear_cache() {
        try {
            $this->verify_request();

            $this->clear_plugin_cache();

            wp_send_json_success(array('message' => esc_html__('Plugin cache has been cleared successfully.', 'exa11y-expo-accessibility-kit')));
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    /**
     * Handle regenerate accessibility statement Ajax request
     */
    public function handle_regenerate_statement() {
        try {
            $this->verify_request();
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }

        // Get the accessibility statement page ID
        $statement_page_id = get_option('exa11y_accessibility_statement_page_id', 0);
        $page_exists = ($statement_page_id > 0 && get_post($statement_page_id) !== null);
        $reload = false;

        // Generate the content using the function from exa11y-expo-accessibility-kit.php
        if (function_exists('exa11y_get_accessibility_statement_content')) {
            $content = exa11y_get_accessibility_statement_content();

            if ($page_exists) {
                // Update the existing page
                $updated = wp_update_post(array(
                    'ID' => $statement_page_id,
                    'post_content' => $content
                ));

                if ($updated) {
                    $message = esc_html__('Accessibility statement page has been updated successfully.', 'exa11y-expo-accessibility-kit');
                } else {
                    wp_send_json_error(array('message' => esc_html__('Failed to update the accessibility statement page.', 'exa11y-expo-accessibility-kit')));
                }
            } else {                // Create a new page
                $page_id = wp_insert_post(array(
                    'post_title'    => esc_html__('Accessibility Statement', 'exa11y-expo-accessibility-kit'),
                    'post_content'  => $content,
                    'post_status'   => 'publish',
                    'post_type'     => 'page',
                    'post_author'   => get_current_user_id(),
                    'comment_status' => 'closed'
                ));

                if ($page_id > 0 && !is_wp_error($page_id)) {
                    update_option('exa11y_accessibility_statement_page_id', $page_id);
                    $message = esc_html__('Accessibility statement page has been created successfully.', 'exa11y-expo-accessibility-kit');
                    $reload = true;
                } else {
                    wp_send_json_error(array('message' => esc_html__('Failed to create the accessibility statement page.', 'exa11y-expo-accessibility-kit')));
                }
            }

            // Log the action if debug is enabled
            if (class_exists('EXA11Y_Debug')) {
                EXA11Y_Debug::log('Accessibility statement page ' . ($page_exists ? 'updated' : 'created'));
            }

            wp_send_json_success(array(
                'message' => $message,
                'reload' => $reload
            ));
        } else {
            wp_send_json_error(array('message' => esc_html__('The function to generate the accessibility statement content is not available.', 'exa11y-expo-accessibility-kit')));
        }
    }

    /**
     * Handle reset accessibility features Ajax request
     */
    public function handle_reset_accessibility_features() {
        try {
            $this->verify_request();

            // Reset only the accessibility features to 0 (disabled)
            $accessibility_features = array(
                'font_size_scaling',
                'line_height_scaling',
                'readable_font',
                'contrast',
                'saturation',
                'brightness',
                'blue_filter',
                'gray_mode',
                'color_vision_deficiency',
                'color_theme',
                'highlight_links',
                'hide_images'
            );

            if (!$this->apply_feature_reset($accessibility_features, false)) {
                throw new Exception(esc_html__('Failed to reset accessibility features.', 'exa11y-expo-accessibility-kit'));
            }

            // Log the action if debug is enabled
            if (class_exists('EXA11Y_Debug')) {
                EXA11Y_Debug::log('Accessibility features reset to disabled');
            }

            wp_send_json_success(array('message' => esc_html__('All accessibility features have been disabled.', 'exa11y-expo-accessibility-kit')));
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    /**
     * Handle export settings Ajax request
     */
    public function handle_export_settings() {
        try {
            $this->verify_request();

            $settings = get_option('exa11y_settings', array());

            // Add metadata
            $export_data = array(
                'plugin' => 'exa11y-expo-accessibility-kit',
                'version' => EXA11Y_VERSION,
                'export_date' => current_time('Y-m-d H:i:s'),
                'site_url' => site_url(),
                'settings' => $settings
            );

            wp_send_json_success($export_data);

        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    /**
     * Handle import settings Ajax request
     */
    public function handle_import_settings() {
        try {
            $this->verify_request();

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by verify_request() above.
            if (!isset($_POST['settings'])) {
                throw new Exception(esc_html__('No settings data provided.', 'exa11y-expo-accessibility-kit'));
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified by verify_request() above; the raw JSON is decoded and then run through validate_settings() below.
            $settings_json = wp_unslash($_POST['settings']);
            $import_data   = json_decode($settings_json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(esc_html__('Invalid JSON data.', 'exa11y-expo-accessibility-kit'));
            }

            // Validate import data structure
            if (!isset($import_data['settings']) || !is_array($import_data['settings'])) {
                throw new Exception(esc_html__('Invalid settings file format.', 'exa11y-expo-accessibility-kit'));
            }

            // Backup current settings
            $current_settings = get_option('exa11y_settings', array());
            update_option('exa11y_settings_backup', $current_settings);

            // Import settings through the same validator used by the settings page,
            // so imported data gets the same clamping/whitelisting as a normal save.
            $validated_settings = EXA11Y_Admin_Core::validate_settings($import_data['settings']);
            update_option('exa11y_settings', $validated_settings);

            wp_send_json_success(array('message' => esc_html__('Settings imported successfully.', 'exa11y-expo-accessibility-kit')));

        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

    /**
     * Handle reset all settings Ajax request
     */
    public function handle_reset_all_settings() {
        try {
            $this->verify_request();

            // Reset to the canonical default settings (single source of truth).
            $this->reset_to_defaults(false);

            $this->clear_plugin_cache();

            wp_send_json_success(array('message' => esc_html__('All settings reset successfully. All accessibility features have been disabled.', 'exa11y-expo-accessibility-kit')));

        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }

}
