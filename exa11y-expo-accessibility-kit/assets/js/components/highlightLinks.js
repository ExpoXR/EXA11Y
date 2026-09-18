/**
 * Highlight Links Component
 * 
 * This component adds functionality to highlight all links on the page
 * with a yellow background, black border, black text, and larger font size
 * for improved accessibility.
 */

(function($) {
    'use strict';
    
    // Store the current state of link highlighting
    let isHighlightActive = false;
    
    // Store the original styles to restore them later
    let originalStyles = {};
    
    const HighlightLinks = {
        /**
         * Initialize the highlight links functionality
         */
        init: function() {
            // Only initialize if the feature is enabled in settings
            if (typeof exa11y !== 'undefined' && exa11y.highlightLinksActive) {
                this.bindEvents();
                this.restoreState();

                if (window.exa11yDebug) {
                    window.exa11yDebug.log('Highlight Links component initialized');
                }
            }
        },

        restoreState: function() {
            if (window.exa11yStorage && window.exa11yStorage.get('highlightLinks') === '1') {
                HighlightLinks.enableHighlighting();
                $('.exa11y-btn-highlight-links').addClass('active');
            }
        },
        
        /**
         * Bind event listeners
         */
        bindEvents: function() {
            // Handle highlight links button click
            $(document).on('click', '.exa11y-btn-highlight-links', function() {
                if (isHighlightActive) {
                    HighlightLinks.disableHighlighting();
                    $(this).removeClass('active');
                } else {
                    HighlightLinks.enableHighlighting();
                    $(this).addClass('active');
                }
            });
            
            // Add event listener for the "Reset" button
            $(document).on('click', '.exa11y-btn-reset', function() {
                if (isHighlightActive) {
                    HighlightLinks.disableHighlighting();
                    $('.exa11y-btn-highlight-links').removeClass('active');
                }
            });
        },
        
        /**
         * Enable link highlighting
         */
        enableHighlighting: function() {
            // Get all links on the page (excluding the accessibility panel links)
            const links = $('a').not('.exa11y-accessibility-panel a, .exa11y-accessibility-toggle');
            
            // Store original styles and apply highlighting
            links.each(function() {
                const $link = $(this);
                const linkId = 'link-' + Math.random().toString(36).substring(2, 9);
                
                // Store original styles
                originalStyles[linkId] = {
                    backgroundColor: $link.css('background-color'),
                    color: $link.css('color'),
                    border: $link.css('border'),
                    fontSize: $link.css('font-size'),
                    fontWeight: $link.css('font-weight'),
                    padding: $link.css('padding'),
                    display: $link.css('display'),
                    textDecoration: $link.css('text-decoration')
                };
                
                // Store the linkId on the element
                $link.attr('data-highlight-id', linkId);
                
                // Apply highlighting styles
                $link.css({
                    'background-color': '#ffff00', // Yellow background
                    'color': '#000000', // Black text
                    'border': '2px solid #000000', // Black border
                    'font-size': '26px', // Larger font size
                    'font-weight': 'bold',
                    'padding': '8px 10px', // Increased padding for better visibility
                    'display': 'inline-block',
                    'text-decoration': 'underline',
                    'border-radius': '3px',
                    'margin': '4px 2px', // Increased margin
                    'line-height': '1.4'  // Better line height for readability
                });
            });
            
            // Announce to screen readers
            if (window.exa11y && window.exa11y.ScreenReader) {
                window.exa11y.ScreenReader.announce('Links highlighted for better visibility', 'polite');
            }
            
            isHighlightActive = true;
            if (window.exa11yStorage) {
                window.exa11yStorage.save('highlightLinks', '1');
            }

            // Log if debug is enabled
            if (window.exa11yDebug) {
                window.exa11yDebug.log('Link highlighting enabled');
            }
        },
        
        /**
         * Disable link highlighting and restore original styles
         */
        disableHighlighting: function() {
            // Get all highlighted links
            const links = $('a[data-highlight-id]');
            
            // Restore original styles
            links.each(function() {
                const $link = $(this);
                const linkId = $link.attr('data-highlight-id');
                
                if (originalStyles[linkId]) {
                    $link.css({
                        'background-color': originalStyles[linkId].backgroundColor,
                        'color': originalStyles[linkId].color,
                        'border': originalStyles[linkId].border,
                        'font-size': originalStyles[linkId].fontSize,
                        'font-weight': originalStyles[linkId].fontWeight,
                        'padding': originalStyles[linkId].padding,
                        'display': originalStyles[linkId].display,
                        'text-decoration': originalStyles[linkId].textDecoration
                    });
                }
                
                // Remove the data attribute
                $link.removeAttr('data-highlight-id');
            });
            
            // Announce to screen readers
            if (window.exa11y && window.exa11y.ScreenReader) {
                window.exa11y.ScreenReader.announce('Link highlighting turned off', 'polite');
            }
            
            isHighlightActive = false;
            if (window.exa11yStorage) {
                window.exa11yStorage.save('highlightLinks', '0');
            }

            // Log if debug is enabled
            if (window.exa11yDebug) {
                window.exa11yDebug.log('Link highlighting disabled');
            }
        }
    };
    
    // Initialize when the document is ready
    $(document).ready(function() {
        HighlightLinks.init();
    });
      // Make the module available to the main script
    if (typeof window.exa11y === 'undefined') {
        window.exa11y = {};
    }
    
    window.exa11y.HighlightLinks = HighlightLinks;
    
    
})(jQuery);
