<?php
/**
 * Admin Fields Component
 * 
 * Handles rendering of settings fields in the admin interface.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Admin_Fields
 * 
 * Renders all the field inputs for the admin interface.
 */
class EXA11Y_Admin_Fields {


    /**
     * Render panel position field
     */
    public function render_panel_position_field() {
        $options = get_option('exa11y_settings', array());
        $position = isset($options['panel_position']) ? $options['panel_position'] : 'bottom-right';
        
        // Extract vertical and horizontal positions
        $position_parts = explode('-', $position);
        $vertical = isset($position_parts[0]) ? $position_parts[0] : 'bottom';
        $horizontal = isset($position_parts[1]) ? $position_parts[1] : 'right';
        ?>
        <div class="exa11y-position-selector">
            <div class="exa11y-position-main">
                <h4><?php esc_html_e('Vertical Position', 'exa11y-expo-accessibility-kit'); ?></h4>
                <div class="exa11y-vertical-options">
                    <label class="exa11y-vertical-option <?php echo esc_attr(($vertical === 'bottom') ? 'selected' : ''); ?>">
                        <input type="radio" name="exa11y_vertical_position" value="bottom" <?php checked($vertical, 'bottom'); ?> class="exa11y-vertical-radio">
                        <span class="dashicons dashicons-arrow-down-alt"></span>
                        <span class="position-label"><?php esc_html_e('Bottom', 'exa11y-expo-accessibility-kit'); ?></span>
                    </label>
                    <label class="exa11y-vertical-option <?php echo esc_attr(($vertical === 'center') ? 'selected' : ''); ?>">
                        <input type="radio" name="exa11y_vertical_position" value="center" <?php checked($vertical, 'center'); ?> class="exa11y-vertical-radio">
                        <span class="dashicons dashicons-minus"></span>
                        <span class="position-label"><?php esc_html_e('Center', 'exa11y-expo-accessibility-kit'); ?></span>
                    </label>
                </div>
            </div>
            
            <div class="exa11y-position-secondary bottom-options <?php echo esc_attr(($vertical === 'bottom') ? '' : 'is-hidden'); ?>">
                <h4><?php esc_html_e('Horizontal Position', 'exa11y-expo-accessibility-kit'); ?></h4>
                <div class="exa11y-horizontal-options">
                    <label class="exa11y-horizontal-option <?php echo esc_attr(($vertical === 'bottom' && $horizontal === 'left') ? 'selected' : ''); ?>">
                        <input type="radio" name="exa11y_horizontal_bottom" value="left" <?php checked($horizontal, 'left'); ?> class="exa11y-horizontal-radio">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <span class="position-label"><?php esc_html_e('Left', 'exa11y-expo-accessibility-kit'); ?></span>
                    </label>
                    <label class="exa11y-horizontal-option <?php echo esc_attr(($vertical === 'bottom' && $horizontal === 'center') ? 'selected' : ''); ?>">
                        <input type="radio" name="exa11y_horizontal_bottom" value="center" <?php checked($horizontal, 'center'); ?> class="exa11y-horizontal-radio">
                        <span class="dashicons dashicons-minus"></span>
                        <span class="position-label"><?php esc_html_e('Center', 'exa11y-expo-accessibility-kit'); ?></span>
                    </label>
                    <label class="exa11y-horizontal-option <?php echo esc_attr(($vertical === 'bottom' && $horizontal === 'right') ? 'selected' : ''); ?>">
                        <input type="radio" name="exa11y_horizontal_bottom" value="right" <?php checked($horizontal, 'right'); ?> class="exa11y-horizontal-radio">
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                        <span class="position-label"><?php esc_html_e('Right', 'exa11y-expo-accessibility-kit'); ?></span>
                    </label>
                </div>
            </div>
            
            <div class="exa11y-position-secondary center-options <?php echo esc_attr(($vertical === 'center') ? '' : 'is-hidden'); ?>">
                <h4><?php esc_html_e('Horizontal Position', 'exa11y-expo-accessibility-kit'); ?></h4>
                <div class="exa11y-horizontal-options">
                    <label class="exa11y-horizontal-option <?php echo esc_attr(($vertical === 'center' && $horizontal === 'left') ? 'selected' : ''); ?>">
                        <input type="radio" name="exa11y_horizontal_center" value="left" <?php checked($horizontal, 'left'); ?> class="exa11y-horizontal-radio">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <span class="position-label"><?php esc_html_e('Left', 'exa11y-expo-accessibility-kit'); ?></span>
                    </label>
                    <label class="exa11y-horizontal-option <?php echo esc_attr(($vertical === 'center' && $horizontal === 'right') ? 'selected' : ''); ?>">
                        <input type="radio" name="exa11y_horizontal_center" value="right" <?php checked($horizontal, 'right'); ?> class="exa11y-horizontal-radio">
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                        <span class="position-label"><?php esc_html_e('Right', 'exa11y-expo-accessibility-kit'); ?></span>
                    </label>
                </div>
            </div>
            
            <!-- Hidden field that will store the combined position value -->
            <input type="hidden" name="exa11y_settings[panel_position]" id="exa11y_combined_position" value="<?php echo esc_attr($position); ?>">
        </div>
        <p class="description"><?php esc_html_e('Choose where the accessibility icon should be displayed.', 'exa11y-expo-accessibility-kit'); ?></p>
        <?php
    }

