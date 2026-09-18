/**
 * Storage Module
 * Handles saving and retrieving user settings from localStorage
 */

const storageManager = {
    // Keys used for storing settings
    keys: {
        // Font settings
        fontSize: 'exa11y-font-size',
        lineHeight: 'exa11y-line-height',
        readableFont: 'exa11y-readable-font',
        
        // Visual filters
        contrast: 'exa11y-contrast',
        saturation: 'exa11y-saturation',
        brightness: 'exa11y-brightness',
        blueFilter: 'exa11y-blue-filter',
        grayscale: 'exa11y-grayscale',
        
        // CVD (Color Vision Deficiency) filters
        redWeakness: 'exa11y-red-weakness',
        greenWeakness: 'exa11y-green-weakness',
        blueWeakness: 'exa11y-blue-weakness',
          // Theme settings
        theme: 'exa11y-theme',
        lastUsedTheme: 'exa11y-last-used-theme',
        manualReset: 'exa11y-theme-manual-reset', // Flag for manual reset to original state
        
        // Content adjustments
        highlightLinks: 'exa11y-highlight-links',
        hideImages: 'exa11y-hide-images',
        
        // Accessibility features
        screenReader: 'exa11y-screen-reader',
        keyboardNavigation: 'exa11y-keyboard-nav'
    },

    // Save a setting to localStorage
    save: function(key, value) {
        if (this.keys[key]) {
            localStorage.setItem(this.keys[key], value);
            if (window.exa11yDebug) {
                window.exa11yDebug.log(`Saved setting: ${key} = ${value}`);
            }
        } else {
            if (window.exa11yDebug) {
                window.exa11yDebug.warn(`Attempted to save unknown setting key: ${key}`);
            }
        }
    },

    // Get a setting from localStorage
    get: function(key) {
        if (this.keys[key]) {
            return localStorage.getItem(this.keys[key]);
        }
        if (window.exa11yDebug) {
            window.exa11yDebug.warn(`Attempted to get unknown setting key: ${key}`);
        }
        return null;
    },

    // Remove a setting from localStorage
    remove: function(key) {
        if (this.keys[key]) {
            localStorage.removeItem(this.keys[key]);
            if (window.exa11yDebug) {
                window.exa11yDebug.log(`Removed setting: ${key}`);
            }
        } else {
            if (window.exa11yDebug) {
                window.exa11yDebug.warn(`Attempted to remove unknown setting key: ${key}`);
            }
        }
    },    // Clear all settings from localStorage
    clearAll: function() {
        // Clear all predefined keys
        Object.values(this.keys).forEach(key => {
            const oldValue = localStorage.getItem(key);
            if (oldValue !== null) {
                localStorage.removeItem(key);
                if (window.exa11yDebug) {
                    window.exa11yDebug.log(`Cleared setting: ${key} (was: ${oldValue})`);
                }
            }
        });
        
        if (window.exa11yDebug) {
            window.exa11yDebug.log('Cleared all accessibility settings - website should return to original appearance');
        }
    },

    // Get all active settings
    getActiveSettings: function() {
        const activeSettings = {};
        Object.entries(this.keys).forEach(([setting, key]) => {
            const value = localStorage.getItem(key);
            if (value) {
                activeSettings[setting] = value;
            }
        });
        return activeSettings;
    },
    
    // Check if any settings are active
    hasActiveSettings: function() {
        return Object.values(this.keys).some(key => localStorage.getItem(key) !== null);
    }
};

// Log active settings if debug is enabled
if (window.exa11yDebug) {
    const activeSettings = storageManager.getActiveSettings();
    window.exa11yDebug.info('Active user settings from localStorage:', activeSettings);
}

// Make the storage manager available globally
window.exa11yStorage = storageManager;
