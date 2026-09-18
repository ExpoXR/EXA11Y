/**
 * Filters Module
 * Handles visual filter adjustments (contrast, saturation, brightness, blue
 * light, grayscale) and the colour-vision-deficiency (CVD) filters.
 *
 * Values are driven by stepper tiles: controls.js fires `exa11y:stepper`
 * events whose `control` matches a key in `filterValues`. The apply pipeline
 * (applyFilters / applyDynamicCVDFilter) is unchanged; only the input source
 * moved from range sliders to steppers.
 */

(function($) {
    // Persist a value, or clear it when at its default.
    function persist(control, value, isDefault) {
        if (!window.exa11yStorage) {
            return;
        }
        if (isDefault) {
            window.exa11yStorage.remove(control);
        } else {
            window.exa11yStorage.save(control, value);
        }
    }

    // Reflect a value back onto its stepper tile display.
    function syncStepper(control, value) {
        if (window.exa11yControls && typeof window.exa11yControls.setStepper === 'function') {
            window.exa11yControls.setStepper(control, value);
        }
    }

    const filters = {
        filterValues: {
            contrast: 100,
            saturation: 100,
            brightness: 100,
            blueFilter: 0,
            grayscale: 0,
            redWeakness: 0,
            greenWeakness: 0,
            blueWeakness: 0
        },

        // Controls whose default (off) value is 0 rather than 100.
        zeroDefault: ['blueFilter', 'grayscale', 'redWeakness', 'greenWeakness', 'blueWeakness'],
        cvdControls: ['redWeakness', 'greenWeakness', 'blueWeakness'],

        init: function() {
            if ($('#exa11y-content-wrapper').length === 0) {
                $('body').wrapInner('<div id="exa11y-content-wrapper"></div>');
            }

            this.bindSteppers();
            this.restoreSettings();
            this.applyFilters();

            if (window.exa11yDebug) {
                window.exa11yDebug.log('Filters module initialized');
            }
        },

        bindSteppers: function() {
            const self = this;
            document.addEventListener('exa11y:stepper', function(event) {
                const detail = event.detail || {};
                const control = detail.control;
                if (!(control in self.filterValues)) {
                    return;
                }
                const value = parseFloat(detail.value);

                // CVD filters are mutually exclusive: raising one clears the others.
                if (detail.cvd && value > 0) {
                    self.cvdControls.forEach(function(other) {
                        if (other !== control) {
                            self.filterValues[other] = 0;
                            syncStepper(other, 0);
                            persist(other, 0, true);
                        }
                    });
                }

                self.filterValues[control] = value;
                const isDefault = self.zeroDefault.indexOf(control) !== -1 ? value === 0 : value === 100;
                persist(control, value, isDefault);
                self.applyFilters();

                if (window.exa11yDebug) {
                    window.exa11yDebug.log('Filter ' + control + ' set to ' + value);
                }
            });
        },

        // Apply all active filters to the content wrapper.
        applyFilters: function() {
            let filterString = '';
            const $wrapper = $('#exa11y-content-wrapper');
            if (!$wrapper.length) {
                return;
            }

            $wrapper.removeClass('exa11y-red-weakness-active exa11y-green-weakness-active exa11y-blue-weakness-active');

            if (this.filterValues.contrast !== 100) {
                filterString += `contrast(${this.filterValues.contrast}%) `;
            }
            if (this.filterValues.saturation !== 100) {
                filterString += `saturate(${this.filterValues.saturation}%) `;
            }
            if (this.filterValues.brightness !== 100) {
                filterString += `brightness(${this.filterValues.brightness}%) `;
            }
            if (this.filterValues.blueFilter > 0) {
                const intensity = this.filterValues.blueFilter / 100;
                filterString += `brightness(${100 - (intensity * 5)}%) sepia(${intensity * 20}%) hue-rotate(${intensity * 30}deg) `;
            }
            if (this.filterValues.grayscale > 0) {
                filterString += `grayscale(${this.filterValues.grayscale}%) `;
            }

            // Colour-vision-deficiency filters (mutually exclusive).
            if (this.filterValues.redWeakness > 0) {
                this.applyDynamicCVDFilter($wrapper, 'red', this.filterValues.redWeakness);
            } else if (this.filterValues.greenWeakness > 0) {
                this.applyDynamicCVDFilter($wrapper, 'green', this.filterValues.greenWeakness);
            } else if (this.filterValues.blueWeakness > 0) {
                this.applyDynamicCVDFilter($wrapper, 'blue', this.filterValues.blueWeakness);
            }

            const hasCVDFilter = $wrapper.hasClass('exa11y-red-weakness-active') ||
                                 $wrapper.hasClass('exa11y-green-weakness-active') ||
                                 $wrapper.hasClass('exa11y-blue-weakness-active');

            if (filterString && !hasCVDFilter) {
                $wrapper.css('filter', filterString.trim());
            } else if (!filterString && !hasCVDFilter) {
                $wrapper.css('filter', 'none');
            }
        },

        // Apply dynamic Color Vision Deficiency filter with gradual intensity.
        applyDynamicCVDFilter: function($wrapper, type, intensity) {
            const factor = intensity / 100;

            const matrices = {
                red: [0.567, 0.433, 0, 0, 0, 0.558, 0.442, 0, 0, 0, 0, 0.242, 0.758, 0, 0, 0, 0, 0, 1, 0],
                green: [0.625, 0.375, 0, 0, 0, 0.7, 0.3, 0, 0, 0, 0, 0.3, 0.7, 0, 0, 0, 0, 0, 1, 0],
                blue: [0.95, 0.05, 0, 0, 0, 0, 0.433, 0.567, 0, 0, 0, 0.475, 0.525, 0, 0, 0, 0, 0, 1, 0]
            };
            const identity = [1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0];

            if (!matrices[type]) {
                return;
            }

            const dynamicMatrix = [];
            const baseMatrix = matrices[type];
            for (let i = 0; i < 20; i++) {
                dynamicMatrix[i] = identity[i] + (baseMatrix[i] - identity[i]) * factor;
            }

            const svgFilter = `url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg"><filter id="${type}-weakness-dynamic"><feColorMatrix type="matrix" values="${dynamicMatrix.join(', ')}"/></filter></svg>#${type}-weakness-dynamic')`;
            $wrapper.css('filter', svgFilter);
            $wrapper.addClass(`${type}-weakness-active`);
        },

        // Restore filter settings from storage and sync the stepper displays.
        restoreSettings: function() {
            const self = this;
            if (!window.exa11yStorage) {
                return;
            }

            Object.keys(this.filterValues).forEach(function(control) {
                const stored = window.exa11yStorage.get(control);
                if (stored !== null && stored !== undefined && stored !== '') {
                    const value = parseInt(stored, 10);
                    if (!isNaN(value)) {
                        self.filterValues[control] = value;
                        syncStepper(control, value);
                    }
                }
            });

            this.applyFilters();
        }
    };

    $(document).ready(function() {
        filters.init();
    });

    window.exa11yFilters = filters;
})(jQuery);
