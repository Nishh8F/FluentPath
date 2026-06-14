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

// Globally override fetch to ALWAYS include credentials
// This is critical for cross-origin sessions and cookies to work (e.g., GitHub Pages to Azure)
const originalFetch = window.fetch;
window.fetch = async function(resource, config) {
    if (!config) {
        config = {};
    }
    config.credentials = 'include';
    return originalFetch(resource, config);
};