    /**
     * Render icon hover text field
     */
    public function render_icon_hover_text_field() {
        $options = get_option('exa11y_settings', array());
        $hover_text = isset($options['icon_hover_text']) ? $options['icon_hover_text'] : esc_html__('Open Accessibility Menu', 'exa11y-expo-accessibility-kit');
        
        echo '<input type="text" name="exa11y_settings[icon_hover_text]" value="' . esc_attr($hover_text) . '" class="regular-text">';
        echo '<p class="description">' . esc_html__('Text to display when hovering over the accessibility icon. Default: "Open Accessibility Menu"', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Render accessibility statement page field
     */
    public function render_accessibility_statement_page_field() {
        $options = get_option('exa11y_settings', array());
        $statement_page_id = isset($options['accessibility_statement_page']) ? $options['accessibility_statement_page'] : 0;
        
        // If no page is set in options, try to get the auto-created page ID
        if (empty($statement_page_id)) {
            $statement_page_id = get_option('exa11y_accessibility_statement_page_id', 0);
        }
        
        // Create dropdown of pages
        wp_dropdown_pages(array(
            'name' => 'exa11y_settings[accessibility_statement_page]',
            'echo' => 1,
            'show_option_none' => esc_html__('-- Select a Page --', 'exa11y-expo-accessibility-kit'),
            'option_none_value' => '0',
            'selected' => intval($statement_page_id)
        ));
        
        echo '<p class="description">' . esc_html__('Select the page that contains your accessibility statement. This page was automatically created when the plugin was first activated. You can edit the content to reflect your organization\'s specific information.', 'exa11y-expo-accessibility-kit') . '</p>';
        
        // If we have a page ID, add an edit link
        if ($statement_page_id > 0) {
            echo '<p><a href="' . esc_url(get_edit_post_link($statement_page_id)) . '" class="button" target="_blank">' . esc_html__('Edit Accessibility Statement Page', 'exa11y-expo-accessibility-kit') . '</a></p>';
        }
    }

    /**
     * Render font size field
     */    public function render_font_size_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['font_size_scaling']) && $options['font_size_scaling'] == 1;
        
        echo '<div class="exa11y-settings-field">';
        echo '<input type="hidden" name="exa11y_settings[font_size_scaling]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[font_size_scaling]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Font Size', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Enables font size adjustment.', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '</div>';
    }

