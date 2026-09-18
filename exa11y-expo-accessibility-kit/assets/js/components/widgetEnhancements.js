/**
 * exa11y Widget Enhancements
 *
 * Additive accessibility upgrades layered on top of controls.js:
 *   - moves focus into the panel when it opens;
 *   - a focus trap that keeps Tab within the panel while it is open;
 *   - an optional Alt+A keyboard shortcut to open/close the panel.
 *
 * The visible close (×) button now lives in the template and is wired by
 * controls.js, so this file no longer injects it.
 */
(function() {
    'use strict';

    function init() {
        var toggle = document.querySelector('.exa11y-accessibility-toggle');
        var panel = document.querySelector('.exa11y-accessibility-panel');
        if (!toggle || !panel) {
            return;
        }

        var config = window.exa11y || {};
        var settings = config.settings || {};

        function isOpen() {
            return panel.classList.contains('active');
        }

        function getFocusable() {
            var selector = 'a[href], button:not([disabled]), input:not([disabled]), ' +
                'select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
            return Array.prototype.slice.call(panel.querySelectorAll(selector)).filter(function(el) {
                return el.offsetParent !== null && !el.closest('[hidden]');
            });
        }

        // Move focus into the panel when it opens (controls.js toggles .active on
        // click; defer so we read the post-toggle state).
        toggle.addEventListener('click', function() {
            window.setTimeout(function() {
                if (isOpen()) {
                    var focusable = getFocusable();
                    if (focusable.length) {
                        focusable[0].focus();
                    }
                }
            }, 0);
        });

        // Trap Tab within the panel while open.
        document.addEventListener('keydown', function(e) {
            if (!isOpen() || (e.key !== 'Tab' && e.keyCode !== 9)) {
                return;
            }
            var focusable = getFocusable();
            if (!focusable.length) {
                return;
            }
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            var active = document.activeElement;

            if (e.shiftKey && (active === first || active === toggle)) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && active === last) {
                e.preventDefault();
                first.focus();
            }
        });

        // Optional Alt+A shortcut to open/close the panel.
        if (settings.keyboard_shortcuts) {
            document.addEventListener('keydown', function(e) {
                if (e.altKey && !e.ctrlKey && !e.metaKey &&
                    (e.key === 'a' || e.key === 'A' || e.keyCode === 65)) {
                    e.preventDefault();
                    toggle.click();
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
