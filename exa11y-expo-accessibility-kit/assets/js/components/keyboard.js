/**
 * Keyboard navigation module for exa11y
 * Enhances accessibility by providing better keyboard navigation support
 */

// Self-executing function to avoid polluting global namespace
(function() {
    'use strict';
    
    // Store the current state of keyboard navigation
    let isUsingKeyboard = false;
    
    const KeyboardNavigation = {
        /**
         * Initialize keyboard navigation features
         */
        init: function() {
            // Add listeners for keyboard and mouse events
            this.addEventListeners();
            
            // Add tabindex to elements that should be focusable
            this.makeElementsFocusable();
            
            // Initialize tab trapping for modal dialogs if they exist
            this.initTabTrapping();
            
            // Setup enhanced focus highlighting for buttons and sliders
            this.setupFocusHighlighting();
        },
        
        /**
         * Add necessary event listeners for keyboard detection
         */
        addEventListeners: function() {
            // Track when keyboard is being used
            document.addEventListener('keydown', function(e) {
                // Only add class if it was a Tab key press
                if (e.key === 'Tab') {
                    isUsingKeyboard = true;
                    document.body.classList.add('exa11y-keyboard-navigation');

                    // Check if accessibility panel is open
                    const panel = document.querySelector('.exa11y-accessibility-panel.active');
                    if (panel) {
                        // If panel is open and focus is outside, move focus to the header
                        if (!panel.contains(document.activeElement)) {
                            e.preventDefault();
                            const header = panel.querySelector('h2');
                            if (header) {
                                header.focus();
                            }
                        }
                    }
                }
            });
            
            // Remove keyboard navigation class when mouse is used
            document.addEventListener('mousedown', function() {
                isUsingKeyboard = false;
                document.body.classList.remove('exa11y-keyboard-navigation');
                
                // Remove any active focus highlight classes when switching to mouse
                document.querySelectorAll('.exa11y-focus-highlight').forEach(function(element) {
                    element.classList.remove('exa11y-focus-highlight');
                });
            });
            
            // Listen for Escape key to close panels
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // Close any open accessibility panels via the header close button.
                    const panel = document.querySelector('.exa11y-accessibility-panel.active');
                    if (panel) {
                        const closeButton = panel.querySelector('.exa11y-panel-close');
                        if (closeButton) {
                            closeButton.click();
                        }
                    }
                }
            });
        },
        
        /**
         * Make important elements focusable by adding tabindex if needed
         */
        makeElementsFocusable: function() {
            // Get all interactive elements in the accessibility panel
            const accessibilityPanel = document.querySelector('.exa11y-accessibility-panel');
            if (!accessibilityPanel) return;

            // Settings and Accessibility header should be first in tab order
            const settingsHeader = accessibilityPanel.querySelector('h2');
            if (settingsHeader) {
                settingsHeader.setAttribute('tabindex', '-1');
                settingsHeader.setAttribute('role', 'heading');
                settingsHeader.setAttribute('aria-level', '2');
            }

            // Main toggle button should be second focusable
            const mainToggle = document.querySelector('.exa11y-accessibility-toggle');
            if (mainToggle) {
                mainToggle.setAttribute('tabindex', '1');
            }

            // Select all potentially interactive elements
            const elements = accessibilityPanel.querySelectorAll(`
                button:not([tabindex]),
                [role="button"]:not([tabindex]),
                a:not([tabindex]),
                input:not([tabindex]),
                select:not([tabindex]),
                textarea:not([tabindex]),
                [data-action]:not([tabindex]),
                .exa11y-button:not([tabindex]),
                .exa11y-slider:not([tabindex]),
                .exa11y-switch:not([tabindex])
            `);

            // Make every interactive control in the grid keyboard-reachable.
            elements.forEach(element => {
                if (element.hasAttribute('disabled') || element.getAttribute('aria-disabled') === 'true') {
                    element.setAttribute('tabindex', '-1');
                } else {
                    element.setAttribute('tabindex', '0');
                }
            });
        },
        
        /**
         * Initialize tab trapping for modal dialogs to improve accessibility
         */
        initTabTrapping: function() {
            const panel = document.querySelector('.exa11y-accessibility-panel');
            if (!panel) return;
            
            // Add a global event listener for tab key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    // Only trap focus if panel is active/open
                    if (!panel.classList.contains('active')) return;

                    const focusableElements = panel.querySelectorAll(
                        'h2[tabindex], button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    
                    if (focusableElements.length === 0) return;
                    
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];
                    
                    // If focus is outside the panel, move it to the first element
                    if (!panel.contains(document.activeElement)) {
                        e.preventDefault();
                        firstElement.focus();
                        return;
                    }
                    
                    // Handle tab cycling within the panel
                    if (e.shiftKey && document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    } else if (!e.shiftKey && document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            });
        },
        
        /**
         * Setup enhanced focus highlighting for buttons and sliders
         * This will apply a special class to the focused element for better visibility
         */
        setupFocusHighlighting: function() {
            // Target interactive elements that should receive enhanced highlight
            document.addEventListener('focusin', function(e) {
                // Only apply enhanced highlight if using keyboard navigation
                if (!isUsingKeyboard) return;
                
                // Remove highlight class from all elements first
                document.querySelectorAll('.exa11y-focus-highlight').forEach(function(element) {
                    element.classList.remove('exa11y-focus-highlight');
                });
                
                const target = e.target;
                
                // Check if the element is a button, slider or other interactive element
                if (
                    target.tagName === 'BUTTON' || 
                    target.hasAttribute('role') && target.getAttribute('role') === 'button' ||
                    target.classList.contains('exa11y-button') ||
                    target.type === 'range' ||
                    target.classList.contains('exa11y-slider-handle') ||
                    target.classList.contains('exa11y-switch') ||
                    target.closest('.exa11y-tile')
                ) {
                    // Add highlight class to the element
                    target.classList.add('exa11y-focus-highlight');
                    
                    // If it's an input inside a container, also add the class to the container for better visibility
                    const container = target.closest('.exa11y-tile');
                    if (container) {
                        container.classList.add('exa11y-option-focused');
                    }
                    
                    // Announce to screen readers which control is currently focused
                    this.announceFocusedElement(target);
                }
            }.bind(this));
            
            // Remove the focus highlight class when focus leaves
            document.addEventListener('focusout', function(e) {
                const container = e.target.closest('.exa11y-tile');
                if (container) {
                    container.classList.remove('exa11y-option-focused');
                }
            });
        },
        
        /**
         * Announce the focused element to screen readers
         * @param {HTMLElement} element - The element that has received focus
         */
        announceFocusedElement: function(element) {
            // Create or get the screen reader announce element
            let announcer = document.getElementById('exa11y-keyboard-announcer');
            if (!announcer) {
                announcer = document.createElement('div');
                announcer.id = 'exa11y-keyboard-announcer';
                announcer.setAttribute('aria-live', 'polite');
                announcer.className = 'sr-only';
                document.body.appendChild(announcer);
            }
            
            // Determine what text to announce based on the element
            let announcement = '';
            
            // Get the accessible name from aria-label, inner text, or title
            const accessibleName = element.getAttribute('aria-label') || 
                                  element.textContent.trim() || 
                                  element.getAttribute('title') ||
                                  '';
            
            if (accessibleName) {
                announcement = accessibleName + ' focused';
            }
            
            // Only announce if we have something meaningful to say
            if (announcement) {
                announcer.textContent = announcement;
                
                // Clear the announcer after a short delay
                setTimeout(function() {
                    announcer.textContent = '';
                }, 1500);
            }
        },
        
        /**
         * Add skip links for keyboard navigation
         */
        addSkipLinks: function() {
            // Only add skip links if they don't already exist
            if (document.querySelector('.exa11y-skip-to-content, .exa11y-skip-link')) return;
            
            const skipLink = document.createElement('a');
            skipLink.className = 'exa11y-skip-to-content screen-reader-text';
            skipLink.href = '#content';
            skipLink.textContent = 'Skip to content';
            
            // Styling now handled by keyboard.css
            
            document.body.insertBefore(skipLink, document.body.firstChild);
        }
    };
    
    // Make the Keyboard Navigation module available to the main script
    if (typeof window.exa11y === 'undefined') {
        window.exa11y = {};
    }
    
    window.exa11y.KeyboardNavigation = KeyboardNavigation;
    
    // Initialize when the DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        KeyboardNavigation.init();
        KeyboardNavigation.addSkipLinks();
    });
})();
