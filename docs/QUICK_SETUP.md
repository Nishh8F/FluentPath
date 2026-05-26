# Quick Setup: GitHub Pages + Backend API

Once you've deployed the frontend to GitHub Pages and backend to a service like Heroku, use this simple method to connect them:

## Option 1: Browser Console (Easiest, No Code Changes)

1. Go to your GitHub Pages site: `https://YOUR_USERNAME.github.io/fluentpath/`
2. Open Developer Tools: Press `F12` or right-click → **Inspect**
3. Click the **Console** tab
4. Paste this command (replace with your backend URL):
   ```javascript
   localStorage.setItem('fluentpath_api_url', 'https://your-backend-url.herokuapp.com');
   location.reload();
   ```
5. Done! The site will now use your backend

**Note**: This setting is saved per browser. Each user on a different browser/device will need to do this once.

## Option 2: Update Source Code (Permanent)

Edit `api-config.js` in your repository:

```javascript
// Change this line:
const API_BASE_URL = localStorage.getItem('fluentpath_api_url') || (
    window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
        ? 'http://localhost/FluentPath'
        : '' // Use same origin for GitHub Pages
);

// To this (replace with your URL):
const API_BASE_URL = 'https://your-backend-url.herokuapp.com';
```

Then commit and push:
```bash
git add api-config.js
git commit -m "Update backend API URL"
git push origin main
```

## Verify It's Working

1. Open your site in browser
2. Open Developer Tools (F12)
3. Go to **Network** tab
4. Try to login or perform any action
5. You should see network requests going to your backend URL

If you see errors:
- Check the **Console** tab for error messages
- Verify your backend URL is correct
- Make sure backend is running and accessible
- Check backend CORS headers allow your GitHub Pages domain

## Example Backend URLs

**Heroku**: `https://your-app-name.herokuapp.com`
**Railway**: `https://your-app-name-production.up.railway.app`
**Render**: `https://your-app-name.onrender.com`

See [GITHUB_PAGES_DEPLOYMENT.md](./GITHUB_PAGES_DEPLOYMENT.md) for full deployment guide.
