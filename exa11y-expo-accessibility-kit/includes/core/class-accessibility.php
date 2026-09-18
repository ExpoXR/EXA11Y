<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class EXA11Y_Accessibility {
    /**
     * The plugin settings
     */
    private $settings;

    /**
     * Initialize the class.
     */
    public function __construct() {
        // Use the standardized settings option name
        $this->settings = get_option('exa11y_settings', array());
        
        // Add frontend actions
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'add_accessibility_controls'));
    }

    /**
     * Enqueue frontend styles.
     */
    public function enqueue_styles() {
        // Enqueue dashicons for frontend use (needed for accessibility toggle button and other icons)
        wp_enqueue_style('dashicons');
        
        // No build step: the stylesheet always loads as authored source
        // (native @import chain), human-readable for WordPress.org review.
        $frontend_css = 'assets/css/frontend.css';
        // Version by file mtime so CSS edits always bust the browser cache
        // (fixes stale "old CSS still showing" after updates).
        $css_ver = file_exists(EXA11Y_PATH . $frontend_css) ? filemtime(EXA11Y_PATH . $frontend_css) : EXA11Y_VERSION;
        wp_enqueue_style('exa11y-frontend', EXA11Y_URL . $frontend_css, array('dashicons'), $css_ver, 'all');
        
        // Emit the widget's CSS-variable contract (:root block) derived from
        // admin settings. The component CSS consumes these variables only.
        wp_add_inline_style('exa11y-frontend', $this->build_menu_css_variables());
        
        $position = isset($this->settings['panel_position']) ? $this->settings['panel_position'] : 'bottom-right';
        
        // Extract vertical and horizontal positions
        $position_parts = explode('-', $position);
        $vertical = isset($position_parts[0]) ? $position_parts[0] : 'bottom';
        $horizontal = isset($position_parts[1]) ? $position_parts[1] : 'right';
        
        // Set positioning CSS based on position values
        $left = 'auto';
        $right = 'auto';
        $top = 'auto';
        $bottom = 'auto';
        $transform = 'none';
        
        // Handle horizontal positioning
        if ($horizontal === 'left') {
            $left = '20px';
        } elseif ($horizontal === 'right') {
            $right = '20px';
        } elseif ($horizontal === 'center') {
            $left = '50%';
            $transform = 'translateX(-50%)';
        }
        
        // Handle vertical positioning
        if ($vertical === 'bottom') {
            $bottom = '20px';
        } elseif ($vertical === 'center') {
            $top = '50%';
            $transform = $transform === 'none' ? 'translateY(-50%)' : 'translate(-50%, -50%)';
        }
        
        // Panel positioning (always relative to the button)
        $panel_left = 'auto';
        $panel_right = 'auto';
        $panel_top = 'auto';
        $panel_bottom = 'auto';
        $panel_transform = 'none';
        $panel_translate = '';
        
        // For center vertical positioning, position panel correctly beside the icon
        if ($vertical === 'center') {
            $panel_top = '50%';
            $panel_translate = 'translateY(-50%)';
            
            if ($horizontal === 'left') {
                $panel_left = '60px'; // Position to the right of the left-aligned icon
            } elseif ($horizontal === 'right') {
                $panel_right = '60px'; // Position to the left of the right-aligned icon
            }
        } else {
            // Standard positioning for bottom alignment
            if ($horizontal === 'left') {
                $panel_left = '0';
            } elseif ($horizontal === 'right') {
                $panel_right = '0';
            } elseif ($horizontal === 'center') {
                $panel_left = '50%';
                $panel_transform = 'translateX(-50%)';
            }
            
            if ($vertical === 'bottom') {
                $panel_bottom = '60px';
            }
        }
        
        // Set transform property based on the panel_translate
        if (!empty($panel_translate)) {
            $panel_transform = $panel_translate;
        }
        
        // Structural positioning only (input-derived, not theming). All widget
        // colours and shape come from build_menu_css_variables().
        $custom_css = "
            .exa11y-accessibility-controls {
                left: {$left};
                right: {$right};
                top: {$top};
                bottom: {$bottom};
                transform: {$transform};
            }
            .exa11y-accessibility-panel {
                left: {$panel_left};
                right: {$panel_right};
                top: {$panel_top};
                bottom: {$panel_bottom};
                transform: {$panel_transform};
            }
        ";

        wp_add_inline_style('exa11y-frontend', $custom_css);
    }
    
    /**
     * Helper function to convert hex color to rgba
     */
    private function hex2rgba($color, $opacity = false) {
        // If no color provided, return transparent
        if(empty($color)) {
            return 'rgba(0,0,0,0)';
        }
        
        // If the color starts with '#', remove it
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }
        
        // Check if color is valid
        if (strlen($color) == 6) {
            // Break the hex into RGB
            $rgb = array(
                hexdec(substr($color, 0, 2)), // Red
                hexdec(substr($color, 2, 2)), // Green
                hexdec(substr($color, 4, 2))  // Blue
            );
        } else if (strlen($color) == 3) {
            // Short format to full format
            $rgb = array(
                hexdec(substr($color, 0, 1) . substr($color, 0, 1)), // Red
                hexdec(substr($color, 1, 1) . substr($color, 1, 1)), // Green
                hexdec(substr($color, 2, 1) . substr($color, 2, 1))  // Blue
            );
        } else {
            // Invalid format, return black
            return 'rgba(0,0,0,1)';
        }
        
        // Format the output
        return 'rgba(' . implode(',', $rgb) . ',' . $opacity . ')';
    }

    /**
     * Enqueue frontend scripts.
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery'); // Ensure jQuery is enqueued

        // No build step: every component loads as its own authored source
        // file, human-readable for WordPress.org review.
        wp_enqueue_script('exa11y-debug', EXA11Y_URL . 'assets/js/components/debug.js', array('jquery'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-content-wrapper', EXA11Y_URL . 'assets/js/components/contentWrapper.js', array('jquery', 'exa11y-debug'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-storage', EXA11Y_URL . 'assets/js/components/storage.js', array('jquery', 'exa11y-debug'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-controls', EXA11Y_URL . 'assets/js/components/controls.js', array('jquery', 'exa11y-debug', 'exa11y-storage'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-font-settings', EXA11Y_URL . 'assets/js/components/fontSettings.js', array('jquery', 'exa11y-debug', 'exa11y-storage'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-filters', EXA11Y_URL . 'assets/js/components/filters.js', array('jquery', 'exa11y-debug', 'exa11y-storage'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-keyboard', EXA11Y_URL . 'assets/js/components/keyboard.js', array('jquery', 'exa11y-debug'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-screen-reader', EXA11Y_URL . 'assets/js/components/screenReader.js', array('jquery', 'exa11y-debug'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-highlight-links', EXA11Y_URL . 'assets/js/components/highlightLinks.js', array('jquery', 'exa11y-debug', 'exa11y-screen-reader'), EXA11Y_VERSION, true);
        wp_enqueue_script('exa11y-hide-images', EXA11Y_URL . 'assets/js/components/hideImages.js', array('jquery', 'exa11y-debug', 'exa11y-screen-reader'), EXA11Y_VERSION, true);
        // Skip-to-content link (only acts when the skip_links setting is enabled).
        wp_enqueue_script('exa11y-skip-links', EXA11Y_URL . 'assets/js/components/skipLinks.js', array('exa11y-debug'), EXA11Y_VERSION, true);
        // Widget enhancements: visible close button, focus trap, Alt+A shortcut.
        wp_enqueue_script('exa11y-widget-enhancements', EXA11Y_URL . 'assets/js/components/widgetEnhancements.js', array('jquery', 'exa11y-debug', 'exa11y-controls', 'exa11y-screen-reader'), EXA11Y_VERSION, true);

        $localize_handle = 'exa11y-debug';
        $optional_deps   = array('jquery', 'exa11y-debug', 'exa11y-storage');

        // Enqueue the orchestrator last, depending on all component handles.
        wp_enqueue_script(
            'exa11y-frontend',
            EXA11Y_URL . 'assets/js/frontend.js',
            array(
                'jquery',
                'exa11y-debug',
                'exa11y-content-wrapper',
                'exa11y-storage',
                'exa11y-controls',
                'exa11y-font-settings',
                'exa11y-filters',
                'exa11y-keyboard',
                'exa11y-screen-reader',
                'exa11y-highlight-links',
                'exa11y-hide-images',
            ),
            EXA11Y_VERSION,
            true
        );

        // Optional scripts excluded from the bundle — loaded conditionally in both modes.

        // Color themes (only when the setting is on).
        if (isset($this->settings['color_theme']) && $this->settings['color_theme'] == 1) {
            wp_enqueue_script('exa11y-themes', EXA11Y_URL . 'assets/js/components/themes.js', $optional_deps, EXA11Y_VERSION, true);
        }

        // Get the icon hover text if set
        $icon_hover_text = isset($this->settings['icon_hover_text']) ? $this->settings['icon_hover_text'] : esc_html__('Open Accessibility Menu', 'exa11y-expo-accessibility-kit');

        // Get the active theme slug for JS consumers
        $active_theme = wp_get_theme()->get_stylesheet();

        // Get menu font sizes from settings
        $menu_font_size = isset($this->settings['menu_font_size']) ? $this->settings['menu_font_size'] : array(
            'header' => '18',
            'subheader' => '16',
            'buttons' => '14',
            'text' => '14',
            'elements' => '14'
        );

        // Pass settings to JavaScript, attached to 'exa11y-debug' (the first
        // component script enqueued) so window.exa11y is defined before any
        // later component IIFE runs.
        wp_localize_script($localize_handle, 'exa11y', array(
            'fontSizeActive' => (isset($this->settings['font_size_scaling']) && $this->settings['font_size_scaling'] == 1) ? 1 : 0,
            'menuFontSizes' => $menu_font_size,
            'lineHeightActive' => (isset($this->settings['line_height_scaling']) && $this->settings['line_height_scaling'] == 1) ? 1 : 0,
            'readableFontActive' => (isset($this->settings['readable_font']) && $this->settings['readable_font'] == 1) ? 1 : 0,
            'colorThemeActive' => (isset($this->settings['color_theme']) && $this->settings['color_theme'] == 1) ? 1 : 0,
            'colorThemeDefault' => isset($this->settings['color_theme_default']) ? $this->settings['color_theme_default'] : 'system',
            'iconHoverText' => $icon_hover_text,
            'contrastActive' => (isset($this->settings['contrast']) && $this->settings['contrast'] == 1) ? 1 : 0,
            'saturationActive' => (isset($this->settings['saturation']) && $this->settings['saturation'] == 1) ? 1 : 0,
            'brightnessActive' => (isset($this->settings['brightness']) && $this->settings['brightness'] == 1) ? 1 : 0,
            'blueFilterActive' => (isset($this->settings['blue_filter']) && $this->settings['blue_filter'] == 1) ? 1 : 0,
            'grayscaleActive' => (isset($this->settings['gray_mode']) && $this->settings['gray_mode'] == 1) ? 1 : 0,
            'colorVisionDeficiencyActive' => (isset($this->settings['color_vision_deficiency']) && $this->settings['color_vision_deficiency'] == 1) ? 1 : 0,
            'highlightLinksActive' => (isset($this->settings['highlight_links']) && $this->settings['highlight_links'] == 1) ? 1 : 0,
            'hideImagesActive' => (isset($this->settings['hide_images']) && $this->settings['hide_images'] == 1) ? 1 : 0,
            'position' => isset($this->settings['panel_position']) ? $this->settings['panel_position'] : 'bottom-right',
            'version' => EXA11Y_VERSION,
            'debugMode' => (defined('WP_DEBUG') && WP_DEBUG) ? 1 : 0,
            'pluginName' => 'exa11y',
            'currentTheme' => $active_theme,
            'isLoggedIn' => is_user_logged_in() ? 1 : 0,
            // Behaviour flags consumed by the JS components.
            'settings' => array(
                'keyboard_shortcuts' => !empty($this->settings['keyboard_shortcuts']),
                'skip_links'         => !empty($this->settings['skip_links']),
                'hide_on_mobile'     => !empty($this->settings['hide_on_mobile']),
            ),
            // Translatable strings used by the JS components.
            'i18n' => array(
                'close'         => esc_html__('Close accessibility menu', 'exa11y-expo-accessibility-kit'),
                'skipToContent' => esc_html__('Skip to main content', 'exa11y-expo-accessibility-kit'),
            ),
        ));
    }

    /**
     * Add accessibility controls to the frontend.
     */
    public function add_accessibility_controls() {
        // Only show controls if at least one feature is enabled
        if (empty($this->settings)) {
            return;
        }

        // ---------------------------------------------------------------------
        // Resolve, once, which individual menu items render and therefore which
        // group headers are non-empty. The template consumes these directly -
        // it must not recompute them.
        // ---------------------------------------------------------------------
        $exa11y_show_font_size        = !empty($this->settings['font_size_scaling']);
        $exa11y_show_line_height      = !empty($this->settings['line_height_scaling']);
        $exa11y_show_readable         = !empty($this->settings['readable_font']);
        $exa11y_show_highlight_links  = !empty($this->settings['highlight_links']);
        $exa11y_show_hide_images      = !empty($this->settings['hide_images']);

        $exa11y_show_contrast    = !empty($this->settings['contrast']);
        $exa11y_show_saturation  = !empty($this->settings['saturation']);
        $exa11y_show_brightness  = !empty($this->settings['brightness']);
        $exa11y_show_blue_filter = !empty($this->settings['blue_filter']);
        $exa11y_show_grayscale   = !empty($this->settings['gray_mode']);
        $exa11y_show_cvd         = !empty($this->settings['color_vision_deficiency']);
        $exa11y_show_color_theme = !empty($this->settings['color_theme']);

        $exa11y_show_statement = !empty($this->settings['accessibility_statement_enabled'])
            && !empty($this->settings['accessibility_statement_page']);

        $exa11y_group_content = $exa11y_show_font_size || $exa11y_show_line_height || $exa11y_show_readable
            || $exa11y_show_highlight_links || $exa11y_show_hide_images;
        $exa11y_group_color   = $exa11y_show_contrast || $exa11y_show_saturation || $exa11y_show_brightness
            || $exa11y_show_blue_filter || $exa11y_show_grayscale || $exa11y_show_cvd || $exa11y_show_color_theme;
        $exa11y_group_nav     = $exa11y_show_statement;

        // Nothing to show means no widget at all.
        if (!$exa11y_group_content && !$exa11y_group_color && !$exa11y_group_nav) {
            return;
        }

        // Render the widget template. The view consumes the flags resolved
        // above plus the raw settings array.
        $settings = $this->settings;
        include EXA11Y_PATH . 'includes/core/views/accessibility-widget.php';
    }
    
    /**
     * Build the front-end widget's CSS-variable contract.
     *
     * Emits a single :root block mapping exa11y_ appearance settings to the
     * --exa11y-menu-* custom properties consumed by the component CSS. This is the
     * ONLY styling PHP gives the widget — no per-selector rules, no !important.
     * Defaults here must match exa11y_get_default_settings() and the token
     * defaults in assets/css/_tokens.css.
     *
     * @return string CSS :root block.
     */
    private function build_menu_css_variables() {
        $s = $this->settings;

        // Trigger icon, panel surface, buttons and sliders are fixed brand
        // colours and shapes; only the two button colours and the menu font
        // sizes below come from settings.
        $toggle_bg     = '#2AACE2';
        $toggle_icon   = '#ffffff';
        $toggle_pad    = 10;

        $menu_bg       = '#ffffff';
        $menu_text     = '#333333';
        $menu_hover    = '#f0f0f0';
        $menu_radius   = 12;
        $menu_pad      = 20;

        $btn_bg     = '#2AACE2';
        $btn_text   = '#ffffff';
        $btn_border = '#2AACE2';
        $btn_radius = 4;
        $btn_pad    = 8;
        // sanitize_hex_color() already restricted these to a valid hex color
        // (or empty) at save time in admin-core.php; esc_attr() here is
        // defense-in-depth so nothing unescaped reaches the inline <style>
        // block regardless of how the option was written.
        $btn_active = esc_attr(!empty($s['active_button_color']) ? $s['active_button_color'] : '#2AACE2');
        $btn_hover  = esc_attr(!empty($s['hover_button_color']) ? $s['hover_button_color'] : '#1e8bb8');

        $slider_track = '#dddddd';
        $slider_thumb = '#2AACE2';

        // Menu typography.
        $menu_font_size = isset($s['menu_font_size']) && is_array($s['menu_font_size']) ? $s['menu_font_size'] : array();
        $font_header    = isset($menu_font_size['header']) ? max(10, min(30, intval($menu_font_size['header']))) : 18;
        $font_subheader = isset($menu_font_size['subheader']) ? max(10, min(30, intval($menu_font_size['subheader']))) : 16;
        $font_buttons   = isset($menu_font_size['buttons']) ? max(10, min(30, intval($menu_font_size['buttons']))) : 14;
        $font_text      = isset($menu_font_size['text']) ? max(10, min(30, intval($menu_font_size['text']))) : 14;
        $font_elements  = isset($menu_font_size['elements']) ? max(10, min(30, intval($menu_font_size['elements']))) : 14;

        // Derived values.
        $panel_border      = $this->adjust_color_brightness($menu_bg, -20);
        $panel_shadow      = '0 8px 24px rgba(0, 0, 0, 0.18)';
        $toggle_radius_css = '50%';
        $toggle_size       = 32 + (2 * $toggle_pad);
        $focus_ring        = '0 0 0 3px ' . $this->hex2rgba($btn_active, 0.5);

        return "
            :root {
                --exa11y-menu-toggle-bg: {$toggle_bg};
                --exa11y-menu-toggle-icon: {$toggle_icon};
                --exa11y-menu-toggle-hover-bg: {$btn_hover};
                --exa11y-menu-toggle-size: {$toggle_size}px;
                --exa11y-menu-toggle-radius: {$toggle_radius_css};

                --exa11y-menu-panel-bg: {$menu_bg};
                --exa11y-menu-panel-text: {$menu_text};
                --exa11y-menu-panel-border: {$panel_border};
                --exa11y-menu-panel-radius: {$menu_radius}px;
                --exa11y-menu-panel-shadow: {$panel_shadow};
                --exa11y-menu-panel-pad: {$menu_pad}px;

                --exa11y-menu-hover-bg: {$menu_hover};

                --exa11y-menu-btn-bg: {$btn_bg};
                --exa11y-menu-btn-text: {$btn_text};
                --exa11y-menu-btn-border: {$btn_border};
                --exa11y-menu-btn-radius: {$btn_radius}px;
                --exa11y-menu-btn-pad: {$btn_pad}px;
                --exa11y-menu-btn-active: {$btn_active};
                --exa11y-menu-btn-hover: {$btn_hover};

                --exa11y-menu-slider-track: {$slider_track};
                --exa11y-menu-slider-thumb: {$slider_thumb};

                --exa11y-menu-focus-ring: {$focus_ring};

                --exa11y-menu-header-font-size: {$font_header}px;
                --exa11y-menu-subheader-font-size: {$font_subheader}px;
                --exa11y-menu-buttons-font-size: {$font_buttons}px;
                --exa11y-menu-text-font-size: {$font_text}px;
                --exa11y-menu-elements-font-size: {$font_elements}px;
            }
        ";
    }
    
    /**
     * Adjust color brightness
     * @param string $hex Hex color code
     * @param int $steps Amount to adjust brightness (positive = lighter, negative = darker)
     * @return string Adjusted hex color code
     */
    private function adjust_color_brightness($hex, $steps) {
        // Remove # if present
        $hex = str_replace('#', '', $hex);
        
        // Convert to RGB
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2).str_repeat(substr($hex, 1, 1), 2).str_repeat(substr($hex, 2, 1), 2);
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        
        // Convert back to hex
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
}
