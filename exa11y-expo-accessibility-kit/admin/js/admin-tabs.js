/**
 * Shared tab behavior for EXA11Y admin pages.
 */
(function($) {
    'use strict';

    function activateTab($tab) {
        var panelId = $tab.attr('aria-controls');
        var $tabs = $tab.closest('.exa11y-tabs');
        var $panel = $tabs.find('#' + panelId);

        if (!$panel.length) {
            return;
        }

        $tabs.find('.exa11y-tab-button')
            .removeClass('active')
            .attr('aria-selected', 'false');

        $tabs.find('.exa11y-tab-panel')
            .removeClass('active')
            .attr('hidden', 'hidden')
            .hide();

        $tab.addClass('active').attr('aria-selected', 'true');
        $panel.addClass('active').removeAttr('hidden').show();

        try {
            sessionStorage.setItem('exa11y_accessibility_active_tab', $tab.attr('id'));
        } catch (error) {
            // Storage can be unavailable in hardened browsers; tabs still work.
        }
    }

    function initialTab($tabs) {
        var hash = window.location.hash ? window.location.hash.substring(1) : '';
        var stored = '';

        try {
            stored = sessionStorage.getItem('exa11y_accessibility_active_tab') || '';
        } catch (error) {
            stored = '';
        }

        if (hash && $('#' + hash).length) {
            return $('#' + hash);
        }

        if (stored && $('#' + stored).length) {
            return $('#' + stored);
        }

        return $tabs.find('.exa11y-tab-button.active').first().length
            ? $tabs.find('.exa11y-tab-button.active').first()
            : $tabs.find('.exa11y-tab-button').first();
    }

    function initTabs() {
        $('.exa11y-tabs').each(function() {
            var $tabs = $(this);

            $tabs.off('click.exa11yTabs').on('click.exa11yTabs', '.exa11y-tab-button', function(event) {
                event.preventDefault();
                activateTab($(this));
            });

            activateTab(initialTab($tabs));
        });

        $('form').off('submit.exa11yTabs').on('submit.exa11yTabs', function() {
            var activeTabId = $('.exa11y-tab-button.active').attr('id');

            if (!activeTabId) {
                return;
            }

            $(this).find('input[name="active_tab"]').remove();
            $('<input>', {
                type: 'hidden',
                name: 'active_tab',
                value: activeTabId
            }).appendTo(this);
        });
    }

    $(initTabs);
})(jQuery);
