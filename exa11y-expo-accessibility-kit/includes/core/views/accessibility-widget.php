<?php
/**
 * Frontend accessibility widget template (EyeAble-style tile grid).
 *
 * Rendered by EXA11Y_Accessibility::add_accessibility_controls(), which
 * resolves every visibility flag before including this file. Expected
 * variables: $settings plus the $exa11y_show_* / $exa11y_group_* flags. This
 * template must not recompute them.
 *
 * Layout: a floating trigger opens a dialog panel containing feature groups.
 * Each group is a responsive grid of tiles. Two tile kinds:
 *   - Toggle tile  <button class="exa11y-tile exa11y-btn-*">  (on/off)
 *   - Stepper tile <div class="exa11y-tile exa11y-tile-stepper"
 *                       data-control="..." data-levels="..."> (graded)
 * Toggle tiles reuse the existing feature button classes so the feature JS
 * (highlightLinks/hideImages/fontSettings/themes) binds to them unchanged.
 * Stepper tiles are driven generically by controls.js (exa11y:stepper
 * events); fontSettings.js and filters.js apply the values.
 *
 * Markup (ids, classes, aria attributes) is a public contract - themes and
 * user CSS target it - so change it deliberately.
 *
 * @package EXA11Y
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Echo an inline SVG icon (24x24, currentColor) for a widget feature.
 * Inline keeps the widget dependency-free (no icon font / CDN). Output is
 * escaped with wp_kses against a fixed SVG allow-list; the markup itself is
 * static and developer-defined (no user input).
 */
if (!function_exists('exa11y_render_icon')) {
    function exa11y_render_icon($name) {
        $icons = array(
            'accessibility' => '<path d="M12 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm9 5.29c-.27-.78-1.13-1.2-1.92-.94-.03.01-3.02 1.15-7.08 1.15S5.95 6.36 5.92 6.35c-.79-.26-1.65.16-1.92.94-.26.79.16 1.65.94 1.92.13.04 2.34.83 4.81 1.09v3.11l-2.42 6.65c-.28.78.12 1.64.9 1.92.78.28 1.64-.12 1.92-.9L12 16.85l1.85 5.09c.22.61.79.98 1.41.98.16 0 .34-.03.51-.09.78-.28 1.18-1.14.9-1.92l-2.42-6.65v-3.11c2.47-.26 4.68-1.05 4.81-1.09.78-.27 1.2-1.13.93-1.92z"/>',
            'font-size'     => '<path d="M3 18h6v-2H3v2zM3 6v2h18V6H3zm0 7h12v-2H3v2z"/><path d="M16.5 10.5 19 18h1.6l-2.9-9h-1.4l-2.9 9H15l2.5-7.5z" opacity=".9"/>',
            'line-height'   => '<path d="M6 7h15v2H6V7zm0 4h15v2H6v-2zm0 4h15v2H6v-2zM2 6l2.5-3L7 6H5v12h2l-2.5 3L2 18h1V6H2z"/>',
            'readable-font' => '<path d="M5 4v3h5.5v12h3V7H19V4H5z"/>',
            'highlight-links' => '<path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/>',
            'hide-images'   => '<path d="M21 5v10.59l-2-2V5H7.41l-2-2H19c1.1 0 2 .9 2 2zM2.81 2.81 1.39 4.22 3 5.83V19c0 1.1.9 2 2 2h13.17l1.61 1.61 1.42-1.42L2.81 2.81zM5 19V7.83l4.88 4.88-1.13 1.42L11 17H5l1.5-2 .34.42L5 19z"/>',
            'contrast'      => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18V4c4.41 0 8 3.59 8 8s-3.59 8-8 8z"/>',
            'saturation'    => '<path d="M12 3.77 5.5 10.2a9.19 9.19 0 0 0 0 12.8 9.02 9.02 0 0 0 13 0 9.19 9.19 0 0 0 0-12.8L12 3.77zm0 15.9V6.6l4.09 4.05a6.4 6.4 0 0 1 0 9.02c-1.13 1.12-2.61 1.68-4.09 1.68v-1.68z"/>',
            'brightness'    => '<path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0-5-2 2h4l-2-2zM4 6l1 3 2-2-3-1zm16 0-3 1 2 2 1-3zM2 12l2 2v-4l-2 2zm20 0-2-2v4l2-2zM4 18l3-1-2-2-1 3zm16 0-1-3-2 2 3 1zm-8 4 2-2h-4l2 2z"/>',
            'blue-filter'   => '<path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.39 5.39 0 0 1-4.4 2.26 5.4 5.4 0 0 1-5.4-5.4c0-1.81.89-3.42 2.26-4.4-.44-.06-.9-.1-1.36-.1z"/>',
            'grayscale'     => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 2c1.85 0 3.55.63 4.9 1.69L5.69 16.9A7.95 7.95 0 0 1 4 12c0-4.41 3.59-8 8-8zm0 16a7.95 7.95 0 0 1-4.9-1.69L18.31 7.1A7.95 7.95 0 0 1 20 12c0 4.41-3.59 8-8 8z"/>',
            'cvd'           => '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>',
            'theme-light'   => '<path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0-5-2 2h4l-2-2zM4 6l1 3 2-2-3-1zm16 0-3 1 2 2 1-3zM2 12l2 2v-4l-2 2zm20 0-2-2v4l2-2zM4 18l3-1-2-2-1 3zm16 0-1-3-2 2 3 1zm-8 4 2-2h-4l2 2z"/>',
            'theme-dark'    => '<path d="M9.37 5.51A7.35 7.35 0 0 0 9.1 7.5c0 4.08 3.32 7.4 7.4 7.4.68 0 1.34-.09 1.99-.27A7.014 7.014 0 0 1 12 19c-3.86 0-7-3.14-7-7 0-2.93 1.81-5.45 4.37-6.49z"/>',
            'theme-contrast' => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18V4c4.41 0 8 3.59 8 8s-3.59 8-8 8z"/>',
            'statement'     => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
            'reset'         => '<path d="M17.65 6.35A7.958 7.958 0 0 0 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08A5.99 5.99 0 0 1 12 18c-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>',
        );
        $path = isset($icons[$name]) ? $icons[$name] : '';
        $svg  = '<svg class="exa11y-tile-icon" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false" fill="currentColor">' . $path . '</svg>';
        echo wp_kses($svg, array(
            'svg'  => array('class' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'aria-hidden' => true, 'focusable' => true, 'fill' => true, 'xmlns' => true),
            'path' => array('d' => true, 'opacity' => true, 'fill' => true),
        ));
    }
}

