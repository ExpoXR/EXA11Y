/**
 * Themes Module
 * Handles color theme switching (light / dark / high-contrast) with automatic
 * theme detection. Theme tiles reuse the .exa11y-btn-theme class; this module
 * keeps their .active class and aria-pressed state in sync.
 */

(function($) {
    /**
     * Return the shared storage manager, or a minimal localStorage-backed shim
     * covering the theme keys if storage.js failed to load.
     */
    function getStorage() {
        return window.exa11yStorage || {
            keys: {
                theme: 'exa11y-theme',
                lastUsedTheme: 'exa11y-last-used-theme',
                manualReset: 'exa11y-theme-manual-reset'
            },
            save: function(key, value) { localStorage.setItem(this.keys[key] || key, value); },
            get: function(key) { return localStorage.getItem(this.keys[key] || key); },
            remove: function(key) { localStorage.removeItem(this.keys[key] || key); }
        };
    }

    // Mark the given theme tile active (pressed) and clear the others.
    function setActiveThemeTile(theme) {
        $('.exa11y-btn-theme').removeClass('active').attr('aria-pressed', 'false');
        $('.exa11y-btn-theme-' + theme).addClass('active').attr('aria-pressed', 'true');
    }

    const themes = {
        init: function() {
            if (typeof exa11y !== 'undefined' && exa11y.colorThemeActive) {
                if (window.exa11yDebug) {
                    window.exa11yDebug.log('Initializing Color Theme functionality');
                    window.exa11yDebug.log('Color Theme Default Setting:', exa11y.colorThemeDefault);
                }
                this.initThemeButtons();
                this.restoreOrDetectTheme();
            } else if (window.exa11yDebug) {
                if (typeof exa11y === 'undefined') {
                    window.exa11yDebug.log('Color Theme not initialized: exa11y object not found');
                } else if (!exa11y.colorThemeActive) {
                    window.exa11yDebug.log('Color Theme not initialized: feature disabled in admin');
                }
            }
        },

        // Initialize theme toggle tiles.
        initThemeButtons: function() {
            if (typeof $ === 'undefined') {
                return;
            }

            const themeButtons = $('.exa11y-btn-theme');
            if (themeButtons.length === 0) {
                if (window.exa11yDebug) {
                    window.exa11yDebug.error('No theme buttons found in DOM');
                }
                return;
            }

            themeButtons.on('click', function(e) {
                e.preventDefault();
                const theme = $(this).attr('data-theme');

                if (window.exa11yDebug) {
                    window.exa11yDebug.log(`Theme button clicked: ${theme}`);
                }
                if (!theme) {
                    return;
                }

                $('body').addClass('exa11y-theme-transition');
                $('body').removeClass('exa11y-theme-light exa11y-theme-dark exa11y-theme-high-contrast');
                setActiveThemeTile(theme);

                const storage = getStorage();
                if (theme !== 'auto') {
                    if (theme !== 'light') {
                        $('body').addClass('exa11y-theme-' + theme);
                    }
                    storage.save('theme', theme);
                } else {
                    const self = window.exa11yThemes;
                    const detectedTheme = self ? self.detectWebsiteTheme() : 'light';
                    if (detectedTheme !== 'light') {
                        $('body').addClass('exa11y-theme-' + detectedTheme);
                    }
                    storage.save('theme', 'auto');
                }

                setTimeout(function() {
                    $('body').removeClass('exa11y-theme-transition');
                }, 300);
            });
        },

        // Restore theme from storage or detect automatically.
        restoreOrDetectTheme: function() {
            const storage = getStorage();

            const manualReset = storage.get('manualReset');
            if (manualReset === 'true') {
                setActiveThemeTile('light');
                storage.remove('manualReset');
                return;
            }

            const savedTheme = storage.get('theme');
            const adminDefault = (typeof exa11y !== 'undefined' && exa11y.colorThemeDefault)
                ? exa11y.colorThemeDefault
                : 'auto';

            if (savedTheme && savedTheme !== 'auto') {
                if (savedTheme !== 'light') {
                    $('body').addClass('exa11y-theme-' + savedTheme);
                }
                setActiveThemeTile(savedTheme);
                if (window.exa11yDebug) {
                    window.exa11yDebug.log(`Restored saved theme: ${savedTheme}`);
                }
            } else {
                let finalTheme;
                if (adminDefault === 'auto') {
                    finalTheme = this.detectWebsiteTheme();
                } else if (adminDefault === 'system') {
                    finalTheme = this.detectSystemPreference();
                } else {
                    finalTheme = adminDefault;
                }

                if (finalTheme !== 'light') {
                    $('body').addClass('exa11y-theme-' + finalTheme);
                }
                setActiveThemeTile(finalTheme);
            }
        },

        // Detect the website's theme automatically.
        detectWebsiteTheme: function() {
            try {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    return 'dark';
                }

                const bodyBgColor = this.getComputedBackgroundColor(document.body);
                const htmlBgColor = this.getComputedBackgroundColor(document.documentElement);

                const darkThemeSelectors = [
                    'body.dark', 'html.dark', '.dark-mode', '.dark-theme',
                    'body[data-theme="dark"]', 'html[data-theme="dark"]',
                    'body.theme-dark', 'html.theme-dark'
                ];

                let isDarkMode = false;
                darkThemeSelectors.forEach(selector => {
                    if (document.querySelector(selector)) {
                        isDarkMode = true;
                    }
                });
                if (isDarkMode) {
                    return 'dark';
                }

                const backgroundColor = bodyBgColor || htmlBgColor || '#ffffff';
                const luminance = this.getColorLuminance(backgroundColor);
                if (luminance < 0.5) {
                    return 'dark';
                }

                const wpDarkClasses = [
                    '.wp-dark-mode-active', '.dark-mode-active',
                    'body.dark-theme', 'body.night-mode'
                ];
                for (let className of wpDarkClasses) {
                    if (document.querySelector(className)) {
                        return 'dark';
                    }
                }

                return 'light';
            } catch (error) {
                if (window.exa11yDebug) {
                    window.exa11yDebug.log('Theme detection error:', error);
                }
                return 'light';
            }
        },

        // Detect system color scheme preference.
        detectSystemPreference: function() {
            try {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    return 'dark';
                }
                return 'light';
            } catch (error) {
                return 'light';
            }
        },

        // Get computed background color of an element.
        getComputedBackgroundColor: function(element) {
            if (!element) {
                return null;
            }
            const computedStyle = window.getComputedStyle(element);
            const backgroundColor = computedStyle.backgroundColor;
            if (backgroundColor === 'transparent' ||
                backgroundColor === 'rgba(0, 0, 0, 0)' ||
                backgroundColor === 'rgba(0,0,0,0)') {
                return null;
            }
            return backgroundColor;
        },

        // Calculate color luminance (0 = dark, 1 = light).
        getColorLuminance: function(color) {
            try {
                const div = document.createElement('div');
                div.style.color = color;
                document.body.appendChild(div);
                const computedColor = window.getComputedStyle(div).color;
                document.body.removeChild(div);

                const rgbMatch = computedColor.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
                if (!rgbMatch) {
                    return 0.5;
                }
                const r = parseInt(rgbMatch[1]) / 255;
                const g = parseInt(rgbMatch[2]) / 255;
                const b = parseInt(rgbMatch[3]) / 255;

                const rLin = r <= 0.03928 ? r / 12.92 : Math.pow((r + 0.055) / 1.055, 2.4);
                const gLin = g <= 0.03928 ? g / 12.92 : Math.pow((g + 0.055) / 1.055, 2.4);
                const bLin = b <= 0.03928 ? b / 12.92 : Math.pow((b + 0.055) / 1.055, 2.4);

                return 0.2126 * rLin + 0.7152 * gLin + 0.0722 * bLin;
            } catch (error) {
                return 0.5;
            }
        }
    };

    // Note: themes.init() is called from frontend.js to ensure proper order.

    // Listen for system theme changes.
    if (window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addListener(function() {
            const savedTheme = getStorage().get('theme');
            if (!savedTheme || savedTheme === 'auto') {
                themes.restoreOrDetectTheme();
            }
        });
    }

    window.exa11yThemes = themes;

    // Reset to the original website state (light theme).
    themes.resetToDefault = function() {
        const storage = getStorage();
        storage.remove('theme');
        storage.remove('lastUsedTheme');
        // Flag prevents auto-detection from re-applying system preference at once.
        storage.save('manualReset', 'true');

        $('body').removeClass('exa11y-theme-dark exa11y-theme-high-contrast exa11y-theme-transition');
        setActiveThemeTile('light');

        if (window.exa11yDebug) {
            window.exa11yDebug.log('Theme reset to original website state (light theme)');
        }
    };
})(jQuery);
