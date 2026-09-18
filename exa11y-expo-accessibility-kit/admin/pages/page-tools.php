<?php
/**
 * Settings Page
 * 
 * Handles the settings page functionality.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Page_Tools
 * 
 * Manages the settings page.
 */
class EXA11Y_Page_Tools {
    /**
     * Enqueue the Tools page's JS and localize its strings/nonce.
     *
     * Registered as a static file-scope hook (below): this class is only ever
     * used statically, via display_page().
     *
     * @param string $hook The current admin page hook suffix.
     */
    public static function enqueue_scripts($hook) {
        if (strpos($hook, 'exa11y-tools') === false) {
            return;
        }

        wp_enqueue_script(
            'exa11y-tools-page',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/js/tools-page.js',
            array('jquery'),
            EXA11Y_VERSION,
            true
        );

        wp_localize_script('exa11y-tools-page', 'exa11yTools', array(
            'nonce' => wp_create_nonce('exa11y_admin_ajax'),
            'i18n'  => array(
                'exporting'                        => esc_html__('Exporting...', 'exa11y-expo-accessibility-kit'),
                'exportSettingsLabel'              => esc_html__('Export Settings', 'exa11y-expo-accessibility-kit'),
                'exportSuccess'                    => esc_html__('Settings exported successfully!', 'exa11y-expo-accessibility-kit'),
                'exportFailed'                     => esc_html__('Export failed', 'exa11y-expo-accessibility-kit'),
                'selectFileToImport'               => esc_html__('Please select a file to import', 'exa11y-expo-accessibility-kit'),
                'confirmOverwrite'                 => esc_html__('This will overwrite all current settings. Are you sure?', 'exa11y-expo-accessibility-kit'),
                'importing'                        => esc_html__('Importing...', 'exa11y-expo-accessibility-kit'),
                'importSuccess'                    => esc_html__('Settings imported successfully! Page will reload...', 'exa11y-expo-accessibility-kit'),
                'importFailed'                     => esc_html__('Import failed', 'exa11y-expo-accessibility-kit'),
                'invalidJson'                      => esc_html__('Invalid JSON file', 'exa11y-expo-accessibility-kit'),
                'importSettingsLabel'              => esc_html__('Import Settings', 'exa11y-expo-accessibility-kit'),
                'confirmResetAll'                  => esc_html__('This will reset ALL settings to defaults. This cannot be undone! Are you sure?', 'exa11y-expo-accessibility-kit'),
                'resetting'                        => esc_html__('Resetting...', 'exa11y-expo-accessibility-kit'),
                'resetAllSuccess'                  => esc_html__('All settings reset successfully! Page will reload...', 'exa11y-expo-accessibility-kit'),
                'resetFailed'                      => esc_html__('Reset failed', 'exa11y-expo-accessibility-kit'),
                'resetAllSettingsLabel'            => esc_html__('Reset All Settings', 'exa11y-expo-accessibility-kit'),
                'confirmResetAccessibility'        => esc_html__('This will reset all accessibility features to defaults. This cannot be undone! Are you sure?', 'exa11y-expo-accessibility-kit'),
                'resetAccessibilitySuccess'        => esc_html__('Accessibility features reset successfully! Page will reload...', 'exa11y-expo-accessibility-kit'),
                'resetAccessibilityFeaturesLabel'  => esc_html__('Reset Accessibility Features', 'exa11y-expo-accessibility-kit'),
            ),
        ));
    }
    
    /**
     * Display the page content
     */
    public static function display_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'exa11y-expo-accessibility-kit'));
        }
        
        EXA11Y_Admin_Page::header(
            __('Tools', 'exa11y-expo-accessibility-kit'),
            __('Maintenance actions, plus settings import and export.', 'exa11y-expo-accessibility-kit')
        );
        ?>

            <?php EXA11Y_Admin_Page::category_group_open(__('Maintenance', 'exa11y-expo-accessibility-kit'), 'dashicons-admin-generic'); ?>
            <div class="exa11y-section">
                <?php do_settings_sections('exa11y_maintenance'); ?>
            </div>
            <?php EXA11Y_Admin_Page::category_group_close(); ?>

            <?php EXA11Y_Admin_Page::category_group_open(__('Import & Export', 'exa11y-expo-accessibility-kit'), 'dashicons-database-export'); ?>
            <div class="exa11y-section">
                <?php do_settings_sections('exa11y_import_export'); ?>
            </div>
            <?php EXA11Y_Admin_Page::category_group_close(); ?>

        <?php EXA11Y_Admin_Page::footer(); ?>
        <?php
    }
}

add_action('admin_enqueue_scripts', array('EXA11Y_Page_Tools', 'enqueue_scripts'));
