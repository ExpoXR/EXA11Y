<?php
/**
 * Admin Settings Component
 * 
 * Handles registration of settings, sections, and fields.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Admin_Settings
 * 
 * Manages settings registration and organization.
 */
class EXA11Y_Admin_Settings {
    /**
     * Admin Fields instance
     * 
     * @var EXA11Y_Admin_Fields
     */
    private $fields;
    
    /**
     * Initialize the class.
     * 
     * @param EXA11Y_Admin_Fields $fields The fields instance for rendering fields
     */    public function __construct($fields) {
        $this->fields = $fields;
        add_action('admin_init', array($this, 'register_settings'));
    }    /**
     * Register plugin settings
     */    
    public function register_settings() {
        global $wp_registered_settings;
        if (!isset($wp_registered_settings['exa11y_settings'])) {
            register_setting(
                'exa11y_options',
                'exa11y_settings',
                array(
                    'type'              => 'array',
                    'sanitize_callback' => array('EXA11Y_Admin_Core', 'validate_settings'),
                    'default'           => exa11y_get_default_settings(),
                )
            );
        }

        // Register each settings group
        $this->register_general_behavior_settings();
        $this->register_appearance_settings();
        $this->register_accessibility_settings();
        $this->register_accessibility_statement_settings();
        $this->register_font_settings();
        $this->register_color_settings();
        $this->register_maintenance_settings();
        $this->register_import_export_settings();
    }

