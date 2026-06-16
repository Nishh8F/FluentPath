// API Configuration - Change this URL when deploying to GitHub Pages
// For local development: http://localhost/FluentPath
// For production: https://your-backend-api.herokuapp.com (or your backend URL)
let savedApiUrl = null;
try {
    if (window.localStorage) {
        savedApiUrl = window.localStorage.getItem('fluentpath_api_url');
    }
} catch (e) {
    // Ignore, WebView has storage disabled
}

const API_BASE_URL = savedApiUrl || (
    window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
        ? window.location.origin + '/FluentPath'
        : '' // Use same origin for GitHub Pages
);

// Helper function to build API URLs
function getApiUrl(endpoint) {
    if (API_BASE_URL === '') {
        return endpoint; // Same origin
    }
    return API_BASE_URL + '/' + endpoint.replace(/^\//, '');
}

// Globally override fetch to ALWAYS include credentials and token
const originalFetch = window.fetch;
window.fetch = async function(resource, config) {
    if (!config) {
        config = {};
    }
    config.credentials = 'include';
    
    // Auto-attach auth_token if available
    let token = null;
    try {
        if (window.localStorage) {
            token = window.localStorage.getItem('fluentpath_token');
        }
    } catch(e) {}
    
    if (window.AppInventor) {
        const msg = window.AppInventor.getWebViewString() || "";
        if (msg.startsWith("LOAD_TOKEN:")) {
             const parts = msg.split("|");
             token = parts[0].split(":")[1] || token;
        }
    }
    
    // Fallback to globally stored session token (set during login/registration)
    token = window.fluentpath_auth_token || token;
    
    if (token) {
        if (!config.headers) config.headers = {};
        // If config.headers is Headers object, we need to append
        if (config.headers instanceof Headers) {
            config.headers.append('Authorization', 'Bearer ' + token);
        } else {
            config.headers['Authorization'] = 'Bearer ' + token;
        }
    }
    
    return originalFetch(resource, config);
};
