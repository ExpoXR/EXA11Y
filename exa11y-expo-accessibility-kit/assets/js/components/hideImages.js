/**
 * Hide Images Component
 * 
 * This component adds functionality to hide all images and videos on the page
 * for a distraction-free reading experience. This includes background images and videos.
 */

(function($) {
    'use strict';
    
    // Store the current state of image/video visibility
    let isMediaHidden = false;
    
    // Store elements that will be hidden/shown
    let mediaElements = [];
    
    // Store for inline styles we'll add to the page
    let styleEl = null;
    
    const HideImages = {
        /**
         * Initialize the hide images functionality
         */
        init: function() {
            // Only initialize if the feature is enabled in settings
            if (typeof exa11y !== 'undefined' && exa11y.hideImagesActive) {
                this.bindEvents();
                this.restoreState();

                if (window.exa11yDebug) {
                    window.exa11yDebug.log('Hide Images component initialized');
                }
            }
        },

        restoreState: function() {
            if (window.exa11yStorage && window.exa11yStorage.get('hideImages') === '1') {
                HideImages.hideMedia();
                $('.exa11y-btn-hide-images').addClass('active');
            }
        },
        
        /**
         * Bind event listeners
         */
        bindEvents: function() {
            // Handle hide images button click
            $(document).on('click', '.exa11y-btn-hide-images', function() {
                if (isMediaHidden) {
                    HideImages.showMedia();
                    $(this).removeClass('active');
                } else {
                    HideImages.hideMedia();
                    $(this).addClass('active');
                }
            });
            
            // Add event listener for the "Reset" button
            $(document).on('click', '.exa11y-btn-reset', function() {
                if (isMediaHidden) {
                    HideImages.showMedia();
                    $('.exa11y-btn-hide-images').removeClass('active');
                }
            });
        },
        
        /**
         * Find all media elements on the page
         */
        findMediaElements: function() {
            // Only collect elements if we haven't done so yet
            if (mediaElements.length === 0) {
                // Images (exclude the accessibility menu images)
                const images = $('img').not('.exa11y-accessibility-panel img, .exa11y-accessibility-toggle img');
                
                // Videos and iframes (often used for videos like YouTube)
                const videos = $('video, iframe[src*="youtube"], iframe[src*="vimeo"], iframe[src*="dailymotion"], .wp-video, .wp-block-video, .wp-block-embed iframe');
                
                // Background images in divs - find elements with background-image CSS
                const bgImages = $('*').filter(function() {
                    const bgImage = $(this).css('background-image');
                    const bg = $(this).css('background');
                    return (bgImage && bgImage !== 'none' && !bgImage.includes('gradient')) || 
                           (bg && bg !== 'none' && bg.includes('url')) && 
                           !$(this).hasClass('exa11y-accessibility-toggle') && 
                           !$(this).parents('.exa11y-accessibility-panel').length;
                });
                
                // Store original states for elements with background images
                bgImages.each(function() {
                    const $elem = $(this);
                    $elem.attr('data-original-bg-image', $elem.css('background-image'));
                    $elem.attr('data-original-bg', $elem.css('background'));
                });
                
                // Find HTML5 video backgrounds (often used in modern themes)
                const videoBackgrounds = $('.background-video-container, .video-background, [class*="bg-video"], [class*="video-bg"], .wp-block-cover__video-background').filter(function() {
                    return !$(this).parents('.exa11y-accessibility-panel').length;
                });
                
                // Combine all media elements
                mediaElements = [...images, ...videos, ...bgImages, ...videoBackgrounds];
            }
            
            return mediaElements;
        },
        
        /**
         * Creates CSS to hide pseudo-element backgrounds
         */
        createBackgroundBlockingStyles: function() {
            if (!styleEl) {
                styleEl = $('<style id="exa11y-hide-bg-images"></style>').appendTo('head');
            }
            
            const cssRules = `
                *:not(.exa11y-accessibility-panel *):not(.exa11y-accessibility-toggle) {
                    background-image: none !important;
                    background-video: none !important;
                }
                *:before, *:after {
                    background-image: none !important;
                    background: none !important;
                }
                [data-background], [data-bg], [class*="background-image"], [style*="background-image"],
                [style*="background:url"], [style*="background-image:url"] {
                    background-image: none !important;
                    background: none !important;
                }
                video.background, .background-video, .video-bg, .video-background,
                .wp-block-cover__video-background, div[class*="video-bg"], div[class*="bg-video"] {
                    opacity: 0 !important;
                    visibility: hidden !important;
                }
            `;
            
            styleEl.html(cssRules);
        },
        
        /**
         * Remove the background blocking styles
         */
        removeBackgroundBlockingStyles: function() {
            if (styleEl) {
                styleEl.remove();
                styleEl = null;
            }
        },
        
        /**
         * Hide all media elements on the page
         */
        hideMedia: function() {
            // Find all media elements if we haven't already
            this.findMediaElements();
            
            // Add CSS to block all background images including those in pseudo-elements
            this.createBackgroundBlockingStyles();
            
            // Hide images and videos
            $(mediaElements).each(function() {
                const $elem = $(this);
                
                // For elements with background images
                if ($elem.attr('data-original-bg-image') || $elem.attr('data-original-bg')) {
                    $elem.css({
                        'background-image': 'none',
                        'background': $elem.css('background').replace(/url\([^)]+\)/g, 'none')
                    });
                } 
                // For HTML5 video backgrounds
                else if ($elem.hasClass('background-video-container') || 
                         $elem.hasClass('video-background') || 
                         $elem.attr('class') && ($elem.attr('class').includes('bg-video') || $elem.attr('class').includes('video-bg')) ||
                         $elem.hasClass('wp-block-cover__video-background')) {
                    $elem.css({
                        'opacity': '0',
                        'visibility': 'hidden'
                    });
                    $elem.find('video').css({
                        'opacity': '0',
                        'visibility': 'hidden'
                    });
                }
                // For regular elements (images, videos, iframes)
                else {
                    $elem.css({
                        'opacity': '0',
                        'visibility': 'hidden',
                        'position': 'absolute',
                        'width': '1px',
                        'height': '1px',
                        'overflow': 'hidden'
                    }).attr('aria-hidden', 'true');
                    
                    // Add a placeholder for images with appropriate text
                    if ($elem.is('img') && !$elem.siblings('.exa11y-img-placeholder').length) {
                        const altText = $elem.attr('alt') || 'Image';
                        $elem.after('<span class="exa11y-img-placeholder">[' + altText + ']</span>');
                    }
                    
                    // Add a placeholder for videos with appropriate text
                    if (($elem.is('video') || $elem.is('iframe')) && !$elem.siblings('.exa11y-video-placeholder').length) {
                        $elem.after('<span class="exa11y-video-placeholder">[Video content hidden]</span>');
                    }
                }
            });
            
            // Announce to screen readers
            if (window.exa11y && window.exa11y.ScreenReader) {
                window.exa11y.ScreenReader.announce('Images and videos hidden for distraction-free reading', 'polite');
            }
            
            isMediaHidden = true;
            if (window.exa11yStorage) {
                window.exa11yStorage.save('hideImages', '1');
            }

            // Log if debug is enabled
            if (window.exa11yDebug) {
                window.exa11yDebug.log('Media elements hidden');
            }
        },
        
        /**
         * Show all previously hidden media elements
         */
        showMedia: function() {
            // Remove the background blocking styles
            this.removeBackgroundBlockingStyles();
            
            // Remove placeholders
            $('.exa11y-img-placeholder, .exa11y-video-placeholder').remove();
            
            // Show all media elements
            $(mediaElements).each(function() {
                const $elem = $(this);
                
                // For elements with background images
                if ($elem.attr('data-original-bg-image') || $elem.attr('data-original-bg')) {
                    if ($elem.attr('data-original-bg-image')) {
                        $elem.css('background-image', $elem.attr('data-original-bg-image'));
                    }
                    if ($elem.attr('data-original-bg')) {
                        $elem.css('background', $elem.attr('data-original-bg'));
                    }
                } 
                // For HTML5 video backgrounds
                else if ($elem.hasClass('background-video-container') || 
                         $elem.hasClass('video-background') || 
                         $elem.attr('class') && ($elem.attr('class').includes('bg-video') || $elem.attr('class').includes('video-bg')) ||
                         $elem.hasClass('wp-block-cover__video-background')) {
                    $elem.css({
                        'opacity': '',
                        'visibility': ''
                    });
                    $elem.find('video').css({
                        'opacity': '',
                        'visibility': ''
                    });
                }
                // For regular elements (images, videos, iframes)
                else {
                    $elem.css({
                        'opacity': '',
                        'visibility': '',
                        'position': '',
                        'width': '',
                        'height': '',
                        'overflow': ''
                    }).removeAttr('aria-hidden');
                }
            });
            
            // Announce to screen readers
            if (window.exa11y && window.exa11y.ScreenReader) {
                window.exa11y.ScreenReader.announce('Images and videos are now visible', 'polite');
            }
            
            isMediaHidden = false;
            if (window.exa11yStorage) {
                window.exa11yStorage.save('hideImages', '0');
            }

            // Log if debug is enabled
            if (window.exa11yDebug) {
                window.exa11yDebug.log('Media elements made visible');
            }
        }
    };
    
    // Initialize when the document is ready
    $(document).ready(function() {
        HideImages.init();
    });
      // Make the module available to the main script
    if (typeof window.exa11y === 'undefined') {
        window.exa11y = {};
    }
    
    window.exa11y.HideImages = HideImages;
    
    })(jQuery);
