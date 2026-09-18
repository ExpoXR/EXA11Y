/**
 * exa11y Skip Links Component
 *
 * When the `skip_links` setting is enabled, injects a "Skip to main content"
 * link as the first focusable element on the page, targeting the first
 * recognised main landmark. It is visually hidden until focused (see the
 * .exa11y-skip-link rules in screenreader.css).
 */
(function() {
    'use strict';

    var config = window.exa11y || {};
    var settings = config.settings || {};

    if (!settings.skip_links) {
        return;
    }

    function findMain() {
        return document.querySelector('main, [role="main"], #main, #content, #primary');
    }

    function init() {
        var main = findMain();
        if (!main) {
            return;
        }

        if (!main.hasAttribute('tabindex')) {
            main.setAttribute('tabindex', '-1');
        }
        if (!main.id) {
            main.id = 'exa11y-main-content';
        }

        var link = document.createElement('a');
        link.href = '#' + main.id;
        link.className = 'exa11y-skip-link exa11y-sr-only';
        link.textContent = (config.i18n && config.i18n.skipToContent) || 'Skip to main content';

        link.addEventListener('click', function(e) {
            e.preventDefault();
            main.focus();
            main.scrollIntoView();
        });

        document.body.insertBefore(link, document.body.firstChild);
    }

    if (document.readyState !== 'loading') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
})();
