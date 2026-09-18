/**
 * Color Theme Settings
 *
 * Shows or hides the nested "Default Theme Setting" block when the Color Theme
 * checkbox is toggled. Bound as a delegated listener rather than an inline
 * onchange attribute.
 */
(function () {
    'use strict';

    var CHECKBOX = '.exa11y-color-theme-settings input[type="checkbox"][name="exa11y_settings[color_theme]"]';

    function sync(checkbox) {
        var options = document.getElementById('color-theme-options');
        if (options) {
            options.classList.toggle('is-hidden', !checkbox.checked);
        }
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.matches && event.target.matches(CHECKBOX)) {
            sync(event.target);
        }
    });

})();
