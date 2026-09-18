/**
 * Admin bar quick actions.
 *
 * Handles the Clear Cache and Reset Everything items in the EXA11Y admin-bar
 * menu. Bound as delegated listeners on the admin-bar node ids, so the markup
 * carries no inline onclick attributes.
 */
(function ($) {
    'use strict';

    function post(action, nonce, onSuccess, onFailure) {
        $.ajax({
            url: exa11y_admin_menu.ajax_url,
            type: 'POST',
            data: {
                action: action,
                security: nonce
            },
            success: function (response) {
                if (response && response.success) {
                    onSuccess();
                } else {
                    onFailure();
                }
            },
            error: onFailure
        });
    }

    $(document).on('click', '#wp-admin-bar-exa11y-clear-cache a', function (event) {
        event.preventDefault();

        var strings = exa11y_admin_menu.strings;
        if (!window.confirm(strings.confirm_clear_cache)) {
            return;
        }

        post('exa11y_clear_cache', exa11y_admin_menu.nonces.clear_cache, function () {
            window.alert(strings.cache_cleared);
        }, function () {
            window.alert(strings.cache_clear_failed);
        });
    });

    $(document).on('click', '#wp-admin-bar-exa11y-reset-all a', function (event) {
        event.preventDefault();

        var strings = exa11y_admin_menu.strings;
        if (!window.confirm(strings.confirm_reset)) {
            return;
        }

        post('exa11y_reset_settings', exa11y_admin_menu.nonces.reset_settings, function () {
            window.alert(strings.settings_reset);
            location.reload();
        }, function () {
            window.alert(strings.reset_failed);
        });
    });
})(jQuery);
