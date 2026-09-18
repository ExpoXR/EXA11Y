/**
 * Font Settings Module
 * Handles font size, line height, and readable font adjustments.
 *
 * Font size and line height are driven by stepper tiles: controls.js fires
 * `exa11y:stepper` events for the `fontSize` / `lineHeight` controls and this
 * module applies + persists the value. The apply/persist helpers are unchanged;
 * only the input source moved from range sliders to steppers.
 */

(function($) {
    const fontSettings = {
        // Supported font size values
        fontSizes: [1, 1.25, 1.5, 1.75, 2, 2.5],

        // Supported line height values
        lineHeights: [1, 1.5, 2],

        // Standard readable font family
        readableFontFamily: "'Arial', 'Helvetica', 'Verdana', sans-serif",

        init: function() {
            if (typeof exa11y !== 'undefined') {
                this.bindSteppers();
                this.initReadableFontToggle();
                this.restoreSettings();

                if (window.exa11yDebug) {
                    window.exa11yDebug.log('Font Settings component initialized');
                }
            }
        },

        // Listen for stepper changes from controls.js.
        bindSteppers: function() {
            const self = this;
            document.addEventListener('exa11y:stepper', function(event) {
                const detail = event.detail || {};
                if (detail.control === 'fontSize') {
                    self.applyFontSize(parseFloat(detail.value));
                    if (window.exa11yDebug) {
                        window.exa11yDebug.log('Font size changed: ' + detail.value + 'x');
                    }
                } else if (detail.control === 'lineHeight') {
                    self.applyLineHeight(parseFloat(detail.value));
                    if (window.exa11yDebug) {
                        window.exa11yDebug.log('Line height changed: ' + detail.value + 'x');
                    }
                }
            });
        },

        // Initialize readable font toggle tile.
        initReadableFontToggle: function() {
            const self = this;

            $('.exa11y-btn-readable-font').on('click', function() {
                const isActive = !$(this).hasClass('active');
                $(this).toggleClass('active', isActive).attr('aria-pressed', isActive ? 'true' : 'false');
                $('body').toggleClass('exa11y-readable-font', isActive);
                self.applyReadableFont(isActive);

                if (window.exa11yStorage) {
                    if (isActive) {
                        window.exa11yStorage.save('readableFont', '1');
                    } else {
                        window.exa11yStorage.remove('readableFont');
                    }
                }
            });
        },

        // Restore all font settings from localStorage.
        restoreSettings: function() {
            if (!window.exa11yStorage) {
                return;
            }
            const storage = window.exa11yStorage;

            // Font size.
            const savedFontSize = storage.get('fontSize');
            if (savedFontSize) {
                let scale = parseFloat(savedFontSize);
                if (!this.fontSizes.includes(scale)) {
                    scale = this.findClosestValue(scale, this.fontSizes);
                }
                this.applyFontSize(scale);
                this.syncStepper('fontSize', scale);
            }

            // Line height.
            const savedLineHeight = storage.get('lineHeight');
            if (savedLineHeight) {
                let scale = parseFloat(savedLineHeight);
                if (!this.lineHeights.includes(scale)) {
                    scale = this.findClosestValue(scale, this.lineHeights);
                }
                this.applyLineHeight(scale);
                this.syncStepper('lineHeight', scale);
            }

            // Readable font.
            const savedReadableFont = storage.get('readableFont');
            if (savedReadableFont === '1') {
                $('.exa11y-btn-readable-font').addClass('active').attr('aria-pressed', 'true');
                $('body').addClass('exa11y-readable-font');
                this.applyReadableFont(true);
            }
        },

        syncStepper: function(control, value) {
            if (window.exa11yControls && typeof window.exa11yControls.setStepper === 'function') {
                window.exa11yControls.setStepper(control, value);
            }
        },

        // Apply font size to content.
        applyFontSize: function(scale) {
            const contentWrapper = document.getElementById('exa11y-content-wrapper');
            if (!contentWrapper) {
                return;
            }
            document.documentElement.style.setProperty('--exa11y-font-size-scale', scale);

            if (scale !== 1) {
                document.body.classList.add('exa11y-large-font');
            } else {
                document.body.classList.remove('exa11y-large-font');
            }

            if (window.exa11yStorage) {
                if (scale === 1) {
                    window.exa11yStorage.remove('fontSize');
                } else {
                    window.exa11yStorage.save('fontSize', scale);
                }
            }
        },

        // Apply line height to content.
        applyLineHeight: function(scale) {
            document.documentElement.style.setProperty('--exa11y-line-height-scale', scale);

            if (window.exa11yStorage) {
                if (scale === 1) {
                    document.body.classList.remove('exa11y-increased-line-height');
                    window.exa11yStorage.remove('lineHeight');
                } else {
                    document.body.classList.add('exa11y-increased-line-height');
                    window.exa11yStorage.save('lineHeight', scale);
                }
            }
        },

        // Apply readable font to content.
        applyReadableFont: function(isEnabled) {
            const contentWrapper = document.getElementById('exa11y-content-wrapper');
            if (!contentWrapper) {
                return;
            }
            if (isEnabled) {
                document.documentElement.style.setProperty('--exa11y-readable-font-family', this.readableFontFamily, 'important');
                document.documentElement.classList.add('exa11y-readable-font-active');
                contentWrapper.classList.add('exa11y-apply-readable-font');
            } else {
                document.documentElement.style.removeProperty('--exa11y-readable-font-family');
                document.documentElement.classList.remove('exa11y-readable-font-active');
                contentWrapper.classList.remove('exa11y-apply-readable-font');
                // Keep the toggle tile visual state in sync when reset externally.
                $('.exa11y-btn-readable-font').removeClass('active').attr('aria-pressed', 'false');
            }
        },

        // Helper: Find closest value in array.
        findClosestValue: function(value, array) {
            return array.reduce((prev, curr) =>
                Math.abs(curr - value) < Math.abs(prev - value) ? curr : prev
            );
        }
    };

    $(document).ready(function() {
        fontSettings.init();
    });

    window.exa11yFontSettings = fontSettings;
})(jQuery);
