// API Configuration - Change this URL when deploying to GitHub Pages
// For local development: http://localhost/FluentPath
// For production: https://your-backend-api.herokuapp.com (or your backend URL)
const API_BASE_URL = localStorage.getItem('fluentpath_api_url') || (
    window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
        ? 'http://localhost/FluentPath'
        : '' // Use same origin for GitHub Pages
);

// Helper function to build API URLs
function getApiUrl(endpoint) {
    if (API_BASE_URL === '') {
        return endpoint; // Same origin
    }
    return API_BASE_URL + '/' + endpoint.replace(/^\//, '');
}
