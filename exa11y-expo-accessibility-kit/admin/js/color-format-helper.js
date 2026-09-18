/**
 * exa11y Color Format Helper
 * 
 * This script enhances the WordPress color picker to display colors in multiple formats.
 */

(function($) {
    'use strict';
    
    // Color format converter functions
    var ColorFormatHelper = {
        /**
         * Convert hex color to RGB
         * @param {string} hex Hex color code
         * @return {object} RGB color object
         */
        hexToRgb: function(hex) {
            // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
            var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
            hex = hex.replace(shorthandRegex, function(m, r, g, b) {
                return r + r + g + g + b + b;
            });

            var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        },
        
        /**
         * Convert RGB to hex
         * @param {number} r Red value (0-255)
         * @param {number} g Green value (0-255)
         * @param {number} b Blue value (0-255)
         * @return {string} Hex color code
         */
        rgbToHex: function(r, g, b) {
            return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
        },
        
        /**
         * Parse a CSS color string in any format
         * @param {string} colorStr CSS color string
         * @return {object} Color object with various format methods
         */
        parseColor: function(colorStr) {
            // Create a temporary element to use the browser's color parsing
            var tempDiv = document.createElement("div");
            tempDiv.style.color = colorStr;
            document.body.appendChild(tempDiv);
            
            // Get computed style (normalized color value)
            var computedColor = window.getComputedStyle(tempDiv).color;
            document.body.removeChild(tempDiv);
            
            // Parse RGB/RGBA format
            var rgbMatch = computedColor.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);
            
            if (rgbMatch) {
                var r = parseInt(rgbMatch[1], 10);
                var g = parseInt(rgbMatch[2], 10);
                var b = parseInt(rgbMatch[3], 10);
                var a = rgbMatch[4] !== undefined ? parseFloat(rgbMatch[4]) : 1;
                
                return {
                    toHexString: function() {
                        return ColorFormatHelper.rgbToHex(r, g, b);
                    },
                    toRgbString: function() {
                        return 'rgb(' + r + ', ' + g + ', ' + b + ')';
                    },
                    toRgbaString: function() {
                        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + a + ')';
                    }
                };
            }
            
            return null;
        }
    };
    
    window.exa11y_ColorFormatHelper = ColorFormatHelper;
    
})(jQuery);
