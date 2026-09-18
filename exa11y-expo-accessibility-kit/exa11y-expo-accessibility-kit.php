<?php
/**
 * Plugin Name: EXA11Y - Expo Accessibility Kit
 * Plugin URI: https://www.expoxr.com/
 * Description: A free WordPress accessibility widget: font scaling, color/contrast adjustments, link and image helpers, and an accessibility statement page.
 * Version: 1.0.3
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Ayal Othman
 * Author URI: https://www.expoxr.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: exa11y-expo-accessibility-kit
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Prevent direct execution and ensure plugin is not loaded twice
if (defined('EXA11Y_VERSION')) {
    return;
}

// Define plugin constants
define('EXA11Y_VERSION', '1.0.3');
define('EXA11Y_PATH', plugin_dir_path(__FILE__));
define('EXA11Y_URL', plugin_dir_url(__FILE__));
define('EXA11Y_BASENAME', plugin_basename(__FILE__));

// Include required files with error checking
$exa11y_required_files = array(
    'admin/class-admin.php',
    'includes/core/class-debug.php',
    'includes/core/class-settings-defaults.php',
    'includes/core/class-accessibility.php',
);

foreach ($exa11y_required_files as $exa11y_file) {
    $exa11y_full_path = EXA11Y_PATH . $exa11y_file;
    if (file_exists($exa11y_full_path)) {
        require_once $exa11y_full_path;
    } else {
        // Surface the failure in the admin and bail out.
        if (is_admin()) {
            add_action('admin_notices', function() use ($exa11y_file) {
                echo '<div class="error"><p>EXA11Y Plugin Error: Missing required file: ' . esc_html($exa11y_file) . '</p></div>';
            });
        }
        return;
    }
}

/**
 * Helper function to initialize a class properly, handling singleton pattern
 *
 * @param string $class_name The name of the class to initialize
 * @return object|null The class instance or null on failure
 */
function exa11y_init_class($class_name, ...$args) {
    if (!class_exists($class_name)) {
        return null;
    }
    // Check if the class uses the singleton pattern with get_instance method
    if (method_exists($class_name, 'get_instance')) {
        return call_user_func_array(array($class_name, 'get_instance'), $args);
    }
    // Otherwise, create a new instance if it's not abstract
    $reflection = new ReflectionClass($class_name);
    if (!$reflection->isAbstract()) {
        return $reflection->newInstanceArgs($args);
    }
    return null;
}

/**
 * Begins execution of the plugin.
 *
 * @since 0.1.0
 */
function exa11y_run_plugin() {
    // Activation/deactivation hooks
    register_activation_hook(__FILE__, 'exa11y_activate');
    register_deactivation_hook(__FILE__, 'exa11y_deactivate');

    // Run the plugin components
    do_action('exa11y_before_init');
    
    // Initialize components with error handling
    try {
        // Initialize all plugin components using our helper function
        exa11y_init_class('EXA11Y_Accessibility');
        // Slug (not __FILE__) — it is used as the prefix for script/style handles.
        exa11y_init_class('EXA11Y_Admin', 'exa11y', EXA11Y_VERSION);
    } catch (Exception $e) {
        if (is_admin()) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="error"><p>EXA11Y Plugin Error: ' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        return;
    }

    do_action('exa11y_after_init');
}

/**
 * The code that runs during plugin activation.
 */
function exa11y_activate() {
    // Check for minimum PHP version (must match the plugin header requirement).
    if (version_compare(PHP_VERSION, '8.0', '<')) {
        deactivate_plugins(EXA11Y_BASENAME);
        wp_die(esc_html__('EXA11Y requires PHP 8.0 or higher.', 'exa11y-expo-accessibility-kit'));
    }

    // Check if WordPress version is compatible (must match the plugin header requirement).
    if (version_compare(get_bloginfo('version'), '6.5', '<')) {
        deactivate_plugins(EXA11Y_BASENAME);
        wp_die(esc_html__('EXA11Y requires WordPress 6.5 or higher.', 'exa11y-expo-accessibility-kit'));
    }
    
    // Create accessibility statement page if it doesn't exist
    $statement_page_id = get_option('exa11y_accessibility_statement_page_id');
    if (!$statement_page_id) {
        $statement_content = exa11y_get_accessibility_statement_content();
        
        $page_args = array(
            'post_title'    => esc_html__('Accessibility Statement', 'exa11y-expo-accessibility-kit'),
            'post_content'  => $statement_content,
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page',
        );
        
        $page_id = wp_insert_post($page_args);
        if ($page_id && !is_wp_error($page_id)) {
            update_option('exa11y_accessibility_statement_page_id', $page_id);
        }
    }
    
    // Set default plugin settings with all accessibility features disabled by default
    $existing_settings = get_option('exa11y_settings', array());
    
    // Only set defaults if this is a fresh install (no existing settings)
    if (empty($existing_settings)) {
        // Canonical defaults live in includes/core/class-settings-defaults.php
        update_option('exa11y_settings', exa11y_get_default_settings());
    } else {
        // Backfill any keys added in later versions so $settings[$key] is always defined.
        update_option('exa11y_settings', array_merge(exa11y_get_default_settings(), $existing_settings));
    }
    
    // Flush rewrite rules to ensure proper URL structure
    flush_rewrite_rules();

    // Other activation code
    do_action('exa11y_activated');
}

/**
 * The code that runs during plugin deactivation.
 */
function exa11y_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();

    // Cleanup options or temporary data
    do_action('exa11y_deactivated');
}

