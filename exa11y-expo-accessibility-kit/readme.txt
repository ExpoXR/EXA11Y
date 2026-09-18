=== EXA11Y - Expo Accessibility Kit ===
Contributors: expoxr
Tags: accessibility, a11y, wcag, compliance, disability
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free accessibility widget for WordPress: font scaling, color/contrast adjustments, and an accessibility statement page.

== Description ==

EXA11Y adds a floating accessibility widget to your site so visitors can adjust font size, contrast, color themes, and more to suit their needs — no configuration required to get useful defaults, but everything is adjustable from the WordPress admin.

EXA11Y is completely free, with every feature unlocked — no license key, no locked settings, no upgrade nags and nothing to buy inside the plugin.

= Key Features =

* **Font Scaling**: Adjustable font size, line height, and a readable-font toggle for easier reading
* **Color Adjustments**: Contrast, saturation, brightness, blue-light filter, gray mode, color-vision-deficiency support, and light/dark/high-contrast themes
* **Link & Image Helpers**: Highlight all links or hide images/videos for distraction-free reading
* **Accessibility Statement**: An auto-generated, fully editable accessibility statement page that reflects your enabled features
* **Appearance Controls**: Icon position, menu font size, hover text, and button colors

= Installation =

1. Upload the plugin files to the `/wp-content/plugins/exa11y-expo-accessibility-kit` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the EXA11Y menu in your WordPress admin to configure the plugin.
4. Customize accessibility features from the admin dashboard.

== Frequently Asked Questions ==

= Is this plugin free? =

Yes — EXA11Y is completely free, with every feature unlocked and no license key required.

= Is it WCAG compliant? =

The plugin provides tools to help improve WCAG compliance, but complete compliance depends on your content and theme.

= My settings are gone after updating to 1.0.2. Why? =

EXA11Y 1.0.2 moved every stored option to a new namespace and does not migrate the old values. Configure the plugin once on the EXA11Y admin screens and your settings will persist normally from then on. Visitors' saved widget preferences (font size, theme, filters) also reset to your configured defaults.

== Screenshots ==

1. Main accessibility controls panel
2. Font scaling and color adjustment options
3. Admin dashboard and settings

== Changelog ==

= 1.0.3 =
* Removed the build/minification step entirely. All JavaScript and CSS now ship exactly as authored, human-readable source — no `dist/` bundles, no esbuild dependency.
* Added a "Source Code" section to this readme documenting that no compiled assets are shipped.

= 1.0.2 =
* EXA11Y now uses its own `exa11y` namespace for every stored setting. Settings from older versions are not carried over and reset to defaults.
* Fixed: Hide Images and Highlight Links now remember their state between page loads.
* Fixed: the color theme script no longer loads twice when the color theme feature is enabled.
* Fixed: a JavaScript error that ran on every front-end page load.
* Fixed: the "Menu Text" font-size setting now applies to the widget's descriptive text.
* Fixed: the Accessibility Statement preview now shows the statement that will be generated instead of placeholder text.
* Fixed: admin notices now appear below the plugin header instead of inside its title.
* Clearing the plugin cache no longer flushes the entire site object cache.
* Removed the Upgrade screen and the Debug Log viewer.
* Internal cleanup: removed unused code, dead styles, and debug logging that ran on every admin request.

= 1.0.1 =
* Enqueue Tools page JS properly instead of an inline script tag.
* Escape appearance-color values before they reach inline CSS.
* Remove unconsented attribution from user-facing pages.
* Stop bundling translation files (handled via translate.wordpress.org).
* Remove code that modified the active_plugins option without user action.

= 1.0.0 =
* Initial release: font scaling, color/contrast adjustments, link and image helpers, accessibility statement generator, and appearance controls.

== Upgrade Notice ==

= 1.0.2 =
Settings from earlier versions are NOT carried over — you will need to configure EXA11Y again once after updating.

= 1.0.1 =
Compliance and security fixes; no settings changes needed.

= 1.0.0 =
Initial release.

== Support ==

For support, please visit our website at https://www.expoxr.com or contact us through the plugin support forum.

== Source Code ==

This plugin does not use a build step or minification — every JavaScript and CSS file it enqueues ships exactly as authored, in human-readable form (see `assets/js/components/`, `assets/css/components/`, `admin/js/`, and `admin/css/` in this package).

== Privacy Policy ==

This plugin does not collect, store, or transmit any usage statistics or personal data.
