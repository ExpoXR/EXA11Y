<?php
/**
 * Accessibility Features Page
 *
 * One screen for all accessibility feature configuration: the former
 * General Accessibility, Font Settings and Color Settings pages now render
 * as tabs here. Each tab keeps its own options.php form (and its own
 * submitted_checkboxes[] declarations) so saving one tab never clears
 * checkbox settings owned by another.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Page_Accessibility_General
 *
 * Manages the accessibility features page (General / Font / Color tabs).
 */
class EXA11Y_Page_Accessibility_General {
    /**
     * Display the page content
     */
    public static function display_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'exa11y-expo-accessibility-kit'));
        }
        EXA11Y_Admin_Page::header(
            __('Accessibility Features', 'exa11y-expo-accessibility-kit'),
            __('Enable and configure the accessibility features offered on your website: general behavior, fonts, and colors.', 'exa11y-expo-accessibility-kit')
        );
        ?>
            <div class="exa11y-tabs">
                <div class="exa11y-tabs-navigation" role="tablist">
                    <button type="button" class="exa11y-tab-button active" id="tab-btn-features" role="tab" aria-controls="tab-features" aria-selected="true">
                        <span class="dashicons dashicons-universal-access-alt"></span> <?php esc_html_e('General', 'exa11y-expo-accessibility-kit'); ?>
                    </button>
                    <button type="button" class="exa11y-tab-button" id="tab-btn-font" role="tab" aria-controls="tab-font" aria-selected="false">
                        <span class="dashicons dashicons-editor-textcolor"></span> <?php esc_html_e('Fonts', 'exa11y-expo-accessibility-kit'); ?>
                    </button>
                    <button type="button" class="exa11y-tab-button" id="tab-btn-color" role="tab" aria-controls="tab-color" aria-selected="false">
                        <span class="dashicons dashicons-art"></span> <?php esc_html_e('Colors', 'exa11y-expo-accessibility-kit'); ?>
                    </button>
                </div>

                <div class="exa11y-tabs-content">
                    <div class="exa11y-tab-panel active" id="tab-features" role="tabpanel" aria-labelledby="tab-btn-features">
                        <form action="options.php" method="post">
                            <?php
                            settings_fields('exa11y_options');
                            // Declare which checkboxes this form owns so validate_settings()
                            // only zeros these, leaving the other tabs' settings intact.
                            ?>
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="highlight_links">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="hide_images">
                            <div class="exa11y-section exa11y-two-column-section">
                                <h2><?php esc_html_e('Accessibility Features', 'exa11y-expo-accessibility-kit'); ?></h2>
                                <?php do_settings_sections('exa11y_accessibility'); ?>
                            </div>

                            <?php submit_button(); ?>
                        </form>
                    </div>

                    <div class="exa11y-tab-panel" id="tab-font" role="tabpanel" aria-labelledby="tab-btn-font">
                        <form action="options.php" method="post">
                            <?php settings_fields('exa11y_options'); ?>
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="font_size_scaling">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="line_height_scaling">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="readable_font">
                            <div class="exa11y-section">
                                <h2><?php esc_html_e('Font Configuration', 'exa11y-expo-accessibility-kit'); ?></h2>
                                <?php do_settings_sections('exa11y_font_settings'); ?>
                            </div>

                            <?php submit_button(); ?>
                        </form>
                    </div>

                    <div class="exa11y-tab-panel" id="tab-color" role="tabpanel" aria-labelledby="tab-btn-color">
                        <form action="options.php" method="post">
                            <?php settings_fields('exa11y_options'); ?>
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="contrast">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="saturation">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="brightness">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="blue_filter">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="gray_mode">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="color_vision_deficiency">
                            <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="color_theme">
                            <div class="exa11y-section">
                                <h2><?php esc_html_e('Color Configuration', 'exa11y-expo-accessibility-kit'); ?></h2>
                                <?php do_settings_sections('exa11y_color_settings'); ?>
                            </div>

                            <?php submit_button(); ?>
                        </form>
                    </div>
                </div>
            </div>

            <?php EXA11Y_Admin_Page::footer(); ?>
        <?php
    }

}
