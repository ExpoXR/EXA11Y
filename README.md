# EXA11Y (v1.0.3)

## Description
EXA11Y is a free, standalone WordPress accessibility plugin. It adds a floating accessibility widget so visitors can adjust font size, contrast, color themes, and more — with sensible defaults out of the box and full control from the WordPress admin. Every feature is unlocked; there is no license system and nothing locked behind a paid tier.

ExpoXR also offers **A11ySuite Cloud**, a separate SaaS platform for freelancers and agencies managing accessibility auditing across multiple sites. It's a different product with no code dependency on this plugin.

## Features

- **Accessibility**
  - Accessibility Statement: Dynamically generated content based on your enabled features. The page is a standard WordPress page, styleable via your theme, the block editor, or page builders. Use the "Update Statement Content" button to refresh the plugin-generated text after changing settings.
  - Highlight Links for better visibility and readability
  - Hide Images and Videos for distraction-free reading
  - Customizable accessibility icon hover text and position
- **Font Settings**
  - Font size scaling with precise control (1x, 1.25x, 1.5x, 1.75x, 2x, 2.5x)
  - Line height adjustment (1x, 1.5x, 2x)
  - Readable font option for improved text clarity
- **Color Settings**
  - Color Theme toggle (Light/Dark/High Contrast)
  - Contrast, saturation, and brightness adjustment
  - Blue light filter for reduced eye strain
  - Gray mode
  - Color vision deficiency support (red/green/blue-weakness filters)
- **Appearance**
  - Icon position, menu font size, hover text, and button colors
- **Language Support** — translation-ready (every string wrapped for i18n); community translations come from translate.wordpress.org, no `.mo`/`.po` files are bundled
- **Developer-Friendly**
  - Security: all database operations use prepared statements
  - Modular component-based architecture for CSS, JavaScript, and PHP
  - Settings export/import and maintenance tools (cache clear, reset to defaults) in the admin Tools page
  - No build step — all JS/CSS ships as authored, human-readable source

## Installation
1. Upload the `exa11y-expo-accessibility-kit` folder to the `/wp-content/plugins/` directory, or install through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin settings in the WordPress admin to configure accessibility options.

## Usage
Once activated, the plugin adds an admin page where you can enable or disable various accessibility features. On the frontend, users will see an accessibility controls button that opens a panel with all enabled accessibility options.

### Accessibility Statement Management
The plugin can create and populate an "Accessibility Statement" page for your site, based on the accessibility features you enable and the information you provide in the plugin settings (organization name, contact details, etc.).
- **Styling**: The appearance of the page (fonts, colors, layout beyond the plugin-generated text) can be customized using your theme, the block editor, or your preferred page builder, just like any other WordPress page.
- **Content Updates**: Use the "Update Statement Content" button in the plugin's admin area to regenerate the text of the statement after changing accessibility settings. *Manual edits to the text content will be overwritten by this process; structural changes from a page builder should generally be preserved.*

### Maintenance & Debugging
1. **Export/Import Settings**: back up or transfer your configuration between sites from the Tools page.
2. **Reset**: reset all settings, or just the accessibility features, to defaults from the Tools page.
3. **Cache**: clear the plugin's cached settings from the Tools page.
4. **PHP-level logging**: enable `WP_DEBUG_LOG` in `wp-config.php` and check `wp-content/debug.log` for plugin errors — there is no in-admin log viewer.

## Code Structure

See [STRUCTURE.md](STRUCTURE.md) for the complete file layout. Briefly:

- **`includes/core/`** — `class-accessibility.php` (frontend enqueue + widget), `class-settings-defaults.php` (canonical defaults), `class-debug.php`.
- **`admin/components/`** — settings registration, validation, field rendering, AJAX handlers, menu registration.
- **`admin/pages/`** — one file per admin screen.
- **`assets/`** — component-based CSS and JS, enqueued directly as authored source (no build step).

## Development

Front-end and admin assets are authored as individual component files and enqueued directly — no bundling, no minification. This keeps every file the plugin serves human-readable in place, which is also a WordPress.org compliance requirement for any plugin shipping compiled/compressed assets.

PHP is used only by `npm run lint` and the optional `npm run build:i18n`.

## Support
For support, please visit https://www.expoxr.com or use the plugin support forum on WordPress.org.

## Troubleshooting
If you encounter issues:

1. Enable `WP_DEBUG_LOG` in `wp-config.php` and check `wp-content/debug.log` for PHP errors.
2. Check the browser console (F12 in most browsers) for JavaScript errors.
3. Use the "Reset All Settings" button on the Tools page before filing a support request.
4. Clear local storage if experiencing persistent font settings issues (try the Reset button first).