    /**
     * Register general behavior settings.
     */
    private function register_general_behavior_settings() {
        add_settings_section(
            'exa11y_general_behavior',
            '', // Empty title to avoid duplication
            array($this, 'general_behavior_section_callback'),
            'exa11y_general_behavior'
        );

        add_settings_field(
            'hide_on_mobile',
            esc_html__('Hide on Mobile', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_checkbox_field'),
            'exa11y_general_behavior',
            'exa11y_general_behavior',
            array(
                'id' => 'hide_on_mobile',
                'label' => esc_html__('Hide the accessibility widget on mobile devices', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('When enabled, the floating accessibility button is hidden on small screens.', 'exa11y-expo-accessibility-kit')
            )
        );

        add_settings_field(
            'keyboard_shortcuts',
            esc_html__('Keyboard Shortcuts', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_checkbox_field'),
            'exa11y_general_behavior',
            'exa11y_general_behavior',
            array(
                'id' => 'keyboard_shortcuts',
                'label' => esc_html__('Enable keyboard shortcuts', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('Allow visitors to open and operate the accessibility menu using keyboard shortcuts.', 'exa11y-expo-accessibility-kit')
            )
        );

        add_settings_field(
            'skip_links',
            esc_html__('Skip Links', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_checkbox_field'),
            'exa11y_general_behavior',
            'exa11y_general_behavior',
            array(
                'id' => 'skip_links',
                'label' => esc_html__('Enable "Skip to content" links', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('Adds a keyboard-focusable link that lets visitors jump straight to the main content.', 'exa11y-expo-accessibility-kit')
            )
        );
    }

    /**
     * Register appearance settings: position, menu font size, hover text,
     * and button colors.
     */
    private function register_appearance_settings() {
        add_settings_section(
            'exa11y_appearance_basic',
            '', // Empty title to avoid duplication
            array($this, 'appearance_section_callback'),
            'exa11y_appearance_basic'
        );

        add_settings_field(
            'panel_position',
            esc_html__('Accessibility Icon Position', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_panel_position_field'),
            'exa11y_appearance_basic',
            'exa11y_appearance_basic'
        );

        // Add new field for menu font size
        add_settings_field(
            'menu_font_size',
            esc_html__('Menu Font Size', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_menu_font_size_field'),
            'exa11y_appearance_basic',
            'exa11y_appearance_basic'
        );

        add_settings_field(
            'icon_hover_text',
            esc_html__('Hover Text for Icon', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_icon_hover_text_field'),
            'exa11y_appearance_basic',
            'exa11y_appearance_basic'
        );

        // Add new color picker fields for button customization
        add_settings_field(
            'active_button_color',
            esc_html__('Active Button Color', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_active_button_color_field'),
            'exa11y_appearance_basic',
            'exa11y_appearance_basic'
        );
        add_settings_field(
            'hover_button_color',
            esc_html__('Hover Button Color', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_hover_button_color_field'),
            'exa11y_appearance_basic',
            'exa11y_appearance_basic'
        );

    }

    /**
     * Register accessibility settings
     */
    private function register_accessibility_settings() {
        add_settings_section(
            'exa11y_accessibility',
            '', // Empty title to avoid duplication
            array($this, 'accessibility_section_callback'),
            'exa11y_accessibility'
        );

        add_settings_field(
            'highlight_links',
            esc_html__('Highlight Links', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_highlight_links_field'),
            'exa11y_accessibility',
            'exa11y_accessibility'
        );
          add_settings_field(
            'hide_images',
            esc_html__('Hide Images and Videos', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_hide_images_field'),
            'exa11y_accessibility',
            'exa11y_accessibility'
        );
    }

    /**
     * Register accessibility statement settings
     */    private function register_accessibility_statement_settings() {
        add_settings_section(
            'exa11y_accessibility_statement',
            '', // Empty title to avoid duplication
            array($this, 'accessibility_statement_section_callback'),
            'exa11y_accessibility_statement'
        );
        
        add_settings_field(
            'accessibility_statement_enabled',
            esc_html__('Enable Statement Link', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_checkbox_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_enabled',
                'label' => esc_html__('Show Accessibility Statement link', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('Displays a link to your accessibility statement page in the accessibility menu.', 'exa11y-expo-accessibility-kit')
            )
        );
        
        add_settings_field(
            'accessibility_statement_page',
            esc_html__('Statement Page', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_accessibility_statement_page_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement'
        );
        
        add_settings_field(
            'regenerate_accessibility_statement',
            esc_html__('Update Statement Content', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_regenerate_accessibility_statement_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement'
        );

        // Organization Information
        add_settings_field(
            'accessibility_statement_org_name',
            esc_html__('Organization Name', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_text_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_org_name',
                'label' => esc_html__('Organization Name', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('Name of the organization responsible for the website.', 'exa11y-expo-accessibility-kit')
            )
        );
        
        add_settings_field(
            'accessibility_statement_contact_info',
            esc_html__('Contact Information', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_textarea_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_contact_info',
                'label' => esc_html__('Organization Contact Information', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('Contact information to be included in the accessibility statement. You can use HTML for formatting.', 'exa11y-expo-accessibility-kit'),
                'rows' => 5,
                'placeholder' => esc_html__("Email: contact@example.com\nPhone: +1 234 567 8900\nContact Form: https://example.com/contact", 'exa11y-expo-accessibility-kit')
            )
        );
        
        add_settings_field(
            'accessibility_statement_org_email',
            esc_html__('Contact Email', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_text_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_org_email',
                'label' => esc_html__('Contact Email', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('Email address listed in the accessibility statement. Defaults to the site admin email when empty.', 'exa11y-expo-accessibility-kit')
            )
        );

        add_settings_field(
            'accessibility_statement_org_phone',
            esc_html__('Contact Phone', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_text_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_org_phone',
                'label' => esc_html__('Contact Phone', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('Phone number listed in the accessibility statement. Leave empty to omit.', 'exa11y-expo-accessibility-kit')
            )
        );

        add_settings_field(
            'accessibility_statement_enforcement',
            esc_html__('Enforcement Authority', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_text_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_enforcement',
                'label' => esc_html__('Enforcement Authority', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('Name of the official authority responsible for enforcing accessibility regulations in your jurisdiction.', 'exa11y-expo-accessibility-kit')
            )
        );
        
        // Compliance Information
        add_settings_field(
            'accessibility_statement_compliance_level',
            esc_html__('WCAG Compliance Level', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_select_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_compliance_level',
                'label' => esc_html__('WCAG Compliance Level', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('The level of WCAG compliance you are aiming to meet.', 'exa11y-expo-accessibility-kit'),
                'options' => array(
                    'A' => esc_html__('WCAG 2.1 Level A (Minimum)', 'exa11y-expo-accessibility-kit'),
                    'AA' => esc_html__('WCAG 2.1 Level AA (Standard)', 'exa11y-expo-accessibility-kit'),
                    'AAA' => esc_html__('WCAG 2.1 Level AAA (Enhanced)', 'exa11y-expo-accessibility-kit')
                ),
                'default' => 'AA'
            )
        );
        
        add_settings_field(
            'accessibility_statement_additional_measures',
            esc_html__('Additional Measures', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_textarea_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_additional_measures',
                'label' => esc_html__('Additional Accessibility Measures', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('List any additional measures you have taken to improve accessibility beyond the plugin features. Each line will become a bullet point.', 'exa11y-expo-accessibility-kit'),
                'rows' => 4,
                'placeholder' => esc_html__("Regular accessibility audits\nStaff training on accessibility\nUser testing with people with disabilities", 'exa11y-expo-accessibility-kit')
            )
        );
        
        add_settings_field(
            'accessibility_statement_last_reviewed',
            esc_html__('Last Reviewed Date', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_date_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement',
            array(
                'id' => 'accessibility_statement_last_reviewed',
                'label' => esc_html__('Last Reviewed Date', 'exa11y-expo-accessibility-kit'),
                'description' => esc_html__('The date when your accessibility statement was last reviewed. Leaving this empty will use today\'s date.', 'exa11y-expo-accessibility-kit')
            )
        );
        
        add_settings_field(
            'accessibility_statement_preview',
            esc_html__('Statement Preview', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_accessibility_statement_preview_field'),
            'exa11y_accessibility_statement',
            'exa11y_accessibility_statement'
        );
    }

    /**
     * Register font settings
     */
    private function register_font_settings() {
        add_settings_section(
            'exa11y_font_settings',
            '', // Empty title to avoid duplication
            array($this, 'font_settings_section_callback'),
            'exa11y_font_settings'
        );
        
        add_settings_field(
            'font_size_scaling',
            esc_html__('Font Size', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_font_size_field'),
            'exa11y_font_settings',
            'exa11y_font_settings'
        );
        
        add_settings_field(
            'line_height_scaling',
            esc_html__('Line Height', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_line_height_field'),
            'exa11y_font_settings',
            'exa11y_font_settings'
        );
        
        add_settings_field(
            'readable_font',
            esc_html__('Readable Font', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_readable_font_field'),
            'exa11y_font_settings',
            'exa11y_font_settings'
        );
    }

    /**
     * Register color settings
     */
    private function register_color_settings() {
        add_settings_section(
            'exa11y_color_settings',
            '', // Empty title to avoid duplication
            array($this, 'color_settings_section_callback'),
            'exa11y_color_settings'
        );

        add_settings_field(
            'contrast',
            esc_html__('Contrast', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_contrast_field'),
            'exa11y_color_settings',
            'exa11y_color_settings'
        );

        add_settings_field(
            'saturation',
            esc_html__('Saturation', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_saturation_field'),
            'exa11y_color_settings',
            'exa11y_color_settings'
        );

        add_settings_field(
            'brightness',
            esc_html__('Brightness', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_brightness_field'),
            'exa11y_color_settings',
            'exa11y_color_settings'
        );

        add_settings_field(
            'blue_filter',
            esc_html__('Blue Filter', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_blue_filter_field'),
            'exa11y_color_settings',
            'exa11y_color_settings'
        );        add_settings_field(
            'gray_mode',
            esc_html__('Gray Mode', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_gray_mode_field'),
            'exa11y_color_settings',
            'exa11y_color_settings'
        );
        
        add_settings_field(
            'color_vision_deficiency',
            esc_html__('Color Vision Deficiency Options', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_color_vision_deficiency_field'),
            'exa11y_color_settings',
            'exa11y_color_settings'
        );

        add_settings_field(
            'color_theme',
            esc_html__('Color Theme', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_color_theme_field'),
            'exa11y_color_settings',
            'exa11y_color_settings'
        );
    }

    /**
     * Register maintenance settings
     */
    private function register_maintenance_settings() {
        add_settings_section(
            'exa11y_maintenance',
            '', // Empty title to avoid duplication
            array($this, 'maintenance_section_callback'),
            'exa11y_maintenance'        );
        
        add_settings_field(
            'reset_accessibility_features',
            esc_html__('Reset Accessibility Features', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_reset_accessibility_features_field'),
            'exa11y_maintenance',
            'exa11y_maintenance'
        );

        add_settings_field(
            'reset_settings',
            esc_html__('Reset All Settings', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_reset_settings_field'),
            'exa11y_maintenance',
            'exa11y_maintenance'
        );
          add_settings_field(
            'clear_cache',
            esc_html__('Clear Cache', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_clear_cache_field'),
            'exa11y_maintenance',
            'exa11y_maintenance'
        );
    }

    /**
     * Register import/export settings.
     */
    private function register_import_export_settings() {
        add_settings_section(
            'exa11y_import_export',
            '', // Empty title to avoid duplication
            array($this, 'import_export_section_callback'),
            'exa11y_import_export'
        );
        
        add_settings_field(
            'export_import_settings',
            esc_html__('Export/Import Settings', 'exa11y-expo-accessibility-kit'),
            array($this->fields, 'render_export_import_settings_field'),
            'exa11y_import_export',
            'exa11y_import_export'
        );

    }

    /**
     * General behavior section callback
     */
    public function general_behavior_section_callback() {
        echo '<p>' . esc_html__('Control how and where the accessibility widget behaves for visitors.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Appearance section callback
     */
    public function appearance_section_callback() {
        echo '<p>' . esc_html__('Customize the appearance of the accessibility icon and panel.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Accessibility section callback
     */
    public function accessibility_section_callback() {
        echo '<p>' . esc_html__('Enable or disable the desired accessibility features.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Accessibility Statement section callback
     */    public function accessibility_statement_section_callback() {
        echo '<div class="exa11y-accessibility-statement-admin">';
        echo '<p class="exa11y-section-description">' . 
            esc_html__('An accessibility statement demonstrates your commitment to digital inclusion and helps users understand the accessibility features of your website.', 'exa11y-expo-accessibility-kit') . 
            '</p>';
        
        echo '<p class="exa11y-section-description">' . 
            esc_html__('The settings below allow you to customize your accessibility statement page which will be automatically populated with information about the accessibility features currently active on your site.', 'exa11y-expo-accessibility-kit') . 
            '</p>';
            
        echo '<p class="exa11y-section-description">' . 
            esc_html__('The statement will automatically update to reflect any changes in your accessibility settings.', 'exa11y-expo-accessibility-kit') . 
            '</p>';
        echo '</div>';
    }

    /**
     * Font Settings section callback
     */
    public function font_settings_section_callback() {
        echo '<p>' . esc_html__('Adjust font-related settings for accessibility.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Color Settings section callback
     */
    public function color_settings_section_callback() {
        echo '<p>' . esc_html__('Adjust color-related settings for accessibility.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Maintenance section callback
     */
    public function maintenance_section_callback() {
        echo '<p>' . esc_html__('Perform maintenance tasks for the plugin.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

    /**
     * Debugging section callback
     */
    public function import_export_section_callback() {
        echo '<p>' . esc_html__('Back up your settings to a file, or restore them from one.', 'exa11y-expo-accessibility-kit') . '</p>';
    }

}
