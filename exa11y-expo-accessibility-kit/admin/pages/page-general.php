<?php
/**
 * General Settings Page
 * 
 * Handles the general settings page functionality.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Page_General
 * 
 * Manages the general settings page.
 */
class EXA11Y_Page_General {
    /**
     * Admin Fields instance
     * 
     * @var EXA11Y_Admin_Fields
     */
    private static $fields;
    
    /**
     * Get the global admin fields instance
     */
    private static function get_fields_instance() {
        global $exa11y_admin;
        if (isset($exa11y_admin) && method_exists($exa11y_admin, 'get_fields')) {
            return $exa11y_admin->get_fields();
        }
        
        // Fallback: create instance if needed
        if (!isset(self::$fields)) {
            require_once(EXA11Y_PATH . 'admin/components/admin-fields.php');
            self::$fields = new EXA11Y_Admin_Fields();
        }
        return self::$fields;
    }/**
     * Display the page content
     */
    public static function display_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'exa11y-expo-accessibility-kit'));
        }
        
        // Get fields instance from global admin
        self::$fields = self::get_fields_instance();
        EXA11Y_Admin_Page::header(
            __('General Settings', 'exa11y-expo-accessibility-kit'),
            __('Configure the basic settings for the EXA11Y plugin, including appearance and positioning.', 'exa11y-expo-accessibility-kit')
        );
        ?>
            <form action="options.php" method="post" id="exa11y-settings-form">              
                <?php
                // CRITICAL: Correct settings field call to match registered options group
                settings_fields('exa11y_options');
                // Declare which checkboxes this page owns so validate_settings()
                // only zeros these, leaving other pages' settings intact.
                ?>
                <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="hide_on_mobile">
                <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="keyboard_shortcuts">
                <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="skip_links">
                <?php
                EXA11Y_Admin_Page::category_group_open(__('General Behavior', 'exa11y-expo-accessibility-kit'), 'dashicons-admin-generic');
                echo '<div class="exa11y-section exa11y-spacious-controls">';
                do_settings_sections('exa11y_general_behavior');
                echo '</div>';
                EXA11Y_Admin_Page::category_group_close();

                EXA11Y_Admin_Page::category_group_open(__('Appearance', 'exa11y-expo-accessibility-kit'), 'dashicons-admin-appearance');
                echo '<div class="exa11y-section exa11y-spacious-controls">';
                do_settings_sections('exa11y_appearance_basic');
                echo '</div>';
                EXA11Y_Admin_Page::category_group_close();

                // Add save changes button
                submit_button();
                ?>
            </form>
            
            <?php EXA11Y_Admin_Page::footer(); ?>
        <?php
    }
    
}