$exa11y_controls_classes = 'exa11y-accessibility-controls';
if (!empty($settings['hide_on_mobile'])) {
    $exa11y_controls_classes .= ' exa11y-hide-on-mobile';
}
$exa11y_icon_hover_text = isset($settings['icon_hover_text']) ? $settings['icon_hover_text'] : esc_html__('Open Accessibility Menu', 'exa11y-expo-accessibility-kit');
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals
?>
<div id="exa11y-accessibility-controls" class="<?php echo esc_attr($exa11y_controls_classes); ?>">
    <button type="button" class="exa11y-accessibility-toggle" aria-label="<?php echo esc_attr($exa11y_icon_hover_text); ?>" aria-expanded="false" aria-controls="exa11y-accessibility-panel" title="<?php echo esc_attr($exa11y_icon_hover_text); ?>">
        <svg class="exa11y-toggle-svg" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false" fill="currentColor">
            <path d="M12 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm9 5.29c-.27-.78-1.13-1.2-1.92-.94-.03.01-3.02 1.15-7.08 1.15S5.95 6.36 5.92 6.35c-.79-.26-1.65.16-1.92.94-.26.79.16 1.65.94 1.92.13.04 2.34.83 4.81 1.09v3.11l-2.42 6.65c-.28.78.12 1.64.9 1.92.78.28 1.64-.12 1.92-.9L12 16.85l1.85 5.09c.22.61.79.98 1.41.98.16 0 .34-.03.51-.09.78-.28 1.18-1.14.9-1.92l-2.42-6.65v-3.11c2.47-.26 4.68-1.05 4.81-1.09.78-.27 1.2-1.13.93-1.92z"/>
        </svg>
    </button>

    <div class="exa11y-accessibility-panel" id="exa11y-accessibility-panel" role="dialog" aria-modal="false" aria-label="<?php esc_attr_e('Accessibility settings', 'exa11y-expo-accessibility-kit'); ?>" aria-hidden="true">
        <header class="exa11y-panel-header">
            <h2 class="exa11y-panel-title"><?php esc_html_e('Accessibility', 'exa11y-expo-accessibility-kit'); ?></h2>
            <button type="button" class="exa11y-panel-close" aria-label="<?php esc_attr_e('Close accessibility menu', 'exa11y-expo-accessibility-kit'); ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" fill="currentColor"><path d="M18.3 5.71 12 12.01l-6.3-6.3-1.4 1.41 6.29 6.3-6.3 6.29 1.42 1.42 6.29-6.3 6.3 6.3 1.41-1.42-6.3-6.29 6.3-6.3z"/></svg>
            </button>
        </header>

        <div class="exa11y-panel-body">

        <?php if ($exa11y_group_content): ?>
        <section class="exa11y-group" data-group="content" aria-labelledby="exa11y-group-content">
            <h3 class="exa11y-group-title" id="exa11y-group-content"><?php esc_html_e('Content & Reading', 'exa11y-expo-accessibility-kit'); ?></h3>
            <div class="exa11y-grid" role="group" aria-labelledby="exa11y-group-content">

                <?php if ($exa11y_show_font_size): ?>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="fontSize" data-levels="1,1.25,1.5,1.75,2,2.5" data-unit="x" data-default="1">
                    <?php exa11y_render_icon('font-size'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-fontSize"><?php esc_html_e('Font Size', 'exa11y-expo-accessibility-kit'); ?></span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease font size', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-fontSize">1x</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase font size', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <?php endif; ?>

                <?php if ($exa11y_show_line_height): ?>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="lineHeight" data-levels="1,1.5,2" data-unit="x" data-default="1">
                    <?php exa11y_render_icon('line-height'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-lineHeight"><?php esc_html_e('Line Height', 'exa11y-expo-accessibility-kit'); ?></span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease line height', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-lineHeight">1x</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase line height', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <?php endif; ?>

                <?php if ($exa11y_show_readable): ?>
                <button type="button" class="exa11y-tile exa11y-tile-toggle exa11y-btn-readable-font" aria-pressed="false" aria-label="<?php esc_attr_e('Toggle readable font', 'exa11y-expo-accessibility-kit'); ?>">
                    <?php exa11y_render_icon('readable-font'); ?>
                    <span class="exa11y-tile-label"><?php esc_html_e('Readable Font', 'exa11y-expo-accessibility-kit'); ?></span>
                </button>
                <?php endif; ?>

                <?php if ($exa11y_show_highlight_links): ?>
                <button type="button" class="exa11y-tile exa11y-tile-toggle exa11y-btn-highlight-links" aria-pressed="false" aria-label="<?php esc_attr_e('Highlight all links on the page', 'exa11y-expo-accessibility-kit'); ?>">
                    <?php exa11y_render_icon('highlight-links'); ?>
                    <span class="exa11y-tile-label"><?php esc_html_e('Highlight Links', 'exa11y-expo-accessibility-kit'); ?></span>
                </button>
                <?php endif; ?>

                <?php if ($exa11y_show_hide_images): ?>
                <button type="button" class="exa11y-tile exa11y-tile-toggle exa11y-btn-hide-images" aria-pressed="false" aria-label="<?php esc_attr_e('Hide all images and videos on the page', 'exa11y-expo-accessibility-kit'); ?>">
                    <?php exa11y_render_icon('hide-images'); ?>
                    <span class="exa11y-tile-label"><?php esc_html_e('Hide Images', 'exa11y-expo-accessibility-kit'); ?></span>
                </button>
                <?php endif; ?>

            </div>
        </section>
        <?php endif; ?>

        <?php if ($exa11y_group_color): ?>
        <section class="exa11y-group" data-group="color" aria-labelledby="exa11y-group-color">
            <h3 class="exa11y-group-title" id="exa11y-group-color"><?php esc_html_e('Color & Contrast', 'exa11y-expo-accessibility-kit'); ?></h3>
            <div class="exa11y-grid" role="group" aria-labelledby="exa11y-group-color">

                <?php if ($exa11y_show_contrast): ?>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="contrast" data-min="100" data-max="200" data-stepsize="10" data-unit="%" data-default="100">
                    <?php exa11y_render_icon('contrast'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-contrast"><?php esc_html_e('Contrast', 'exa11y-expo-accessibility-kit'); ?></span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease contrast', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-contrast">100%</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase contrast', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <?php endif; ?>

                <?php if ($exa11y_show_saturation): ?>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="saturation" data-min="100" data-max="200" data-stepsize="10" data-unit="%" data-default="100">
                    <?php exa11y_render_icon('saturation'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-saturation"><?php esc_html_e('Saturation', 'exa11y-expo-accessibility-kit'); ?></span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease saturation', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-saturation">100%</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase saturation', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <?php endif; ?>

                <?php if ($exa11y_show_brightness): ?>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="brightness" data-min="100" data-max="200" data-stepsize="10" data-unit="%" data-default="100">
                    <?php exa11y_render_icon('brightness'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-brightness"><?php esc_html_e('Brightness', 'exa11y-expo-accessibility-kit'); ?></span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease brightness', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-brightness">100%</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase brightness', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <?php endif; ?>

                <?php if ($exa11y_show_blue_filter): ?>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="blueFilter" data-min="0" data-max="100" data-stepsize="10" data-unit="%" data-default="0">
                    <?php exa11y_render_icon('blue-filter'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-blueFilter"><?php esc_html_e('Blue Light Filter', 'exa11y-expo-accessibility-kit'); ?></span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease blue light filter', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-blueFilter">0%</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase blue light filter', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <?php endif; ?>

                <?php if ($exa11y_show_grayscale): ?>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="grayscale" data-min="0" data-max="100" data-stepsize="10" data-unit="%" data-default="0">
                    <?php exa11y_render_icon('grayscale'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-grayscale"><?php esc_html_e('Grayscale', 'exa11y-expo-accessibility-kit'); ?></span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease grayscale', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-grayscale">0%</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase grayscale', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <?php endif; ?>

                <?php if ($exa11y_show_cvd): ?>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="redWeakness" data-cvd="1" data-min="0" data-max="100" data-stepsize="25" data-unit="%" data-default="0">
                    <?php exa11y_render_icon('cvd'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-redWeakness">
                        <span class="exa11y-tile-label-text"><?php esc_html_e('Red-weakness', 'exa11y-expo-accessibility-kit'); ?></span>
                        <span class="exa11y-tile-label-sub"><?php esc_html_e('(Protanomaly)', 'exa11y-expo-accessibility-kit'); ?></span>
                    </span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease red-weakness filter', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-redWeakness">0%</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase red-weakness filter', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="greenWeakness" data-cvd="1" data-min="0" data-max="100" data-stepsize="25" data-unit="%" data-default="0">
                    <?php exa11y_render_icon('cvd'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-greenWeakness">
                        <span class="exa11y-tile-label-text"><?php esc_html_e('Green-weakness', 'exa11y-expo-accessibility-kit'); ?></span>
                        <span class="exa11y-tile-label-sub"><?php esc_html_e('(Deuteranomaly)', 'exa11y-expo-accessibility-kit'); ?></span>
                    </span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease green-weakness filter', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-greenWeakness">0%</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase green-weakness filter', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <div class="exa11y-tile exa11y-tile-stepper" data-control="blueWeakness" data-cvd="1" data-min="0" data-max="100" data-stepsize="25" data-unit="%" data-default="0">
                    <?php exa11y_render_icon('cvd'); ?>
                    <span class="exa11y-tile-label" id="exa11y-label-blueWeakness">
                        <span class="exa11y-tile-label-text"><?php esc_html_e('Blue-weakness', 'exa11y-expo-accessibility-kit'); ?></span>
                        <span class="exa11y-tile-label-sub"><?php esc_html_e('(Tritanomaly)', 'exa11y-expo-accessibility-kit'); ?></span>
                    </span>
                    <div class="exa11y-stepper">
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-dec" data-step="dec" aria-label="<?php esc_attr_e('Decrease blue-weakness filter', 'exa11y-expo-accessibility-kit'); ?>">&minus;</button>
                        <span class="exa11y-stepper-value" aria-live="polite" aria-labelledby="exa11y-label-blueWeakness">0%</span>
                        <button type="button" class="exa11y-stepper-btn exa11y-stepper-inc" data-step="inc" aria-label="<?php esc_attr_e('Increase blue-weakness filter', 'exa11y-expo-accessibility-kit'); ?>">&plus;</button>
                    </div>
                    <span class="exa11y-stepper-track" aria-hidden="true"><span class="exa11y-stepper-fill"></span></span>
                </div>
                <?php endif; ?>

                <?php if ($exa11y_show_color_theme): ?>
                <button type="button" class="exa11y-tile exa11y-tile-toggle exa11y-btn-theme exa11y-btn-theme-light" data-theme="light" aria-pressed="false" aria-label="<?php esc_attr_e('Switch to light theme', 'exa11y-expo-accessibility-kit'); ?>">
                    <?php exa11y_render_icon('theme-light'); ?>
                    <span class="exa11y-tile-label"><?php esc_html_e('Light', 'exa11y-expo-accessibility-kit'); ?></span>
                </button>
                <button type="button" class="exa11y-tile exa11y-tile-toggle exa11y-btn-theme exa11y-btn-theme-dark" data-theme="dark" aria-pressed="false" aria-label="<?php esc_attr_e('Switch to dark theme', 'exa11y-expo-accessibility-kit'); ?>">
                    <?php exa11y_render_icon('theme-dark'); ?>
                    <span class="exa11y-tile-label"><?php esc_html_e('Dark', 'exa11y-expo-accessibility-kit'); ?></span>
                </button>
                <button type="button" class="exa11y-tile exa11y-tile-toggle exa11y-btn-theme exa11y-btn-theme-high-contrast" data-theme="high-contrast" aria-pressed="false" aria-label="<?php esc_attr_e('Switch to high contrast theme', 'exa11y-expo-accessibility-kit'); ?>">
                    <?php exa11y_render_icon('theme-contrast'); ?>
                    <span class="exa11y-tile-label"><?php esc_html_e('High Contrast', 'exa11y-expo-accessibility-kit'); ?></span>
                </button>
                <?php endif; ?>

            </div>
        </section>
        <?php endif; ?>

        <?php if ($exa11y_group_nav): ?>
        <section class="exa11y-group" data-group="navigation" aria-labelledby="exa11y-group-nav">
            <h3 class="exa11y-group-title" id="exa11y-group-nav"><?php esc_html_e('Navigation & Info', 'exa11y-expo-accessibility-kit'); ?></h3>
            <div class="exa11y-grid" role="group" aria-labelledby="exa11y-group-nav">

                <?php if ($exa11y_show_statement): ?>
                <a href="<?php echo esc_url(get_permalink($settings['accessibility_statement_page'])); ?>" class="exa11y-tile exa11y-tile-link exa11y-btn-statement" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('View accessibility statement (opens in new tab)', 'exa11y-expo-accessibility-kit'); ?>">
                    <?php exa11y_render_icon('statement'); ?>
                    <span class="exa11y-tile-label"><?php esc_html_e('Statement', 'exa11y-expo-accessibility-kit'); ?></span>
                </a>
                <?php endif; ?>

            </div>
        </section>
        <?php endif; ?>

        </div><!-- .exa11y-panel-body -->

        <footer class="exa11y-panel-footer">
            <button type="button" class="exa11y-btn-reset" aria-label="<?php esc_attr_e('Reset all settings', 'exa11y-expo-accessibility-kit'); ?>">
                <?php exa11y_render_icon('reset'); ?>
                <span><?php esc_html_e('Reset all settings', 'exa11y-expo-accessibility-kit'); ?></span>
            </button>
        </footer>
    </div>
</div>
