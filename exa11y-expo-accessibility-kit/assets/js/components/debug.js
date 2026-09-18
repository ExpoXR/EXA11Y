/**
 * Debug Logger Module
 * Provides logging functionality with different levels that respect debug mode setting
 */

const debugLogger = {
    // wp_localize_script casts scalars to strings, so debugMode arrives as '1'/'0'.
    isEnabled: typeof window.exa11y !== 'undefined' && Number(window.exa11y.debugMode) === 1,
    
    log: function(message, data = null) {
        if (!this.isEnabled) return;
        
        if (data) {
            console.log(`[EXA11Y] ${message}`, data);
        } else {
            console.log(`[EXA11Y] ${message}`);
        }
    },
    
    info: function(message, data = null) {
        if (!this.isEnabled) return;
        
        if (data) {
            console.info(`[EXA11Y Info] ${message}`, data);
        } else {
            console.info(`[EXA11Y Info] ${message}`);
        }
    },
    
    warn: function(message, data = null) {
        if (!this.isEnabled) return;
        
        if (data) {
            console.warn(`[EXA11Y Warning] ${message}`, data);
        } else {
            console.warn(`[EXA11Y Warning] ${message}`);
        }
    },
    
    error: function(message, data = null) {
        if (!this.isEnabled) return;
        
        if (data) {
            console.error(`[EXA11Y Error] ${message}`, data);
        } else {
            console.error(`[EXA11Y Error] ${message}`);
        }
    },
    
    groupCollapsed: function(title) {
        if (!this.isEnabled) return;
        console.groupCollapsed(`[EXA11Y] ${title}`);
    },
    
    groupEnd: function() {
        if (!this.isEnabled) return;
        console.groupEnd();
    }
};

// Log initialization and settings on startup
if (debugLogger.isEnabled) {
    debugLogger.groupCollapsed('Plugin Initialization');
    debugLogger.info(`Version: ${exa11y.version}`);
    debugLogger.info('Plugin initialized with the following settings:', exa11y);
    
    debugLogger.info('Active features:');
    debugLogger.info(`- Font Size: ${exa11y.fontSizeActive ? 'Enabled' : 'Disabled'}`);
    debugLogger.info(`- Contrast: ${exa11y.contrastActive ? 'Enabled' : 'Disabled'}`);
    debugLogger.info(`- Saturation: ${exa11y.saturationActive ? 'Enabled' : 'Disabled'}`);
    debugLogger.info(`- Brightness: ${exa11y.brightnessActive ? 'Enabled' : 'Disabled'}`);
    debugLogger.info(`- Blue Filter: ${exa11y.blueFilterActive ? 'Enabled' : 'Disabled'}`);
    debugLogger.info(`- Gray Mode: ${exa11y.grayscaleActive ? 'Enabled' : 'Disabled'}`);
    
    debugLogger.info(`Icon Position: ${exa11y.position}`);
    debugLogger.groupEnd();
}

// Export the debugLogger for use in other modules
window.exa11yDebug = debugLogger;
