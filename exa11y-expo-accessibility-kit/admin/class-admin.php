<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://expoxr.com
 * @since      1.0.0
 *
 * @package    exa11y
 * @subpackage exa11y-expo-accessibility-kit/admin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class EXA11Y_Admin {
    /**
     * Admin Fields instance
     * 
     * @var EXA11Y_Admin_Fields
     */
    private $fields;
    
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Load and initialize components
        $this->load_components();
        $this->init_components();

        // Enqueue admin scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }

    /**
     * Load the admin component files
     */
    private function load_components() {
        // Shared page template (header/section/footer chrome) — load first so
        // every page class can rely on EXA11Y_Admin_Page.
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/components/admin-page-template.php';

        // Core component
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/components/admin-core.php';

        // Fields component (load this first as other components depend on it)
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/components/admin-fields.php';

        // Page-based menu component (new)
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/components/admin-menu-pages.php';
        
        // Settings component
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/components/admin-settings.php';
        
        // Ajax component
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/components/admin-ajax.php';
        
        // Load individual page files
        $this->load_page_files();
    }    /**
     * Initialize admin component classes
     */
    private function init_components() {
        // Make this instance available globally
        global $exa11y_admin;
        $exa11y_admin = $this;
        
        // Initialize the fields class first and store the instance
        $this->fields = new EXA11Y_Admin_Fields();
        
        // Create instances of each component and pass the fields instance.
        // EXA11Y_Admin_Core is used statically (validate_settings), never instantiated.
        $admin_settings = new EXA11Y_Admin_Settings($this->fields);
        $admin_menu_pages = new EXA11Y_Admin_Menu_Pages();
        $admin_ajax = new EXA11Y_Admin_Ajax();
    }
      /**
     * Load individual page files
     */    private function load_page_files() {
        $pages_dir = plugin_dir_path(dirname(__FILE__)) . 'admin/pages/';        $page_files = array(
            'page-general.php',
            'page-accessibility-statement.php',
            'page-accessibility-general.php',  // Accessibility features (General / Font / Color tabs)
            'page-tools.php',
        );
        
        foreach ($page_files as $file) {
            $file_path = $pages_dir . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }      /**
     * Register and enqueue admin-specific styles and scripts
     *
     * @since    1.0.0
     * @param    string    $hook    The current admin page
     */    public function enqueue_admin_scripts($hook) {
        $is_plugin_page = (false !== strpos($hook, 'exa11y'));

        // EXA11Y admin assets load ONLY on the plugin's own screens. Nothing
        // is enqueued globally, so the ExpoXR brand skin (headings, focus rings,
        // branded buttons in admin-base.css + admin.css) can never leak onto
        // other wp-admin pages.
        if (!$is_plugin_page) {
            return;
        }

        // No build step: admin styles always load as authored source, human-readable for WordPress.org review.
        $admin_css = 'admin/css/admin.css';
        $admin_base_css = 'admin/css/admin-base.css';

        wp_enqueue_style('dashicons');
        wp_enqueue_style(
            $this->plugin_name . '-admin-base',
            EXA11Y_URL . $admin_base_css,
            array(),
            $this->asset_ver($admin_base_css),
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-admin',
            EXA11Y_URL . $admin_css,
            array($this->plugin_name . '-admin-base'),
            $this->asset_ver($admin_css),
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-accessibility-statement',
            EXA11Y_URL . 'admin/css/accessibility-statement.css',
            array(),
            $this->asset_ver('admin/css/accessibility-statement.css'),
            'all'
        );

        // WordPress colour picker (used by several settings fields).
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // Colour format helper.
        wp_enqueue_script(
            $this->plugin_name . '-color-helper',
            plugin_dir_url(dirname(__FILE__)) . 'admin/js/color-format-helper.js',
            array('jquery'),
            $this->asset_ver('admin/js/color-format-helper.js'),
            false
        );

        // Enqueue admin scripts (authored source, no build step).
        $admin_js = 'admin/js/admin.js';
        wp_enqueue_script(
            $this->plugin_name . '-admin',
            EXA11Y_URL . $admin_js,
            array('jquery', 'wp-color-picker', $this->plugin_name . '-color-helper'),
            $this->asset_ver($admin_js),
            false
        );

        // Localize script for translations and variables
        wp_localize_script(
            $this->plugin_name . '-admin',
            'exa11yAdmin',
            array(
                // Nonce consumed by admin.js for all admin AJAX calls; must match
                // the 'exa11y_admin_ajax' action verified in admin-ajax.php.
                'nonce' => wp_create_nonce('exa11y_admin_ajax'),
                'regenerating_text' => esc_html__('Regenerating...', 'exa11y-expo-accessibility-kit'),
                'error_text' => esc_html__('An error occurred. Please try again.', 'exa11y-expo-accessibility-kit'),
                'success_text' => esc_html__('Statement successfully regenerated!', 'exa11y-expo-accessibility-kit')
            )
        );

        // Cache-clearing data for the Tools page button (handler lives in admin.js).
        wp_localize_script(
            $this->plugin_name . '-admin',
            'exa11y_cache',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                // Must match the 'exa11y_admin_ajax' action verified by
                // handle_clear_cache() in admin-ajax.php.
                'nonce' => wp_create_nonce('exa11y_admin_ajax'),
                'strings' => array(
                    'confirm_clear' => esc_html__('Are you sure you want to clear all plugin cache? This will reset any cached settings for all users.', 'exa11y-expo-accessibility-kit'),
                    'success' => esc_html__('Plugin cache has been cleared successfully.', 'exa11y-expo-accessibility-kit'),
                    'failed' => esc_html__('Failed to clear cache. Please try again.', 'exa11y-expo-accessibility-kit'),
                ),
            )
        );

        // Color-theme settings toggle (delegated listener, so the markup needs
        // no inline onchange attribute).
        wp_enqueue_script(
            $this->plugin_name . '-color-theme-settings',
            EXA11Y_URL . 'admin/js/color-theme-settings.js',
            array(),
            $this->asset_ver('admin/js/color-theme-settings.js'),
            true
        );

        // Tab switching for the merged Accessibility Features page.
        if (strpos($hook, 'accessibility-general') !== false) {
            wp_enqueue_script(
                $this->plugin_name . '-admin-tabs',
                EXA11Y_URL . 'admin/js/admin-tabs.js',
                array('jquery'),
                $this->asset_ver('admin/js/admin-tabs.js'),
                true
            );
        }
    }

    /**
     * Build a cache-busting version string for a plugin-owned asset.
     *
     * Uses the file's modification time so edits invalidate the browser cache
     * immediately, falling back to the plugin version if the file is missing.
     *
     * @param  string $relative_path Path relative to the plugin root.
     * @return string|int
     */
    private function asset_ver($relative_path) {
        $abs = EXA11Y_PATH . ltrim($relative_path, '/');
        return file_exists($abs) ? filemtime($abs) : $this->version;
    }

    /**
     * Get the admin fields instance
     *
     * @return EXA11Y_Admin_Fields
     */
    public function get_fields() {
        return $this->fields;
    }
}

