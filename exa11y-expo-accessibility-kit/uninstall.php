<?php
/**
 * Uninstall script for EXA11Y.
 *
 * Runs when the plugin is deleted from the WordPress admin. Removes everything
 * the plugin writes: the generated accessibility statement page and the three
 * options it owns. Nothing else is stored, so no direct database queries are
 * needed here.
 *
 * @package EXA11Y
 */

// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the generated accessibility statement page. Must run BEFORE the
// options are removed below, or the page ID is lost.
$exa11y_statement_page_id = get_option('exa11y_accessibility_statement_page_id');
if ($exa11y_statement_page_id) {
    wp_delete_post((int) $exa11y_statement_page_id, true);
}

foreach (array('exa11y_settings', 'exa11y_settings_backup', 'exa11y_accessibility_statement_page_id') as $exa11y_option) {
    delete_option($exa11y_option);
}
