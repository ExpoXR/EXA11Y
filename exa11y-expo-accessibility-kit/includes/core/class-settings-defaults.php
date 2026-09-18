<?php
/**
 * Canonical default settings source.
 *
 * Single source of truth for the plugin's default option values. Activation,
 * the settings validation fallback, the reset handlers and import/export all
 * consume this so defaults never drift between locations.
 *
 * @package EXA11Y
 * @subpackage Core
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('exa11y_get_default_settings')) {
    /**
     * Return the canonical default settings array.
     *
     * Every key that the admin UI renders and the front end reads has a default
     * here, so $settings[$key] access is always defined after activation.
     *
     * @return array Default values for the `exa11y_settings` option.
     */
    function exa11y_get_default_settings() {
        $defaults = array(
            // Core settings.
            'panel_position'        => 'bottom-right',
            'icon_hover_text'       => __('Open Accessibility Menu', 'exa11y-expo-accessibility-kit'),
            'hide_on_mobile'        => 0,
            'keyboard_shortcuts'    => 0,
            'skip_links'            => 0,

            // Appearance settings. Per-element menu font sizes (px). The admin UI
            // and front end both treat this as a keyed array, so the default must
            // match that shape rather than a single scalar.
            'menu_font_size'        => array(
                'header'    => '18',
                'subheader' => '16',
                'buttons'   => '14',
                'text'      => '14',
                'elements'  => '14',
            ),
            'active_button_color'   => '#2AACE2',
            'hover_button_color'    => '#1e8bb8',

            // Accessibility features (disabled by default).
            'font_size_scaling'         => 0,
            'line_height_scaling'       => 0,
            'readable_font'             => 0,
            'contrast'                  => 0,
            'saturation'                => 0,
            'brightness'                => 0,
            'blue_filter'               => 0,
            'gray_mode'                 => 0,
            'color_vision_deficiency'   => 0,
            'color_theme'               => 0,
            'highlight_links'           => 0,
            'hide_images'               => 0,
            'accessibility_statement_enabled' => 0,
            'accessibility_statement_page'  => 0,

            // Accessibility statement content.
            'accessibility_statement_org_name'             => '',
            'accessibility_statement_contact_info'         => '',
            'accessibility_statement_org_email'            => '',
            'accessibility_statement_org_phone'            => '',
            'accessibility_statement_enforcement'          => '',
            'accessibility_statement_compliance_level'     => 'AA',
            'accessibility_statement_additional_measures'  => '',
            'accessibility_statement_last_reviewed'        => '',

            // Color theme.
            'color_theme_default'   => 'system',
        );

        /**
         * Filter the canonical default settings.
         *
         * @param array $defaults Default values for the `exa11y_settings` option.
         */
        return apply_filters('exa11y_default_settings', $defaults);
    }
}