/**
 * Generate the content for the accessibility statement page.
 */
function exa11y_get_accessibility_statement_content() {
    // Get site info
    $site_name = get_bloginfo('name');
    $site_url = get_bloginfo('url');

    // Get enabled features and statement content from the main settings array.
    $settings = get_option('exa11y_settings', array());

    // Organization info comes from the settings array the admin UI edits.
    $org_name = !empty($settings['accessibility_statement_org_name'])
        ? $settings['accessibility_statement_org_name']
        : $site_name;
    $org_address = !empty($settings['accessibility_statement_contact_info'])
        ? $settings['accessibility_statement_contact_info']
        : '';
    $org_email = !empty($settings['accessibility_statement_org_email'])
        ? $settings['accessibility_statement_org_email']
        : get_bloginfo('admin_email');
    $org_phone = !empty($settings['accessibility_statement_org_phone'])
        ? $settings['accessibility_statement_org_phone']
        : '';

    // Compliance level (e.g. A, AA, AAA) from settings, default AA.
    $compliance_level = !empty($settings['accessibility_statement_compliance_level'])
        ? $settings['accessibility_statement_compliance_level']
        : 'AA';

    // Last reviewed date: use the configured value when present, else today.
    $statement_date = !empty($settings['accessibility_statement_last_reviewed'])
        ? $settings['accessibility_statement_last_reviewed']
        : gmdate('F j, Y');
    $font_scaling = !empty($settings['font_size_scaling']) ? 'yes' : 'no';
    $contrast_modes = !empty($settings['contrast']) ? 'yes' : 'no';

    // Build statement HTML
    $statement = '';
    $statement .= '<h1>' . esc_html__('Accessibility Statement', 'exa11y-expo-accessibility-kit') . '</h1>';
    
    $statement .= '<p>' . sprintf(
        /* translators: %1$s: Organization name */
        esc_html__('%1$s is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone, and applying the relevant accessibility standards.', 'exa11y-expo-accessibility-kit'),
        esc_html($org_name)
    ) . '</p>';
    
    $statement .= '<h2>' . esc_html__('Accessibility Features', 'exa11y-expo-accessibility-kit') . '</h2>';
    $statement .= '<p>' . esc_html__('Our website includes the following accessibility features:', 'exa11y-expo-accessibility-kit') . '</p>';
    
    $statement .= '<ul>';
    
    if ($font_scaling === 'yes') {
        $statement .= '<li>' . esc_html__('Text resizing: Adjust the font size for easier reading', 'exa11y-expo-accessibility-kit') . '</li>';
    }
    
    if ($contrast_modes === 'yes') {
        $statement .= '<li>' . esc_html__('High contrast modes: Choose from different color contrast options', 'exa11y-expo-accessibility-kit') . '</li>';
    }

    $statement .= '</ul>';
    
    $statement .= '<h2>' . esc_html__('Contact Information', 'exa11y-expo-accessibility-kit') . '</h2>';
    $statement .= '<p>' . esc_html__('If you encounter any accessibility barriers on this website, please contact us:', 'exa11y-expo-accessibility-kit') . '</p>';
    
    $statement .= '<ul>';
    if (!empty($org_name)) {
        $statement .= '<li><strong>' . esc_html__('Organization:', 'exa11y-expo-accessibility-kit') . '</strong> ' . esc_html($org_name) . '</li>';
    }
    if (!empty($org_address)) {
        $statement .= '<li><strong>' . esc_html__('Address:', 'exa11y-expo-accessibility-kit') . '</strong> ' . esc_html($org_address) . '</li>';
    }
    if (!empty($org_email)) {
        $statement .= '<li><strong>' . esc_html__('Email:', 'exa11y-expo-accessibility-kit') . '</strong> <a href="mailto:' . esc_attr($org_email) . '">' . esc_html($org_email) . '</a></li>';
    }
    if (!empty($org_phone)) {
        $statement .= '<li><strong>' . esc_html__('Phone:', 'exa11y-expo-accessibility-kit') . '</strong> ' . esc_html($org_phone) . '</li>';
    }
    $statement .= '</ul>';
    
    $statement .= '<h2>' . esc_html__('Compliance Status', 'exa11y-expo-accessibility-kit') . '</h2>';
    $statement .= '<p>' . sprintf(
        /* translators: %1$s: WCAG conformance level (e.g. AA). %2$s: Last updated date. */
        esc_html__('This website is striving to conform to the Web Content Accessibility Guidelines (WCAG) 2.1, Level %1$s. This accessibility statement was last updated on %2$s.', 'exa11y-expo-accessibility-kit'),
        esc_html($compliance_level),
        esc_html($statement_date)
    ) . '</p>';

    // Optional additional accessibility measures configured by the site owner.
    if (!empty($settings['accessibility_statement_additional_measures'])) {
        $statement .= '<h2>' . esc_html__('Additional Accessibility Measures', 'exa11y-expo-accessibility-kit') . '</h2>';
        $statement .= wp_kses_post(wpautop($settings['accessibility_statement_additional_measures']));
    }

    // Optional enforcement / complaints procedure information.
    if (!empty($settings['accessibility_statement_enforcement'])) {
        $statement .= '<h2>' . esc_html__('Enforcement Procedure', 'exa11y-expo-accessibility-kit') . '</h2>';
        $statement .= wp_kses_post(wpautop($settings['accessibility_statement_enforcement']));
    }
    
    return apply_filters('exa11y_accessibility_statement_content', $statement);
}

// Run the plugin
exa11y_run_plugin();