    /**
     * Render line height field
     */    public function render_line_height_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['line_height_scaling']) && $options['line_height_scaling'] == 1;
        
        echo '<div class="exa11y-settings-field">';
        echo '<input type="hidden" name="exa11y_settings[line_height_scaling]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[line_height_scaling]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Line Height', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Enables line height adjustment.', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '</div>';
    }

    /**
     * Render contrast field
     */    public function render_contrast_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['contrast']) && $options['contrast'] == 1;
        
        echo '<input type="hidden" name="exa11y_settings[contrast]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[contrast]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Contrast', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Allows adjustment of contrast.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Render saturation field
     */    public function render_saturation_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['saturation']) && $options['saturation'] == 1;
        
        echo '<input type="hidden" name="exa11y_settings[saturation]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[saturation]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Saturation', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Allows adjustment of color saturation.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Render brightness field
     */    public function render_brightness_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['brightness']) && $options['brightness'] == 1;
        
        echo '<input type="hidden" name="exa11y_settings[brightness]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[brightness]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Brightness', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Allows adjustment of brightness.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Render blue filter field
     */    public function render_blue_filter_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['blue_filter']) && $options['blue_filter'] == 1;
        
        echo '<input type="hidden" name="exa11y_settings[blue_filter]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[blue_filter]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Blue Filter', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Reduces blue light for better sleep and reduced eye strain.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Render gray mode field
     */    public function render_gray_mode_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['gray_mode']) && $options['gray_mode'] == 1;
        
        echo '<input type="hidden" name="exa11y_settings[gray_mode]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[gray_mode]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Gray Mode', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Displays content in grayscale.', 'exa11y-expo-accessibility-kit') . '</p>';
    }
    
    /**
     * Render color vision deficiency field
     */    public function render_color_vision_deficiency_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['color_vision_deficiency']) && $options['color_vision_deficiency'] == 1;
        
        echo '<input type="hidden" name="exa11y_settings[color_vision_deficiency]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[color_vision_deficiency]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Color Vision Deficiency Options', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Adds color vision deficiency adjustment options (Protanomaly/Red-weakness, Deuteranomaly/Green-weakness, Tritanomaly/Blue-weakness) to the accessibility menu.', 'exa11y-expo-accessibility-kit') . '</p>';
    }    /**
     * Render color theme field
     */
    public function render_color_theme_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['color_theme']) && $options['color_theme'] == 1;
        $default_theme = isset($options['color_theme_default']) ? $options['color_theme_default'] : 'system';
        
        echo '<div class="exa11y-color-theme-settings">';
        
        // Enable/Disable checkbox
        echo '<input type="hidden" name="exa11y_settings[color_theme]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[color_theme]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Color Theme Toggle', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Adds a color theme toggle (Light/Dark/High Contrast) to the accessibility menu.', 'exa11y-expo-accessibility-kit') . '</p>';
        
        // Default theme selection (shown when color theme is enabled)
        echo '<div id="color-theme-options" class="exa11y-nested-options' . ($checked ? '' : ' is-hidden') . '">';
        echo '<h4>' . esc_html__('Default Theme Setting', 'exa11y-expo-accessibility-kit') . '</h4>';
        echo '<p>' . esc_html__('Choose how the plugin should determine the initial theme when visitors first load your website:', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '<div class="exa11y-choice-list">';
        echo '<label class="exa11y-choice"><input type="radio" name="exa11y_settings[color_theme_default]" value="auto" ' . checked($default_theme, 'auto', false) . '> <span><strong>' . esc_html__('Auto-detect (Recommended)', 'exa11y-expo-accessibility-kit') . '</strong><small>' . esc_html__('Automatically detects your website theme and visitor system preference.', 'exa11y-expo-accessibility-kit') . '</small></span></label>';
        echo '<label class="exa11y-choice"><input type="radio" name="exa11y_settings[color_theme_default]" value="light" ' . checked($default_theme, 'light', false) . '> <span><strong>' . esc_html__('Always start with Light Theme', 'exa11y-expo-accessibility-kit') . '</strong><small>' . esc_html__('Starts with light theme regardless of website or system settings.', 'exa11y-expo-accessibility-kit') . '</small></span></label>';
        echo '<label class="exa11y-choice"><input type="radio" name="exa11y_settings[color_theme_default]" value="dark" ' . checked($default_theme, 'dark', false) . '> <span><strong>' . esc_html__('Always start with Dark Theme', 'exa11y-expo-accessibility-kit') . '</strong><small>' . esc_html__('Starts with dark theme regardless of website or system settings.', 'exa11y-expo-accessibility-kit') . '</small></span></label>';
        echo '<label class="exa11y-choice"><input type="radio" name="exa11y_settings[color_theme_default]" value="system" ' . checked($default_theme, 'system', false) . '> <span><strong>' . esc_html__('Follow System Preference', 'exa11y-expo-accessibility-kit') . '</strong><small>' . esc_html__('Uses prefers-color-scheme from the visitor operating system.', 'exa11y-expo-accessibility-kit') . '</small></span></label>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render active button color field
     */    public function render_active_button_color_field() {
        $options = get_option('exa11y_settings', array());
        $active_color = isset($options['active_button_color']) ? $options['active_button_color'] : '#2AACE2';
        
        echo '<input type="text" name="exa11y_settings[active_button_color]" value="' . esc_attr($active_color) . '" class="color-picker" data-default-color="#2AACE2">';
        echo '<p class="description">' . esc_html__('Choose the color for active buttons in the accessibility menu. Default is blue (#2AACE2).', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Render hover button color field
     */    public function render_hover_button_color_field() {
        $options = get_option('exa11y_settings', array());
        $hover_color = isset($options['hover_button_color']) ? $options['hover_button_color'] : '#1e8bb8';
        
        echo '<input type="text" name="exa11y_settings[hover_button_color]" value="' . esc_attr($hover_color) . '" class="color-picker" data-default-color="#1e8bb8">';
        echo '<p class="description">' . esc_html__('Choose the color for buttons when hovered in the accessibility menu. Default is darker blue (#1e8bb8).', 'exa11y-expo-accessibility-kit') . '</p>';
    }


    /**
     * Render reset settings field
     */
    public function render_reset_settings_field() {
        echo '<button type="button" id="exa11y-reset-all-settings" class="button button-secondary">';
        echo '<span class="dashicons dashicons-admin-generic exa11y-button-icon"></span>';
        echo esc_html__('Reset All Settings', 'exa11y-expo-accessibility-kit');
        echo '</button>';
        echo '<div id="exa11y-reset-result" class="exa11y-field-result"></div>';
        echo '<p class="description">' . esc_html__('This will reset ALL plugin settings to their default values and disable all accessibility features.', 'exa11y-expo-accessibility-kit') . '</p>';
        
        // The JavaScript for this button is handled by the tools page script
    }
    
    /**
     * Render clear cache field
     */
    public function render_clear_cache_field() {
        echo '<button type="button" id="exa11y-clear-cache" class="button button-secondary">';
        echo '<span class="dashicons dashicons-trash exa11y-button-icon"></span>';
        echo esc_html__('Clear Plugin Cache', 'exa11y-expo-accessibility-kit');
        echo '</button>';
        echo '<p class="description">' . esc_html__('This will clear all cached data and local storage values created by the plugin.', 'exa11y-expo-accessibility-kit') . '</p>';

        // The click handler lives in admin/js/admin.js; its exa11y_cache data
        // is localized alongside the script in class-admin.php.
    }

    /**
     * Render highlight links field
     */    public function render_highlight_links_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['highlight_links']) && $options['highlight_links'] == 1;
        
        echo '<input type="hidden" name="exa11y_settings[highlight_links]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[highlight_links]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Link Highlighting', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Adds a button to highlight all links on the page with yellow background, black borders, and larger text.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Render hide images field
     */    public function render_hide_images_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['hide_images']) && $options['hide_images'] == 1;
        
        echo '<input type="hidden" name="exa11y_settings[hide_images]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[hide_images]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Hide Images and Videos', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Adds a button to hide all images and videos on the page for distraction-free reading.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Render readable font field
     */    public function render_readable_font_field() {
        $options = get_option('exa11y_settings', array());
        $checked = isset($options['readable_font']) && $options['readable_font'] == 1;
        
        echo '<div class="exa11y-settings-field">';
        echo '<input type="hidden" name="exa11y_settings[readable_font]" value="0">';
        echo '<label><input type="checkbox" name="exa11y_settings[readable_font]" value="1" ' . checked($checked, true, false) . '> ' . esc_html__('Enable Readable Font', 'exa11y-expo-accessibility-kit') . '</label>';
        echo '<p class="description">' . esc_html__('Replaces all fonts with a standard readable font that is easier for users with dyslexia or visual impairments.', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '</div>';
    }

    /**
     * Render checkbox field
     * 
     * @param array $args Arguments for the field
     */
    public function render_checkbox_field($args) {
        $options = get_option('exa11y_settings', array());
        $id = isset($args['id']) ? $args['id'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $checked = isset($options[$id]) ? checked(1, $options[$id], false) : '';
        
        if (!empty($label)) {
            echo '<label class="exa11y-choice" for="exa11y_settings[' . esc_attr($id) . ']">';
            echo '<input type="checkbox" id="exa11y_settings[' . esc_attr($id) . ']" name="exa11y_settings[' . esc_attr($id) . ']" value="1" ' . esc_attr($checked) . '>';
            echo '<span>' . esc_html($label) . '</span>';
            echo '</label>';
        } else {
            echo '<input type="checkbox" id="exa11y_settings[' . esc_attr($id) . ']" name="exa11y_settings[' . esc_attr($id) . ']" value="1" ' . esc_attr($checked) . '>';
        }
        
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    /**
     * Render Regenerate Accessibility Statement field
     */
    public function render_regenerate_accessibility_statement_field() {
        echo '<button type="button" id="regenerate-accessibility-statement" class="button button-secondary">' . esc_html__('Regenerate Accessibility Statement', 'exa11y-expo-accessibility-kit') . '</button>';
        echo '<p class="description">' . esc_html__('Click to regenerate your accessibility statement based on current settings.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    

    /**
     * Render generic text field
     */
    public function render_text_field($args) {
        $options = get_option('exa11y_settings', array());
        $field_id = isset($args['id']) ? $args['id'] : '';
        $value = isset($options[$field_id]) ? $options[$field_id] : (isset($args['default']) ? $args['default'] : '');
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        $disabled = isset($args['disabled']) ? $args['disabled'] : false;

        echo '<input type="text" id="' . esc_attr($field_id) . '" name="exa11y_settings[' . esc_attr($field_id) . ']" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '"' . esc_attr($disabled ? ' disabled' : '') . ' class="regular-text">';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    /**
     * Render generic textarea field
     */
    public function render_textarea_field($args) {
        $options = get_option('exa11y_settings', array());
        $field_id = isset($args['id']) ? $args['id'] : '';
        $value = isset($options[$field_id]) ? $options[$field_id] : (isset($args['default']) ? $args['default'] : '');
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        $disabled = isset($args['disabled']) ? $args['disabled'] : false;
        $rows = isset($args['rows']) ? $args['rows'] : 5;

        echo '<textarea id="' . esc_attr($field_id) . '" name="exa11y_settings[' . esc_attr($field_id) . ']" placeholder="' . esc_attr($placeholder) . '" rows="' . esc_attr($rows) . '"' . esc_attr($disabled ? ' disabled' : '') . ' class="large-text">' . esc_textarea($value) . '</textarea>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    /**
     * Render generic select field
     */
    public function render_select_field($args) {
        $options = get_option('exa11y_settings', array());
        $field_id = isset($args['id']) ? $args['id'] : '';
        $value = isset($options[$field_id]) ? $options[$field_id] : (isset($args['default']) ? $args['default'] : '');
        $disabled = isset($args['disabled']) ? $args['disabled'] : false;
        $select_options = isset($args['options']) ? $args['options'] : array();

        echo '<select id="' . esc_attr($field_id) . '" name="exa11y_settings[' . esc_attr($field_id) . ']"' . esc_attr($disabled ? ' disabled' : '') . '>';
        
        foreach ($select_options as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '"' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
        }
        
        echo '</select>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    /**
     * Render date field
     */
    public function render_date_field($args) {
        $options = get_option('exa11y_settings', array());
        $field_id = isset($args['id']) ? $args['id'] : '';
        $value = isset($options[$field_id]) ? $options[$field_id] : (isset($args['default']) ? $args['default'] : '');
        $disabled = isset($args['disabled']) ? $args['disabled'] : false;

        echo '<input type="date" id="' . esc_attr($field_id) . '" name="exa11y_settings[' . esc_attr($field_id) . ']" value="' . esc_attr($value) . '"' . esc_attr($disabled ? ' disabled' : '') . '>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
        }
    }

    /**
     * Render Reset Accessibility Features field
     */
    public function render_reset_accessibility_features_field() {
        echo '<button type="button" id="exa11y-reset-accessibility-features" class="button button-secondary">' . esc_html__('Reset All Accessibility Features', 'exa11y-expo-accessibility-kit') . '</button>';
        echo '<div id="exa11y-reset-accessibility-result" class="exa11y-field-result"></div>';
        echo '<p class="description">' . esc_html__('Reset all accessibility features to their default disabled state for all users.', 'exa11y-expo-accessibility-kit') . '</p>';

        // The JavaScript for this button is handled by the tools page script
    }

    /**
     * Render Accessibility Statement Preview field
     */
    public function render_accessibility_statement_preview_field() {
        echo '<div class="accessibility-statement-preview">';
        echo '<h4>' . esc_html__('Statement Preview', 'exa11y-expo-accessibility-kit') . '</h4>';
        echo '<div class="statement-content exa11y-statement-box">';
        
        // Render the statement the plugin would generate from the current settings.
        $statement_content = function_exists('exa11y_get_accessibility_statement_content')
            ? exa11y_get_accessibility_statement_content()
            : esc_html__('Your accessibility statement will appear here based on your settings.', 'exa11y-expo-accessibility-kit');
        echo wp_kses_post($statement_content);
        
        echo '</div>';
        echo '</div>';
        
        echo '<p class="description">' . esc_html__('This is a preview of how your accessibility statement will appear on the frontend.', 'exa11y-expo-accessibility-kit') . '</p>';
    }


    /**
     * Render Export Import Settings field
     */
    public function render_export_import_settings_field() {
        ?>
        <div class="exa11y-field-container">
            <div class="exa11y-import-export-container">
                <div class="exa11y-export-section">
                    <h4><?php esc_html_e('📤 Export Settings', 'exa11y-expo-accessibility-kit'); ?></h4>
                    <p class="description"><?php esc_html_e('Export all current plugin settings. This creates a backup that can be imported on another site.', 'exa11y-expo-accessibility-kit'); ?></p>
                    <button type="button" id="exa11y-export-settings" class="button">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Export Settings', 'exa11y-expo-accessibility-kit'); ?>
                    </button>
                    <div id="exa11y-export-result" class="exa11y-field-response"></div>
                </div>
                
                <div class="exa11y-import-section">
                    <h4><?php esc_html_e('📥 Import Settings', 'exa11y-expo-accessibility-kit'); ?></h4>
                    <p class="description"><?php esc_html_e('Import settings from a previously exported configuration file. This will overwrite current settings.', 'exa11y-expo-accessibility-kit'); ?></p>
                    <div class="exa11y-field-file-input">
                        <input type="file" id="exa11y-import-file" accept=".json">
                    </div>
                    <button type="button" id="exa11y-import-settings" class="button" disabled>
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e('Import Settings', 'exa11y-expo-accessibility-kit'); ?>
                    </button>
                    <div id="exa11y-import-result" class="exa11y-field-response"></div>
                </div>
            </div>
            <p class="description exa11y-separated-description">
                <strong><?php esc_html_e('Use export/import to backup settings or transfer configuration between websites. All accessibility settings are included.', 'exa11y-expo-accessibility-kit'); ?></strong>
            </p>
        </div>
        <?php
    }



    /**
     * Render menu font size field
     */
    public function render_menu_font_size_field() {
        $options = get_option('exa11y_settings', array());
        $menu_font_size = isset($options['menu_font_size']) ? $options['menu_font_size'] : array(
            'header' => '18', 
            'subheader' => '16', 
            'buttons' => '14', 
            'text' => '14', 
            'elements' => '14'
        );
        
        echo '<div class="exa11y-settings-panel font-size-settings">';
        echo '<p class="exa11y-settings-description">' . esc_html__('Customize font sizes for different menu elements:', 'exa11y-expo-accessibility-kit') . '</p>';
        
        echo '<div class="exa11y-font-size-grid">';
        
        // Menu Headers
        echo '<div class="exa11y-font-size-item">';
        echo '<label class="exa11y-font-size-label" for="menu-header-size">' . esc_html__('Menu Headers', 'exa11y-expo-accessibility-kit');
        echo '<div class="exa11y-input-group">';
        echo '<input type="number" id="menu-header-size" name="exa11y_settings[menu_font_size][header]" value="' . esc_attr($menu_font_size['header']) . '" min="10" max="30" step="1" />';
        echo '<span class="exa11y-unit">px</span>';
        echo '</div></label>';
        echo '<p class="exa11y-field-hint">' . esc_html__('Font size for menu section headers and titles', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '</div>';
        
        // Menu Subheaders
        echo '<div class="exa11y-font-size-item">';
        echo '<label class="exa11y-font-size-label" for="menu-subheader-size">' . esc_html__('Menu Subheaders', 'exa11y-expo-accessibility-kit');
        echo '<div class="exa11y-input-group">';
        echo '<input type="number" id="menu-subheader-size" name="exa11y_settings[menu_font_size][subheader]" value="' . esc_attr($menu_font_size['subheader']) . '" min="10" max="30" step="1" />';
        echo '<span class="exa11y-unit">px</span>';
        echo '</div></label>';
        echo '<p class="exa11y-field-hint">' . esc_html__('Font size for subheadings and section titles', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '</div>';
        
        // Menu Buttons
        echo '<div class="exa11y-font-size-item">';
        echo '<label class="exa11y-font-size-label" for="menu-buttons-size">' . esc_html__('Menu Buttons', 'exa11y-expo-accessibility-kit');
        echo '<div class="exa11y-input-group">';
        echo '<input type="number" id="menu-buttons-size" name="exa11y_settings[menu_font_size][buttons]" value="' . esc_attr($menu_font_size['buttons']) . '" min="10" max="30" step="1" />';
        echo '<span class="exa11y-unit">px</span>';
        echo '</div></label>';
        echo '<p class="exa11y-field-hint">' . esc_html__('Font size for buttons in the menu', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '</div>';
        
        // Menu Text
        echo '<div class="exa11y-font-size-item">';
        echo '<label class="exa11y-font-size-label" for="menu-text-size">' . esc_html__('Menu Text', 'exa11y-expo-accessibility-kit');
        echo '<div class="exa11y-input-group">';
        echo '<input type="number" id="menu-text-size" name="exa11y_settings[menu_font_size][text]" value="' . esc_attr($menu_font_size['text']) . '" min="10" max="30" step="1" />';
        echo '<span class="exa11y-unit">px</span>';
        echo '</div></label>';
        echo '<p class="exa11y-field-hint">' . esc_html__('Font size for text and descriptions', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '</div>';
        
        // Menu Elements
        echo '<div class="exa11y-font-size-item">';
        echo '<label class="exa11y-font-size-label" for="menu-elements-size">' . esc_html__('Menu Elements', 'exa11y-expo-accessibility-kit');
        echo '<div class="exa11y-input-group">';
        echo '<input type="number" id="menu-elements-size" name="exa11y_settings[menu_font_size][elements]" value="' . esc_attr($menu_font_size['elements']) . '" min="10" max="30" step="1" />';
        echo '<span class="exa11y-unit">px</span>';
        echo '</div></label>';
        echo '<p class="exa11y-field-hint">' . esc_html__('Font size for other UI elements', 'exa11y-expo-accessibility-kit') . '</p>';
        echo '</div>';
        
        echo '</div>'; // Close font-size-grid
        echo '</div>'; // Close settings-panel
    }

}
