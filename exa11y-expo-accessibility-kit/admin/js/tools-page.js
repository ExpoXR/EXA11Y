(function($) {
    'use strict';

    $(document).ready(function() {
        var data = window.exa11yTools || {};
        var i18n = data.i18n || {};
        var nonce = data.nonce || '';

        // Export Settings
        $('#exa11y-export-settings').on('click', function() {
            var $button = $(this);
            var $result = $('#exa11y-export-result');

            $button.prop('disabled', true).text(i18n.exporting);
            $result.empty();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'exa11y_export_settings',
                    security: nonce
                },
                success: function(response) {
                    if (response.success) {
                        var blob = new Blob([JSON.stringify(response.data, null, 2)], {type: 'application/json'});
                        var url = window.URL.createObjectURL(blob);
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = 'exa11y-settings-' + new Date().toISOString().split('T')[0] + '.json';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);

                        $result.html('<div class="notice notice-success inline"><p>' + i18n.exportSuccess + '</p></div>');
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : i18n.exportFailed;
                        $result.html('<div class="notice notice-error inline"><p>' + errorMsg + '</p></div>');
                    }
                },
                error: function() {
                    $result.html('<div class="notice notice-error inline"><p>' + i18n.exportFailed + '</p></div>');
                },
                complete: function() {
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> ' + i18n.exportSettingsLabel);
                }
            });
        });

        // Enable import button when file is selected
        $('#exa11y-import-file').on('change', function() {
            $('#exa11y-import-settings').prop('disabled', !this.files.length);
        });

        // Import Settings
        $('#exa11y-import-settings').on('click', function() {
            var $button = $(this);
            var $result = $('#exa11y-import-result');
            var fileInput = $('#exa11y-import-file')[0];

            if (!fileInput.files.length) {
                alert(i18n.selectFileToImport);
                return;
            }

            if (!confirm(i18n.confirmOverwrite)) {
                return;
            }

            var file = fileInput.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                try {
                    var settings = JSON.parse(e.target.result);

                    $button.prop('disabled', true).text(i18n.importing);

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'exa11y_import_settings',
                            security: nonce,
                            settings: JSON.stringify(settings)
                        },
                        success: function(response) {
                            if (response.success) {
                                $result.html('<div class="notice notice-success inline"><p>' + i18n.importSuccess + '</p></div>');
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                var errorMsg = response.data && response.data.message ? response.data.message : i18n.importFailed;
                                $result.html('<div class="notice notice-error inline"><p>' + errorMsg + '</p></div>');
                            }
                        },
                        error: function() {
                            $result.html('<div class="notice notice-error inline"><p>' + i18n.importFailed + '</p></div>');
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> ' + i18n.importSettingsLabel);
                        }
                    });

                } catch (error) {
                    $result.html('<div class="notice notice-error inline"><p>' + i18n.invalidJson + '</p></div>');
                }
            };

            reader.readAsText(file);
        });

        // Reset All Settings
        $('#exa11y-reset-all-settings').on('click', function() {
            if (!confirm(i18n.confirmResetAll)) {
                return;
            }

            var $button = $(this);
            var $result = $('#exa11y-reset-result');

            $button.prop('disabled', true).text(i18n.resetting);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'exa11y_reset_all_settings',
                    security: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $result.html('<div class="notice notice-success inline"><p>' + i18n.resetAllSuccess + '</p></div>');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : i18n.resetFailed;
                        $result.html('<div class="notice notice-error inline"><p>' + errorMsg + '</p></div>');
                    }
                },
                error: function() {
                    $result.html('<div class="notice notice-error inline"><p>' + i18n.resetFailed + '</p></div>');
                },
                complete: function() {
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-admin-generic"></span> ' + i18n.resetAllSettingsLabel);
                }
            });
        });

        // Reset Accessibility Features
        $('#exa11y-reset-accessibility-features').on('click', function() {
            if (!confirm(i18n.confirmResetAccessibility)) {
                return;
            }

            var $button = $(this);
            var $result = $('#exa11y-reset-accessibility-result');

            $button.prop('disabled', true).text(i18n.resetting);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'exa11y_reset_accessibility_features',
                    security: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $result.html('<div class="notice notice-success inline"><p>' + i18n.resetAccessibilitySuccess + '</p></div>');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : i18n.resetFailed;
                        $result.html('<div class="notice notice-error inline"><p>' + errorMsg + '</p></div>');
                    }
                },
                error: function() {
                    $result.html('<div class="notice notice-error inline"><p>' + i18n.resetFailed + '</p></div>');
                },
                complete: function() {
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> ' + i18n.resetAccessibilityFeaturesLabel);
                }
            });
        });

    });
})(jQuery);
