<?php
/**
 * Admin Menu Component (Page-based)
 * 
 * Handles menu creation with separate pages instead of tabs.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Admin_Menu_Pages
 * 
 * Manages the admin menu with separate pages.
 */
class EXA11Y_Admin_Menu_Pages {
    /**
     * Initialize the class.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_menu'), 999);
        // The quick-action handlers must be enqueued on the normal enqueue
        // hooks: on the front end the admin bar renders at wp_footer priority
        // 1000, far too late to enqueue scripts from within its callback.
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_bar_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_admin_bar_scripts'));
    }
    
    /**
     * Add the options pages to the menu
     */
    public function add_admin_menu() {
        // Main menu page (General Settings)
        add_menu_page(
            esc_html__('EXA11Y - Expo Accessibility Kit', 'exa11y-expo-accessibility-kit'),
            esc_html__('EXA11Y', 'exa11y-expo-accessibility-kit'),
            'manage_options',
            'exa11y',
            array('EXA11Y_Page_General', 'display_page'),
            'dashicons-universal-access',
            99
        );
        
        // General Settings submenu (same as main page)
        add_submenu_page(
            'exa11y',
            esc_html__('General Settings', 'exa11y-expo-accessibility-kit'),
            esc_html__('General Settings', 'exa11y-expo-accessibility-kit'),
            'manage_options',
            'exa11y',
            array('EXA11Y_Page_General', 'display_page')
        );
        
        // Accessibility Statement
        add_submenu_page(
            'exa11y',
            esc_html__('Accessibility Statement', 'exa11y-expo-accessibility-kit'),
            esc_html__('Accessibility Statement', 'exa11y-expo-accessibility-kit'),
            'manage_options',
            'exa11y-accessibility-statement',
            array('EXA11Y_Page_Accessibility_Statement', 'display_page')
        );
          // Accessibility Features (General / Font / Color tabs)
        add_submenu_page(
            'exa11y',
            esc_html__('Accessibility Features', 'exa11y-expo-accessibility-kit'),
            esc_html__('Accessibility Features', 'exa11y-expo-accessibility-kit'),
            'manage_options',
            'exa11y-accessibility-general',
            array('EXA11Y_Page_Accessibility_General', 'display_page')
        );

        // Tools
        add_submenu_page(
            'exa11y',
            esc_html__('Tools', 'exa11y-expo-accessibility-kit'),
            esc_html__('Tools', 'exa11y-expo-accessibility-kit'),
            'manage_options',
            'exa11y-tools',
            array('EXA11Y_Page_Tools', 'display_page')
        );
    }
    
    /**
     * Add admin bar menu item for quick access
     */
    public function add_admin_bar_menu($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Add the main menu item
        $wp_admin_bar->add_node(array(
            'id'    => 'exa11y',
            'title' => '<span class="ab-icon dashicons dashicons-universal-access"></span>' . esc_html__('EXA11Y', 'exa11y-expo-accessibility-kit'),
            'href'  => '#',
            'meta'  => array(
                'title' => esc_html__('EXA11Y Options', 'exa11y-expo-accessibility-kit'),
            ),
        ));
        
        // Add Settings submenu item
        $wp_admin_bar->add_node(array(
            'id'     => 'exa11y-settings',
            'parent' => 'exa11y',
            'title'  => esc_html__('General Settings', 'exa11y-expo-accessibility-kit'),
            'href'   => esc_url(admin_url('admin.php?page=exa11y')),
            'meta'   => array(
                'title' => esc_html__('EXA11Y General Settings', 'exa11y-expo-accessibility-kit'),
                'class' => 'exa11y-admin-bar-item'
            ),
        ));
        
        // Add Accessibility Statement submenu item
        $wp_admin_bar->add_node(array(
            'id'     => 'exa11y-statement',
            'parent' => 'exa11y',
            'title'  => esc_html__('Accessibility Statement', 'exa11y-expo-accessibility-kit'),
            'href'   => esc_url(admin_url('admin.php?page=exa11y-accessibility-statement')),
            'meta'   => array(
                'title' => esc_html__('Manage Accessibility Statement', 'exa11y-expo-accessibility-kit'),
                'class' => 'exa11y-admin-bar-item'
            ),
        ));
        
        // Add Accessibility Features submenu item
        $wp_admin_bar->add_node(array(
            'id'     => 'exa11y-accessibility',
            'parent' => 'exa11y',
            'title'  => esc_html__('Accessibility Features', 'exa11y-expo-accessibility-kit'),
            'href'   => esc_url(admin_url('admin.php?page=exa11y-accessibility-general')),
            'meta'   => array(
                'title' => esc_html__('Manage Accessibility Features', 'exa11y-expo-accessibility-kit'),
                'class' => 'exa11y-admin-bar-item'
            ),
        ));
        
        // Add Clear Cache submenu item
        $wp_admin_bar->add_node(array(
            'id'     => 'exa11y-clear-cache',
            'parent' => 'exa11y',
            'title'  => esc_html__('Clear Cache', 'exa11y-expo-accessibility-kit'),
            'href'   => '#',
            'meta'   => array(
                'title' => esc_html__('Clear Plugin Cache', 'exa11y-expo-accessibility-kit'),
                'class' => 'exa11y-admin-bar-item'
            ),
        ));
        
        // Add Reset Everything submenu item
        $wp_admin_bar->add_node(array(
            'id'     => 'exa11y-reset-all',
            'parent' => 'exa11y',
            'title'  => esc_html__('Reset Everything', 'exa11y-expo-accessibility-kit'),
            'href'   => '#',
            'meta'   => array(
                'title' => esc_html__('Reset All Plugin Settings', 'exa11y-expo-accessibility-kit'),
                'class' => 'exa11y-admin-bar-item'
            ),
        ));
        
    }

    /**
     * Enqueue the admin-bar quick-action handlers (Clear Cache / Reset)
     * wherever the admin bar is visible to a capable user.
     */
    public function enqueue_admin_bar_scripts() {
        if (!current_user_can('manage_options') || !is_admin_bar_showing()) {
            return;
        }

        // Enqueue admin menu functions JavaScript
        wp_enqueue_script(
            'exa11y-admin-menu-functions',
            plugin_dir_url(dirname(__FILE__)) . 'js/admin-menu-functions.js',
            array('jquery'),
            EXA11Y_VERSION,
            true
        );
        
        // Localize script with strings and nonces
        wp_localize_script('exa11y-admin-menu-functions', 'exa11y_admin_menu', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonces' => array(
                'clear_cache' => wp_create_nonce('exa11y_admin_ajax'),
                'reset_settings' => wp_create_nonce('exa11y_admin_ajax')
            ),
            'strings' => array(
                'confirm_clear_cache' => esc_html__('Are you sure you want to clear all plugin cache? This will reset any cached settings for all users.', 'exa11y-expo-accessibility-kit'),
                'cache_cleared' => esc_html__('Plugin cache has been cleared successfully.', 'exa11y-expo-accessibility-kit'),
                'cache_clear_failed' => esc_html__('Failed to clear cache. Please try again.', 'exa11y-expo-accessibility-kit'),
                'confirm_reset' => esc_html__('Are you sure you want to reset all settings to default values? This cannot be undone.', 'exa11y-expo-accessibility-kit'),
                'settings_reset' => esc_html__('All settings have been reset successfully. The page will now reload.', 'exa11y-expo-accessibility-kit'),
                'reset_failed' => esc_html__('Failed to reset settings. Please try again.', 'exa11y-expo-accessibility-kit')
            )
        ));
    }
}

