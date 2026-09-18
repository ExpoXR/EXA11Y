<?php
/**
 * Shared admin page template.
 *
 * Single source of truth for the chrome that wraps every EXA11Y settings
 * screen: the page header, intro block, section wrappers, and the footer.
 * Page classes render their body between EXA11Y_Admin_Page::header() and
 * EXA11Y_Admin_Page::footer() so all screens share one consistent layout
 * instead of each re-implementing it.
 *
 * @package EXA11Y
 * @subpackage Admin
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EXA11Y_Admin_Page
 *
 * Static rendering helpers for the standard admin page layout.
 */
class EXA11Y_Admin_Page {

    /**
     * Open a settings page: wrap, title, and intro.
     *
     * @param string $title Page title (already translated).
     * @param string $intro Optional intro paragraph (already translated).
     */
    public static function header($title, $intro = '') {
        echo '<div class="wrap exa11y-admin-wrap">';

        // Branded gradient header with version badge (ExploreXR style).
        echo '<div class="exa11y-admin-header">';
        echo '<div class="exa11y-logo">';
        echo '<h1>' . esc_html($title);
        echo ' <span class="exa11y-version">' . esc_html(EXA11Y_VERSION) . '</span>';
        echo '</h1>';
        echo '</div>';
        echo '<div class="exa11y-header-actions"></div>';
        echo '</div>';

        // Anchor for WordPress admin notices. Without it core's common.js
        // injects them after the first `.wrap h1`, which is inside the branded
        // header block above.
        echo '<hr class="wp-header-end">';

        self::quick_actions();

        if ('' !== $intro) {
            echo '<div class="exa11y-page-intro"><p>' . esc_html($intro) . '</p></div>';
        }

        // Surface the standard "settings saved" confirmation on every page.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only UI flag set by WordPress core after a settings save.
        if (isset($_GET['settings-updated']) && sanitize_text_field(wp_unslash($_GET['settings-updated']))) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'exa11y-expo-accessibility-kit') . '</p></div>';
        }
    }

    /**
     * Render the quick-actions navigation bar (ExploreXR style).
     * Links to the most-used EXA11Y screens.
     */
    public static function quick_actions() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only highlight of the current admin page.
        $current = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        // Order mirrors the registered WP admin submenu (admin-menu-pages.php::add_admin_menu())
        // so this in-page nav and the real sidebar menu never drift apart.
        $links = array(
            'exa11y'                         => array('dashicons-admin-home', __('General', 'exa11y-expo-accessibility-kit')),
            'exa11y-accessibility-statement' => array('dashicons-media-text', __('Statement', 'exa11y-expo-accessibility-kit')),
            'exa11y-accessibility-general'   => array('dashicons-universal-access-alt', __('Features', 'exa11y-expo-accessibility-kit')),
            'exa11y-tools'                   => array('dashicons-admin-tools', __('Tools', 'exa11y-expo-accessibility-kit')),
        );

        echo '<div class="exa11y-quick-actions">';
        foreach ($links as $slug => $link) {
            printf(
                '<a href="%1$s"%2$s><span class="dashicons %3$s"></span> %4$s</a>',
                esc_url(admin_url('admin.php?page=' . $slug)),
                $current === $slug ? ' class="active"' : '',
                esc_attr($link[0]),
                esc_html($link[1])
            );
        }
        echo '</div>';
    }

    /**
     * Close a settings page: branded ExpoXR family footer and the wrap element.
     */
    public static function footer() {
        echo '<div class="exa11y-admin-footer">';
        echo '<div class="exa11y-footer-content">';

        echo '<div class="exa11y-footer-branding">';
        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Plugin logo for admin footer.
        printf(
            '<img src="%s" alt="%s" class="exa11y-footer-logo" loading="lazy">',
            esc_url(EXA11Y_URL . 'assets/img/logos/ExpoXR-Logo.png'),
            esc_attr__('ExpoXR Logo', 'exa11y-expo-accessibility-kit')
        );
        echo '<p class="exa11y-footer-text">';
        echo esc_html__('EXA11Y is part of the', 'exa11y-expo-accessibility-kit') . ' <strong>' . esc_html__('ExpoXR Family', 'exa11y-expo-accessibility-kit') . '</strong> &mdash; ';
        echo esc_html__('accessibility solutions for the modern web', 'exa11y-expo-accessibility-kit');
        echo '</p>';
        echo '</div>';

        echo '<div class="exa11y-footer-links">';
        echo '<a href="https://expoxr.com" target="_blank" rel="noopener">' . esc_html__('Visit ExpoXR.com', 'exa11y-expo-accessibility-kit') . '</a>';
        echo '<a href="https://expoxr.com/a11ysuite/documentation/" target="_blank" rel="noopener">' . esc_html__('Documentation', 'exa11y-expo-accessibility-kit') . '</a>';
        echo '<a href="https://expoxr.com/support/" target="_blank" rel="noopener">' . esc_html__('Support', 'exa11y-expo-accessibility-kit') . '</a>';
        echo '</div>';

        echo '</div>'; // .exa11y-footer-content

        echo '<p class="exa11y-footer-credit">';
        printf(
            /* translators: %1$s: Plugin version, %2$s: Opening link tag, %3$s: Closing link tag */
            esc_html__('EXA11Y v%1$s | Developed by %2$sAyal Othman%3$s', 'exa11y-expo-accessibility-kit'),
            esc_html(EXA11Y_VERSION),
            '<a href="https://www.expoxr.com" target="_blank" rel="noopener">',
            '</a>'
        );
        echo '</p>';

        echo '</div>'; // .exa11y-admin-footer
        echo '</div>'; // .wrap.exa11y-admin-wrap
    }

    /**
     * Open a labeled category group wrapping one or more section cards.
     * Pair with category_group_close(). Purely presentational grouping used
     * to organize related sections under one heading.
     *
     * @param string $title Category heading (already translated).
     * @param string $icon  Optional dashicon class, e.g. 'dashicons-chart-bar'.
     */
    public static function category_group_open($title, $icon = '') {
        echo '<div class="exa11y-category-group">';
        echo '<h2 class="exa11y-category-heading">';
        if ('' !== $icon) {
            echo '<span class="dashicons ' . esc_attr($icon) . '" aria-hidden="true"></span> ';
        }
        echo esc_html($title) . '</h2>';
        echo '<div class="exa11y-category-body">';
    }

    /**
     * Close a category group opened with category_group_open().
     */
    public static function category_group_close() {
        echo '</div></div>'; // .exa11y-category-body, .exa11y-category-group
    }
}
