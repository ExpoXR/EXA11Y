/**
 * Controls Module
 *
 * Owns the accessibility panel shell (open/close, focus, keyboard) and the
 * generic stepper engine shared by the graded feature tiles.
 *
 * Stepper contract: each `.exa11y-tile-stepper` carries a `data-control`
 * name plus either `data-levels="a,b,c"` or `data-min/data-max/data-stepsize`,
 * a `data-unit` and a `data-default`. Pressing -/+ moves through the levels and
 * fires a `exa11y:stepper` CustomEvent ({control, value, cvd}) on document;
 * fontSettings.js and filters.js listen and apply the value. Feature modules
 * sync a stepper's display via window.exa11yControls.setStepper(control,
 * value) without re-firing the event (used on restore and reset).
 */

(function($) {
    const controls = {
        // control name -> stepper DOM element
        steppers: {},

        init: function() {
            this.bindPanel();
            this.initSteppers();
            this.setupKeyboardNavigation();
            this.preventDefaultBehavior();
        },

        /* --------------------------------------------------------------- *
         * Panel open / close
         * --------------------------------------------------------------- */
        bindPanel: function() {
            const $toggle = $('.exa11y-accessibility-toggle');
            const $panel = $('.exa11y-accessibility-panel');

            $toggle.on('click', function() {
                const isOpen = $panel.hasClass('active');
                if (isOpen) {
                    controls.closePanel();
                } else {
                    controls.openPanel();
                }
            });

            // Close (×) button in the header.
            $('.exa11y-panel-close').on('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                controls.closePanel();
            });

            // Close when clicking outside the widget.
            $(document).on('click', function(event) {
                if (!$(event.target).closest('.exa11y-accessibility-controls').length) {
                    if ($panel.hasClass('active')) {
                        controls.closePanel(true);
                    }
                }
            });

            // Reset button.
            $('.exa11y-btn-reset').on('click', function() {
                controls.resetAllSettings();
            });
        },

        openPanel: function() {
            $('.exa11y-accessibility-panel').addClass('active').attr('aria-hidden', 'false');
            $('.exa11y-accessibility-toggle').attr('aria-expanded', 'true');
        },

        closePanel: function(skipFocus) {
            $('.exa11y-accessibility-panel').removeClass('active').attr('aria-hidden', 'true');
            const $toggle = $('.exa11y-accessibility-toggle').attr('aria-expanded', 'false');
            if (!skipFocus) {
                $toggle.focus();
            }
            controls.announceToScreenReader('Accessibility menu closed');
        },

        /* --------------------------------------------------------------- *
         * Stepper engine
         * --------------------------------------------------------------- */
        initSteppers: function() {
            const self = this;

            $('.exa11y-tile-stepper').each(function() {
                const el = this;
                const control = el.getAttribute('data-control');
                if (!control) {
                    return;
                }

                const levels = self.buildLevels(el);
                const unit = el.getAttribute('data-unit') || '';
                const def = parseFloat(el.getAttribute('data-default'));
                const cvd = el.getAttribute('data-cvd') === '1';
                let index = self.closestIndex(levels, isNaN(def) ? levels[0] : def);

                el._a11yStepper = { levels: levels, unit: unit, cvd: cvd, index: index, control: control };
                self.steppers[control] = el;

                self.renderStepper(el);

                $(el).find('.exa11y-stepper-btn').on('click', function() {
                    const dir = this.getAttribute('data-step') === 'inc' ? 1 : -1;
                    const state = el._a11yStepper;
                    const next = Math.min(state.levels.length - 1, Math.max(0, state.index + dir));
                    if (next === state.index) {
                        return;
                    }
                    state.index = next;
                    self.renderStepper(el);
                    self.emitStepper(el);
                });
            });
        },

        // Build the level array from data-levels or data-min/max/stepsize.
        buildLevels: function(el) {
            const raw = el.getAttribute('data-levels');
            if (raw) {
                return raw.split(',').map(function(v) { return parseFloat(v); });
            }
            const min = parseFloat(el.getAttribute('data-min'));
            const max = parseFloat(el.getAttribute('data-max'));
            const step = parseFloat(el.getAttribute('data-stepsize')) || 1;
            const levels = [];
            for (let v = min; v <= max + 1e-9; v += step) {
                levels.push(Math.round(v * 1000) / 1000);
            }
            return levels;
        },

        closestIndex: function(levels, value) {
            let best = 0;
            let bestDiff = Infinity;
            for (let i = 0; i < levels.length; i++) {
                const diff = Math.abs(levels[i] - value);
                if (diff < bestDiff) {
                    bestDiff = diff;
                    best = i;
                }
            }
            return best;
        },

        // Update the value readout, progress fill and button disabled state.
        renderStepper: function(el) {
            const state = el._a11yStepper;
            const value = state.levels[state.index];
            const display = (state.unit === 'x') ? (value + 'x') : (value + state.unit);

            $(el).find('.exa11y-stepper-value').text(display);

            const pct = state.levels.length > 1
                ? (state.index / (state.levels.length - 1)) * 100
                : 0;
            $(el).find('.exa11y-stepper-fill').css('width', pct + '%');

            $(el).find('.exa11y-stepper-dec').prop('disabled', state.index === 0);
            $(el).find('.exa11y-stepper-inc').prop('disabled', state.index === state.levels.length - 1);

            el.setAttribute('data-value', value);
        },

        emitStepper: function(el) {
            const state = el._a11yStepper;
            document.dispatchEvent(new CustomEvent('exa11y:stepper', {
                detail: {
                    control: state.control,
                    value: state.levels[state.index],
                    cvd: state.cvd
                }
            }));
        },

        // Public: set a stepper's displayed value without firing the event.
        // Used by feature modules on restore, reset and CVD mutual exclusion.
        setStepper: function(control, value) {
            const el = this.steppers[control];
            if (!el) {
                return;
            }
            el._a11yStepper.index = this.closestIndex(el._a11yStepper.levels, parseFloat(value));
            this.renderStepper(el);
        },

        /* --------------------------------------------------------------- *
         * Keyboard: Escape closes; Arrow keys roam the tile grid.
         * --------------------------------------------------------------- */
        setupKeyboardNavigation: function() {
            $('.exa11y-accessibility-toggle').on('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $(this).click();
                }
            });

            $(document).on('keydown', function(event) {
                if (event.key === 'Escape' && $('.exa11y-accessibility-panel').hasClass('active')) {
                    controls.closePanel();
                }
            });

            // Arrow-key roving focus across focusable controls in the grid.
            $('.exa11y-panel-body').on('keydown', function(event) {
                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].indexOf(event.key) === -1) {
                    return;
                }
                const focusables = $(this)
                    .find('a[href], button:not([disabled]), [tabindex="0"]')
                    .filter(':visible')
                    .toArray();
                const idx = focusables.indexOf(document.activeElement);
                if (idx === -1) {
                    return;
                }
                const forward = (event.key === 'ArrowDown' || event.key === 'ArrowRight');
                const nextIdx = forward ? idx + 1 : idx - 1;
                if (nextIdx >= 0 && nextIdx < focusables.length) {
                    event.preventDefault();
                    focusables[nextIdx].focus();
                }
            });
        },

        preventDefaultBehavior: function() {
            document.querySelectorAll('.exa11y-accessibility-controls button[type="button"]').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                });
            });
        },

        /* --------------------------------------------------------------- *
         * Reset everything to the original site appearance.
         * --------------------------------------------------------------- */
        resetAllSettings: function() {
            // Toggle tiles back to off.
            $('.exa11y-tile-toggle').removeClass('active').attr('aria-pressed', 'false');

            // Steppers back to their defaults.
            $('.exa11y-tile-stepper').each(function() {
                const def = parseFloat(this.getAttribute('data-default'));
                controls.setStepper(this.getAttribute('data-control'), isNaN(def) ? 0 : def);
            });

            // Body classes cleared.
            $('body').removeClass('exa11y-large-font exa11y-high-contrast exa11y-saturated exa11y-bright exa11y-blue-filter exa11y-grayscale exa11y-increased-line-height exa11y-theme-dark exa11y-theme-high-contrast exa11y-readable-font exa11y-links-highlighted exa11y-images-hidden');

            // Theme module reset (returns the site to its original light state).
            if (window.exa11yThemes && typeof window.exa11yThemes.resetToDefault === 'function') {
                window.exa11yThemes.resetToDefault();
            } else {
                $('body').removeClass('exa11y-theme-dark exa11y-theme-high-contrast exa11y-theme-transition');
                this.clearStoredTheme();
            }

            // Font size & line height.
            document.documentElement.style.setProperty('--exa11y-font-size-scale', 1);
            document.documentElement.style.setProperty('--exa11y-line-height-scale', 1);

            const contentWrapper = document.getElementById('exa11y-content-wrapper');
            if (contentWrapper) {
                contentWrapper.style.fontSize = '';
                contentWrapper.style.filter = 'none';
                contentWrapper.classList.remove('exa11y-red-weakness-active', 'exa11y-green-weakness-active', 'exa11y-blue-weakness-active');
            }

            // Readable font.
            if (window.exa11yFontSettings && typeof window.exa11yFontSettings.applyReadableFont === 'function') {
                window.exa11yFontSettings.applyReadableFont(false);
            }

            // Visual filters.
            if (window.exa11yFilters) {
                window.exa11yFilters.filterValues = {
                    contrast: 100,
                    saturation: 100,
                    brightness: 100,
                    blueFilter: 0,
                    grayscale: 0,
                    redWeakness: 0,
                    greenWeakness: 0,
                    blueWeakness: 0
                };
                window.exa11yFilters.applyFilters();
            }

            // Clear persisted preferences.
            if (window.exa11yStorage) {
                window.exa11yStorage.clearAll();
            }

            document.documentElement.classList.remove(
                'exa11y-readable-font-active',
                'exa11y-links-highlighted',
                'exa11y-images-hidden'
            );

            this.announceToScreenReader('All accessibility settings have been reset to the original website appearance');
        },

        clearStoredTheme: function() {
            if (window.exa11yStorage) {
                window.exa11yStorage.remove('theme');
                window.exa11yStorage.remove('lastUsedTheme');
            } else {
                localStorage.removeItem('exa11y-theme');
                localStorage.removeItem('exa11y-last-used-theme');
            }
        },

        announceToScreenReader: function(message) {
            let announcer = document.getElementById('exa11y-announcer');
            if (!announcer) {
                announcer = document.createElement('div');
                announcer.id = 'exa11y-announcer';
                announcer.setAttribute('aria-live', 'polite');
                announcer.setAttribute('aria-atomic', 'true');
                announcer.className = 'sr-only';
                document.body.appendChild(announcer);
            }
            announcer.textContent = message;
            setTimeout(function() {
                announcer.textContent = '';
            }, 3000);
        }
    };

    $(document).ready(function() {
        controls.init();
    });

    window.exa11yControls = controls;
})(jQuery);
