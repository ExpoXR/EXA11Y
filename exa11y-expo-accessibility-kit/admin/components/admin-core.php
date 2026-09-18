<?php
/**
 * Admin Core Component
 * 
 * Core functionality for the admin area with initialization and main hooks.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Admin_Core
 * 
 * Handles core admin functionality and initializes other admin components.
 */
class EXA11Y_Admin_Core {
      /**
     * Validate settings
     * 
     * This function handles all submitted form settings and properly 
     * processes checkbox fields, including those that are unchecked
     * (which don't appear in the $_POST data).
     * 
     * @param array $input The submitted form data
     * @return array Validated and sanitized settings
     */    public static function validate_settings($input) {
        // Get the existing settings to preserve non-submitted values
        $current_options = get_option('exa11y_settings', array());
        $validated = $current_options;

        // If input is unexpectedly empty during a settings save, fall back to the
        // existing options so a malformed submission cannot wipe the configuration.
        if (empty($input)) {
            return $validated;
        }

        // Validate Menu Font Size settings
        if (isset($input['menu_font_size'])) {            $menu_font_size = $input['menu_font_size'];
            $validated['menu_font_size'] = array(
                'header' => isset($menu_font_size['header']) ? absint($menu_font_size['header']) : 18,
                'subheader' => isset($menu_font_size['subheader']) ? absint($menu_font_size['subheader']) : 16,
                'buttons' => isset($menu_font_size['buttons']) ? absint($menu_font_size['buttons']) : 14,
                'text' => isset($menu_font_size['text']) ? absint($menu_font_size['text']) : 14,
                'elements' => isset($menu_font_size['elements']) ? absint($menu_font_size['elements']) : 14
            );
            
            // Ensure values are within acceptable range
            foreach ($validated['menu_font_size'] as $key => $value) {
                if ($value < 10) $validated['menu_font_size'][$key] = 10;
                if ($value > 30) $validated['menu_font_size'][$key] = 30;
            }
        }
        
        // Panel position
        if(isset($input['panel_position'])) {
            $valid_positions = array('center-left', 'center-right', 'bottom-left', 'bottom-center', 'bottom-right');
            $validated['panel_position'] = in_array($input['panel_position'], $valid_positions) ? $input['panel_position'] : 'bottom-right';
        }
        
        // Active button color
        if(isset($input['active_button_color'])) {
            $active_color = sanitize_hex_color($input['active_button_color']);
            $validated['active_button_color'] = !empty($active_color) ? $active_color : '#2AACE2';
        }
        
        // Hover button color
        if(isset($input['hover_button_color'])) {
            $hover_color = sanitize_hex_color($input['hover_button_color']);
            $validated['hover_button_color'] = !empty($hover_color) ? $hover_color : '#1e8bb8';
        }

        // Icon hover text
        if(isset($input['icon_hover_text'])){
            $validated['icon_hover_text'] = sanitize_text_field($input['icon_hover_text']);
        } else {
            // Ensure a default if it's somehow cleared or not submitted, though text fields usually submit empty.
            if (!isset($validated['icon_hover_text'])) {
                 $validated['icon_hover_text'] = esc_html__('Open Accessibility Menu', 'exa11y-expo-accessibility-kit');
            }
        }        // Define all checkbox options for proper handling
        $checkbox_options = array(
            'highlight_links',
            'hide_images',
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
            'accessibility_statement_enabled',
            'hide_on_mobile',
            'keyboard_shortcuts',
            'skip_links',
        );

        // Determine which checkboxes were actually rendered on the submitted page.
        // Each admin sub-page must include hidden inputs like:
        //   <input type="hidden" name="exa11y_settings[submitted_checkboxes][]" value="font_size_scaling">
        // for every checkbox it renders. Checkboxes NOT in this list are skipped
        // so that saving one page does not silently zero settings from other pages.
        $active_checkboxes = isset($input['submitted_checkboxes']) && is_array($input['submitted_checkboxes'])
            ? array_map('sanitize_key', $input['submitted_checkboxes'])
            : null;

        // Process all checkbox options
        foreach ($checkbox_options as $option) {
            // If the submitting page declared which checkboxes it contains, only
            // process those. Checkboxes from other pages are left unchanged.
            if ($active_checkboxes !== null && !in_array($option, $active_checkboxes, true)) {
                continue;
            }
            if (isset($input[$option])) {
                // Convert to integer and ensure it's either 0 or 1
                $validated[$option] = (int) $input[$option] === 1 ? 1 : 0;
            } else {
                $validated[$option] = 0;
            }
        }

        // Strip the helper key so it is never persisted.
        unset($validated['submitted_checkboxes']);

        // Color theme default mode
        if(isset($input['color_theme_default'])) {
            $valid_defaults = array('auto', 'light', 'dark', 'system');
            $validated['color_theme_default'] = in_array($input['color_theme_default'], $valid_defaults) ? $input['color_theme_default'] : 'system';
        }
        
        // Accessibility statement page selector
        if (isset($input['accessibility_statement_page'])) {
            $validated['accessibility_statement_page'] = absint($input['accessibility_statement_page']);
        }

        // Validate Accessibility Statement content fields.
        if (isset($input['accessibility_statement_org_name'])) {
            $validated['accessibility_statement_org_name'] = sanitize_text_field($input['accessibility_statement_org_name']);
        }
        if (isset($input['accessibility_statement_contact_info'])) {
            $validated['accessibility_statement_contact_info'] = sanitize_textarea_field($input['accessibility_statement_contact_info']);
        }
        if (isset($input['accessibility_statement_org_email'])) {
            $validated['accessibility_statement_org_email'] = sanitize_email($input['accessibility_statement_org_email']);
        }
        if (isset($input['accessibility_statement_org_phone'])) {
            $validated['accessibility_statement_org_phone'] = sanitize_text_field($input['accessibility_statement_org_phone']);
        }
        if (isset($input['accessibility_statement_enforcement'])) {
            $validated['accessibility_statement_enforcement'] = wp_kses_post($input['accessibility_statement_enforcement']);
        }
        if (isset($input['accessibility_statement_compliance_level'])) {
            $valid_levels = array('A', 'AA', 'AAA');
            $validated['accessibility_statement_compliance_level'] = in_array($input['accessibility_statement_compliance_level'], $valid_levels, true)
                ? $input['accessibility_statement_compliance_level']
                : 'AA';
        }
        if (isset($input['accessibility_statement_additional_measures'])) {
            $validated['accessibility_statement_additional_measures'] = wp_kses_post($input['accessibility_statement_additional_measures']);
        }
        if (isset($input['accessibility_statement_last_reviewed'])) {
            $validated['accessibility_statement_last_reviewed'] = sanitize_text_field($input['accessibility_statement_last_reviewed']);
        }

        // Apply filter to allow extensions to validate their settings
        $validated = apply_filters('exa11y_validate_settings', $validated, $input);

        return $validated;
    }
}
