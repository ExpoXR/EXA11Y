<?php
/**
 * Accessibility Statement Page
 * 
 * Handles the accessibility statement page functionality.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Page_Accessibility_Statement
 * 
 * Manages the accessibility statement page.
 */
class EXA11Y_Page_Accessibility_Statement {
    /**
     * Display the page content
     */
    public static function display_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'exa11y-expo-accessibility-kit'));
        }
        EXA11Y_Admin_Page::header(
            __('Accessibility Statement', 'exa11y-expo-accessibility-kit'),
            __('Create and manage your website\'s accessibility statement to comply with accessibility regulations and inform users about your accessibility efforts.', 'exa11y-expo-accessibility-kit')
        );
        ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('exa11y_options');
                // Declare which checkboxes this page owns so validate_settings()
                // only zeros these, leaving other pages' settings intact.
                ?>
                <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="accessibility_statement_enabled">
                <?php
                EXA11Y_Admin_Page::category_group_open(__('Statement Setup', 'exa11y-expo-accessibility-kit'), 'dashicons-media-text');
                echo '<div class="exa11y-section exa11y-single-column-section">';
                do_settings_sections('exa11y_accessibility_statement');
                echo '</div>';
                EXA11Y_Admin_Page::category_group_close();
                
                submit_button();
                ?>
            </form>
            
            <?php EXA11Y_Admin_Page::footer(); ?>
        <?php
    }
    
}
