/**
 * exa11y Frontend JavaScript
 * 
 * This is the main JavaScript file that coordinates all component modules
 * to improve maintainability and organization.
 */

// Function to apply menu font sizes
function applyMenuFontSizes() {
    // Check if we have all required dependencies
    if (!window.exa11y || !exa11y.menuFontSizes) {
        if (window.exa11yDebug) {
            window.exa11yDebug.warn('Unable to apply menu font sizes - settings not found');
        }
        return;
    }

    // Set the CSS variables at the document root level for global access
    const root = document.documentElement;
      // Apply menu font sizes using CSS variables
    root.style.setProperty('--exa11y-menu-header-font-size', `${exa11y.menuFontSizes.header}px`);
    root.style.setProperty('--exa11y-menu-subheader-font-size', `${exa11y.menuFontSizes.subheader}px`);
    root.style.setProperty('--exa11y-menu-buttons-font-size', `${exa11y.menuFontSizes.buttons}px`);
    root.style.setProperty('--exa11y-menu-text-font-size', `${exa11y.menuFontSizes.text}px`);
    root.style.setProperty('--exa11y-menu-elements-font-size', `${exa11y.menuFontSizes.elements}px`);

    if (window.exa11yDebug) {
        window.exa11yDebug.log('Applied menu font sizes:', exa11y.menuFontSizes);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Check if settings are being properly received
    if (typeof exa11y === 'undefined') {
        return;
    }

    // Apply menu font sizes when DOM is ready
    applyMenuFontSizes();

    // Initialize core functionality in sequence for proper dependencies
    
    // 1. Debug Logger (already self-initializes)
    // debugLogger is now available as window.exa11yDebug
    
    // 2. Setup content wrapper
    if (window.exa11ySetupContentWrapper) {
        window.exa11ySetupContentWrapper();
    }
    
    // 3. Initialize keyboard support
    if (window.exa11y && window.exa11y.KeyboardNavigation) {
        window.exa11y.KeyboardNavigation.init();
    }
      // 4. Initialize screen reader support
    if (window.exa11y && window.exa11y.ScreenReader) {
        window.exa11y.ScreenReader.init();
    }

    // 5. Initialize themes (Color Theme functionality) - only if enabled
    if (window.exa11yThemes && typeof exa11y !== 'undefined' && exa11y.colorThemeActive) {
        window.exa11yThemes.init();
    }
});
