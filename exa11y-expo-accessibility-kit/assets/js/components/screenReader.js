/**
 * Screen Reader Support Module
 * 
 * This module enhances the accessibility of exa11y by providing
 * comprehensive screen reader support including:
 * - ARIA live regions for dynamic content
 * - Status announcements
 * - Setting changes notifications
 * - Modal dialog accessibility
 * - Proper labeling of interactive elements
 */

(function() {
    'use strict';

    // Maintain a reference to announcer elements to avoid creating duplicates
    let politeAnnouncer = null;
    let assertiveAnnouncer = null;
    
    const ScreenReader = {
        /**
         * Initialize the screen reader support
         */
        init: function() {
            // Create the screen reader announcers
            this.createAnnouncers();
            
            // Add ARIA attributes to important elements
            this.setupARIAAttributes();
            
            // Set up event listeners for state changes
            this.bindStateChangeEvents();
            
            // Log initialization if debug is enabled
            if (window.exa11yDebug) {
                window.exa11yDebug.log('Screen reader support initialized');
            }
        },
        
        /**
         * Create screen reader announcer elements
         * These hidden elements use aria-live to announce content changes to screen readers
         */
        createAnnouncers: function() {
            // Create a polite announcer for non-urgent messages
            if (!politeAnnouncer) {
                politeAnnouncer = document.createElement('div');
                politeAnnouncer.id = 'exa11y-sr-announcer-polite';
                politeAnnouncer.className = 'sr-only';
                politeAnnouncer.setAttribute('aria-live', 'polite');
                politeAnnouncer.setAttribute('aria-atomic', 'true');
                document.body.appendChild(politeAnnouncer);
            }
            
            // Create an assertive announcer for urgent messages
            if (!assertiveAnnouncer) {
                assertiveAnnouncer = document.createElement('div');
                assertiveAnnouncer.id = 'exa11y-sr-announcer-assertive';
                assertiveAnnouncer.className = 'sr-only';
                assertiveAnnouncer.setAttribute('aria-live', 'assertive');
                assertiveAnnouncer.setAttribute('aria-atomic', 'true');
                document.body.appendChild(assertiveAnnouncer);
            }
        },
        
        /**
         * Add appropriate ARIA attributes to key interactive elements
         */
        setupARIAAttributes: function() {
            // Set up the main accessibility toggle button
            const toggleButton = document.querySelector('.exa11y-accessibility-toggle');
            if (toggleButton) {
                if (!toggleButton.getAttribute('aria-label')) {
                    toggleButton.setAttribute('aria-label', 'Accessibility Options');
                }
                toggleButton.setAttribute('aria-haspopup', 'dialog');
                
                const panel = document.querySelector('.exa11y-accessibility-panel');
                if (panel) {
                    const panelId = 'exa11y-a11y-panel';
                    panel.id = panelId;
                    toggleButton.setAttribute('aria-controls', panelId);
                    toggleButton.setAttribute('aria-expanded', 'false');
                    
                    // Set up the panel with proper dialog role
                    panel.setAttribute('role', 'dialog');
                    panel.setAttribute('aria-modal', 'true');
                    panel.setAttribute('aria-labelledby', 'exa11y-panel-title');
                    
                    // Find or create a title element
                    let titleElement = panel.querySelector('#exa11y-panel-title');
                    if (!titleElement) {
                        // Look for any heading to use as the title
                        const heading = panel.querySelector('h1, h2, h3, h4');
                        if (heading) {
                            heading.id = 'exa11y-panel-title';
                        } else {
                            // If no heading found, create a visually hidden one
                            titleElement = document.createElement('h2');
                            titleElement.id = 'exa11y-panel-title';
                            titleElement.className = 'sr-only';
                            titleElement.textContent = 'Accessibility Options';
                            panel.insertBefore(titleElement, panel.firstChild);
                        }
                    }
                }
            }
            
            // Set up buttons for proper screen reader interaction
            const buttons = document.querySelectorAll('.exa11y-accessibility-option button');
            buttons.forEach(button => {
                // Skip if button already has aria-label
                if (button.getAttribute('aria-label')) return;
                
                // Try to extract a meaningful label from context
                let label = '';
                
                // Check for icon-only buttons that need labels
                if (button.textContent.trim() === '' || button.querySelector('span.dashicons, i.fas, i.fa')) {
                    // Try to find a label from parent or nearby text
                    const optionLabel = button.closest('.exa11y-accessibility-option')
                        ?.querySelector('.exa11y-option-label')?.textContent.trim();
                    
                    if (optionLabel) {
                        label = optionLabel;
                        button.setAttribute('aria-label', label);
                    }
                }
                
                // Make state-changing buttons have proper pressed state
                if (button.classList.contains('exa11y-toggle-button')) {
                    button.setAttribute('aria-pressed', button.classList.contains('active') ? 'true' : 'false');
                }
            });
            
            // Set up sliders with proper ARIA attributes
            const sliders = document.querySelectorAll('input[type="range"].exa11y-slider');
            sliders.forEach(slider => {
                const sliderId = slider.id || 'exa11y-slider-' + Math.random().toString(36).substring(2, 9);
                slider.id = sliderId;
                
                // Find or create a label
                let label = document.querySelector(`label[for="${sliderId}"]`);
                if (!label) {
                    // Try to find a nearby text element to use as label
                    const labelText = slider.closest('.exa11y-slider-control')
                        ?.querySelector('.exa11y-slider-label')?.textContent.trim();
                    
                    if (labelText) {
                        label = document.createElement('label');
                        label.setAttribute('for', sliderId);
                        label.className = 'sr-only';
                        label.textContent = labelText;
                        slider.parentNode.insertBefore(label, slider);
                    }
                }
                
                // Add min/max/now values for screen readers
                const min = slider.getAttribute('min') || '0';
                const max = slider.getAttribute('max') || '100';
                const value = slider.value || '0';
                
                slider.setAttribute('aria-valuemin', min);
                slider.setAttribute('aria-valuemax', max);
                slider.setAttribute('aria-valuenow', value);
                slider.setAttribute('aria-valuetext', value + 'x');
            });
        },
        
        /**
         * Bind to state change events to announce changes to screen readers
         */
        bindStateChangeEvents: function() {
            // Announce when panel opens or closes
            const toggleButton = document.querySelector('.exa11y-accessibility-toggle');
            if (toggleButton) {
                toggleButton.addEventListener('click', () => {
                    const panel = document.querySelector('.exa11y-accessibility-panel');
                    if (panel) {
                        const isOpen = panel.classList.contains('active');
                        toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                        
                        if (isOpen) {
                            this.announce('Accessibility options panel opened', 'polite');
                        } else {
                            this.announce('Accessibility options panel closed', 'polite');
                        }
                    }
                });
            }
            
            // Announce when settings change
            document.addEventListener('click', (e) => {
                // Handle toggle buttons
                if (e.target.closest('.exa11y-toggle-button')) {
                    const button = e.target.closest('.exa11y-toggle-button');
                    const isActive = button.classList.contains('active');
                    
                    // Get the option name from a nearby element
                    const option = button.closest('.exa11y-accessibility-option');
                    let optionName = '';
                    
                    if (option) {
                        optionName = option.querySelector('.exa11y-option-label')?.textContent.trim() || '';
                    }
                    
                    if (optionName) {
                        // Set the correct ARIA state
                        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                        
                        // Announce the change
                        const message = optionName + ' ' + (isActive ? 'enabled' : 'disabled');
                        this.announce(message, 'polite');
                    }
                }
                
                // Handle reset button
                if (e.target.closest('.exa11y-btn-reset')) {
                    this.announce('All accessibility settings have been reset to default', 'polite');
                }
            });
            
            // Announce slider changes
            document.addEventListener('input', (e) => {
                if (e.target.matches('input[type="range"].exa11y-slider')) {
                    const slider = e.target;
                    
                    // Update ARIA value attributes
                    slider.setAttribute('aria-valuenow', slider.value);
                    slider.setAttribute('aria-valuetext', slider.value + 'x');
                    
                    // Find a label for the slider
                    let sliderName = '';
                    const label = document.querySelector(`label[for="${slider.id}"]`);
                    
                    if (label) {
                        sliderName = label.textContent.trim();
                    } else {
                        // Try to find a nearby text element to use as label
                        sliderName = slider.closest('.exa11y-slider-control')
                            ?.querySelector('.exa11y-slider-label')?.textContent.trim() || '';
                    }
                    
                    if (sliderName) {
                        // Debounce announcements to avoid too many rapid announcements
                        clearTimeout(slider._announceTimeout);
                        slider._announceTimeout = setTimeout(() => {
                            this.announce(sliderName + ' set to ' + slider.value + 'x', 'polite');
                        }, 500);
                    }
                }
            });
        },
        
        /**
         * Announce a message to screen readers
         * @param {string} message - The message to announce
         * @param {string} priority - Priority level: 'polite' (default) or 'assertive'
         */
        announce: function(message, priority = 'polite') {
            // Make sure announcers exist
            if (!politeAnnouncer || !assertiveAnnouncer) {
                this.createAnnouncers();
            }
            
            // Select the appropriate announcer based on priority
            const announcer = priority === 'assertive' ? assertiveAnnouncer : politeAnnouncer;
            
            // Setting textContent to empty string, then to the message ensures 
            // the message is announced even if it's the same as the previous message
            announcer.textContent = '';
            
            // Use setTimeout to ensure the DOM update for clearing happens first
            setTimeout(() => {
                announcer.textContent = message;
                
                // Log announcements if debug is active
                if (window.exa11yDebug) {
                    window.exa11yDebug.log('Screen reader announcement: ' + message);
                }
                
                // Clear the announcement after a delay
                setTimeout(() => {
                    announcer.textContent = '';
                }, 3000);
            }, 50);
        }
    };
    
    // Make the ScreenReader module available to the main script
    if (typeof window.exa11y === 'undefined') {
        window.exa11y = {};
    }
    
    window.exa11y.ScreenReader = ScreenReader;
    
    // Initialize when the DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        ScreenReader.init();
    });
})();
