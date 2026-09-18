/**
 * Content Wrapper Module
 * Creates a wrapper around page content for applying accessibility filters
 */

function setupContentWrapper() {
    // Skip if we've already wrapped the content
    if (document.getElementById('exa11y-content-wrapper')) {
        return;
    }
    
    // Create wrapper element
    const wrapper = document.createElement('div');
    wrapper.id = 'exa11y-content-wrapper';
    
    // Get all body children except our accessibility controls, WordPress admin bar, and EXA11Y Menu
    const bodyChildren = Array.from(document.body.children).filter(child => {
        return !child.classList.contains('exa11y-accessibility-controls') && 
               !child.id?.includes('wpadminbar') && 
               !child.classList.contains('exa11y-menu');
    });
    
    // Move all children into the wrapper
    bodyChildren.forEach(child => {
        wrapper.appendChild(child);
    });
    
    // Add wrapper to the body
    document.body.prepend(wrapper);
    
    if (window.exa11yDebug) {
        window.exa11yDebug.log('Content wrapper created');
    }
}

// Make the function available globally
window.exa11ySetupContentWrapper = setupContentWrapper;
