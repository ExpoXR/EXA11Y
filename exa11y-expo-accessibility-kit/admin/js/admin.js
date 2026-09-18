(function($) {
    'use strict';

    function getReadableColor(colorValue) {
        var rgb = hexToRgb(colorValue);

        if (!rgb && window.exa11y_ColorFormatHelper) {
            var parsed = window.exa11y_ColorFormatHelper.parseColor(colorValue);
            if (parsed) {
                rgb = hexToRgb(parsed.toHexString());
            }
        }

        if (!rgb) {
            return '#0D152C';
        }

        var luminance = (0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b) / 255;
        return luminance > 0.55 ? '#0D152C' : '#ffffff';
    }

    function updateColorPickerButton($input, colorValue) {
        var $container = $input.closest('.wp-picker-container');
        var $button = $container.find('.wp-color-result');
        var $buttonText = $container.find('.wp-color-result-text');

        if (!$button.length || !colorValue) {
            return;
        }

        if ($button[0]) {
            $button[0].style.setProperty('background-color', colorValue, 'important');
            $button[0].style.setProperty('background-image', 'none', 'important');
        }

        $button.css({
            backgroundColor: colorValue,
            backgroundImage: 'none'
        });

        if (!$buttonText.length) {
            return;
        }

        $buttonText.css({
            color: getReadableColor(colorValue),
            backgroundColor: 'transparent',
            textShadow: 'none'
        });
    }

    $(document).ready(function() {
        // "Clear Plugin Cache" button on the Tools page. Data comes from the
        // exa11y_cache object localized in admin/class-admin.php.
        $('#exa11y-clear-cache').on('click', function(e) {
            e.preventDefault();
            var cache = window.exa11y_cache;
            if (!cache || !confirm(cache.strings.confirm_clear)) {
                return;
            }
            $.post(cache.ajax_url, {
                action: 'exa11y_clear_cache',
                security: cache.nonce
            }).done(function(response) {
                alert(response && response.success ? cache.strings.success : cache.strings.failed);
            }).fail(function() {
                alert(cache.strings.failed);
            });
        });

        // Initialize color pickers with enhanced options and multiple format support
        if (typeof $.fn.wpColorPicker === 'function') {
            $('.exa11y-color-picker, .color-picker').each(function() {
                var $colorPickerInput = $(this);
                var defaultColor = $colorPickerInput.data('default-color') || '';
                
                $colorPickerInput.wpColorPicker({
                    defaultColor: defaultColor,
                    change: function(event, ui) {
                        var color = ui.color;
                        var hexColor = color.toString();
                        // Update the input field value on color change
                        $(this).val(hexColor);
                        updateColorPickerButton($(this), hexColor);
                        
                        // Create or update format display
                        var $container = $(this).closest('.wp-picker-container');
                        var $formatInfo = $container.find('.exa11y-color-format-display');
                        
                        if ($formatInfo.length === 0) {
                            $formatInfo = $('<div class="exa11y-color-format-display"></div>');
                            $container.append($formatInfo);
                        }
                        
                        try {
                            // Display different color formats
                            // Use the ColorFormatHelper to convert colors properly
                            var colorObj = window.exa11y_ColorFormatHelper ? 
                                          window.exa11y_ColorFormatHelper.parseColor(hexColor) : null;
                            
                            var formatHtml = '<div class="exa11y-color-format-item"><span>HEX:</span> ' + hexColor + '</div>';
                            
                            if (colorObj) {
                                formatHtml += '<div class="exa11y-color-format-item"><span>RGB:</span> ' + colorObj.toRgbString() + '</div>';
                                formatHtml += '<div class="exa11y-color-format-item"><span>RGBA:</span> ' + colorObj.toRgbaString() + '</div>';
                            } else {
                                // Fallback to our built-in converter
                                var rgbColor = hexToRgb(hexColor);
                                if (rgbColor) {
                                    formatHtml += '<div class="exa11y-color-format-item"><span>RGB:</span> rgb(' + rgbColor.r + ', ' + rgbColor.g + ', ' + rgbColor.b + ')</div>';
                                    formatHtml += '<div class="exa11y-color-format-item"><span>RGBA:</span> rgba(' + rgbColor.r + ', ' + rgbColor.g + ', ' + rgbColor.b + ', 1)</div>';
                                }
                            }
                            
                            // Add HSL if we have our fallback helper
                            var hslColor = hexToHsl(hexColor);
                            if (hslColor) {
                                formatHtml += '<div class="exa11y-color-format-item"><span>HSL:</span> hsl(' + Math.round(hslColor.h) + ', ' + Math.round(hslColor.s * 100) + '%, ' + Math.round(hslColor.l * 100) + '%)</div>';
                            }
                            
                            $formatInfo.html(formatHtml);
                        } catch (error) {
                            // Fallback to just displaying the hex color if there's any error
                            $formatInfo.html('<div class="exa11y-color-format-item"><span>HEX:</span> ' + color.toString() + '</div>');
                        }
                    },
                    clear: function() {
                        // Reset to default color when cleared
                        $(this).val(defaultColor);
                        
                        // Clear format display
                        var $container = $(this).closest('.wp-picker-container');
                        var $formatInfo = $container.find('.exa11y-color-format-display');
                        $formatInfo.empty();
                        updateColorPickerButton($(this), defaultColor);
                    },
                    palettes: true // Enable the predefined color palette
                });
                
                // Trigger change to display formats for initial values
                setTimeout(function() {
                    var initialColor = $colorPickerInput.val();
                    if (initialColor) {
                        $colorPickerInput.wpColorPicker('color', initialColor);
                        updateColorPickerButton($colorPickerInput, initialColor);
                    }
                }, 100);
            });

            $('.exa11y-color-picker, .color-picker').on('change input keyup', function() {
                updateColorPickerButton($(this), $(this).val());
            });
        }

        // Toggle display of font size factor field based on checkbox state
        $('input[name="exa11y_settings[font_size_scaling]"]').on('change', function() {
            if ($(this).is(':checked')) {
                $('.exa11y-field-subitem').fadeIn();
            } else {
                $('.exa11y-field-subitem').fadeOut();
            }
        });

        // Initialize visibility on page load
        if (!$('input[name="exa11y_settings[font_size_scaling]"]').is(':checked')) {
            $('.exa11y-field-subitem').hide();
        }

        // Handle position selection - vertical options
        $('.exa11y-vertical-radio').on('change', function() {
            const verticalPosition = $(this).val();
            
            // Update selected class
            $('.exa11y-vertical-option').removeClass('selected');
            $(this).closest('.exa11y-vertical-option').addClass('selected');
            
            // Show/hide appropriate horizontal options
            if (verticalPosition === 'bottom') {
                $('.bottom-options').show();
                $('.center-options').hide();
                
                // Get the selected horizontal position for bottom
                const horizontalPosition = $('input[name="exa11y_horizontal_bottom"]:checked, input[name="exa11y_horizontal_bottom"]:checked').val() || 'right';
                
                // Update the combined position value
                $('#exa11y_combined_position, #exa11y_combined_position').val(verticalPosition + '-' + horizontalPosition);
            } else if (verticalPosition === 'center') {
                $('.bottom-options').hide();
                $('.center-options').show();
                
                // Get the selected horizontal position for center
                const horizontalPosition = $('input[name="exa11y_horizontal_center"]:checked, input[name="exa11y_horizontal_center"]:checked').val() || 'right';
                
                // Update the combined position value
                $('#exa11y_combined_position, #exa11y_combined_position').val(verticalPosition + '-' + horizontalPosition);
            }
        });
        
        // Handle position selection - horizontal options for bottom
        $('.exa11y-horizontal-radio[name="exa11y_horizontal_bottom"], .exa11y-horizontal-radio[name="exa11y_horizontal_bottom"]').on('change', function() {
            const horizontalPosition = $(this).val();
            
            // Update selected class
            $('.bottom-options .exa11y-horizontal-option').removeClass('selected');
            $(this).closest('.exa11y-horizontal-option').addClass('selected');
            
            // Update the combined position value
            $('#exa11y_combined_position, #exa11y_combined_position').val('bottom-' + horizontalPosition);
        });
        
        // Handle position selection - horizontal options for center
        $('.exa11y-horizontal-radio[name="exa11y_horizontal_center"], .exa11y-horizontal-radio[name="exa11y_horizontal_center"]').on('change', function() {
            const horizontalPosition = $(this).val();
            
            // Update selected class
            $('.center-options .exa11y-horizontal-option').removeClass('selected');
            $(this).closest('.exa11y-horizontal-option').addClass('selected');
            
            // Update the combined position value
            $('#exa11y_combined_position, #exa11y_combined_position').val('center-' + horizontalPosition);
        });
        
        // Handle regenerate accessibility statement button
        $('#regenerate-accessibility-statement').on('click', function() {
            const $button = $(this);
            const originalText = $button.text();
            
            // Disable button and show loading state
            $button.prop('disabled', true).text(exa11yAdmin.regenerating_text);
            
            // Send AJAX request to regenerate statement
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'exa11y_regenerate_statement',
                    security: exa11yAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        const $message = $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                        $button.closest('form').before($message);
                        
                        // Auto-dismiss after 5 seconds
                        setTimeout(function() {
                            $message.fadeOut(function() {
                                $(this).remove();
                            });
                        }, 5000);
                    } else {
                        // Show error message
                        const $message = $('<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>');
                        $button.closest('form').before($message);
                    }
                },
                error: function() {
                    // Show generic error message
                    const $message = $('<div class="notice notice-error is-dismissible"><p>' + exa11yAdmin.error_text + '</p></div>');
                    $button.closest('form').before($message);
                },
                complete: function() {
                    // Re-enable button and restore original text
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
    });

    // Helper functions for color format conversion. hexToRgb delegates to the
    // shared exa11y_ColorFormatHelper (color-format-helper.js) so the conversion
    // lives in one place; the null fallback keeps callers safe if that helper
    // failed to load.
    function hexToRgb(hex) {
        if (window.exa11y_ColorFormatHelper && typeof window.exa11y_ColorFormatHelper.hexToRgb === 'function') {
            return window.exa11y_ColorFormatHelper.hexToRgb(hex);
        }
        return null;
    }

    function hexToHsl(hex) {
        var rgb = hexToRgb(hex);
        if (!rgb) return null;
        
        var r = rgb.r / 255;
        var g = rgb.g / 255;
        var b = rgb.b / 255;
        
        var max = Math.max(r, g, b);
        var min = Math.min(r, g, b);
        var h, s, l = (max + min) / 2;
        
        if (max === min) {
            h = s = 0; // achromatic
        } else {
            var d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        
        return {
            h: h * 360,
            s: s,
            l: l
        };
    }

})(jQuery);
