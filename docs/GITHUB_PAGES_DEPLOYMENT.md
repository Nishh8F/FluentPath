# GitHub Pages Deployment Guide

This guide explains how to deploy FluentPath to GitHub Pages with a separate backend.

## Overview

FluentPath is a full-stack application with:
- **Frontend**: Static HTML/CSS/JavaScript (can be hosted on GitHub Pages)
- **Backend**: PHP with MySQL (requires a backend service)

## Step 1: Deploy Frontend to GitHub Pages

### 1.1 Create a GitHub Repository
```bash
# Navigate to your project folder
cd FluentPath

# Initialize git (if not already done)
git init

# Add remote
git remote add origin https://github.com/YOUR_USERNAME/fluentpath.git

# Create main branch and push
git branch -M main
git add .
git commit -m "Initial commit"
git push -u origin main
```

### 1.2 Enable GitHub Pages
1. Go to your GitHub repository settings
2. Navigate to **Pages** section
3. Select **Deploy from a branch**
4. Choose **main** branch and **root** folder
5. Save

Your frontend will be available at: `https://YOUR_USERNAME.github.io/fluentpath/`

## Step 2: Deploy Backend

You need to host the PHP backend separately. Choose one:

### Option A: Heroku (Recommended for PHP)
1. Create a [Heroku account](https://www.heroku.com)
2. Install [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli)
3. Create a new app:
   ```bash
   heroku create your-app-name
   heroku addons:create cleardb:ignite
   ```
4. Get your database URL and update your `.env`
5. Deploy:
   ```bash
   git push heroku main
   ```

### Option B: Railway.app
1. Create a [Railway account](https://railway.app)
2. Connect your GitHub repository
3. Add MySQL service
4. Set environment variables
5. Deploy automatically

### Option C: Render
1. Create a [Render account](https://render.com)
2. Create a new Web Service
3. Connect your GitHub repository
4. Set environment variables
5. Deploy

## Step 3: Configure Frontend to Use Remote Backend

Once your backend is deployed, update the API URL in your GitHub Pages site:

### Method 1: Local Storage (User-configurable)
1. Open the deployed site: `https://YOUR_USERNAME.github.io/fluentpath/`
2. Open browser console (F12)
3. Run:
   ```javascript
   localStorage.setItem('fluentpath_api_url', 'https://your-backend-url.herokuapp.com');
   location.reload();
   ```

### Method 2: Update index.html before deploying
Edit the `API_BASE_URL` in index.html:
```javascript
const API_BASE_URL = 'https://your-backend-url.herokuapp.com';
```

Then commit and push to update the GitHub Pages site.

## Step 4: Set Up Backend Database

Once your backend is running:
1. Navigate to: `https://your-backend-url.herokuapp.com/setup_progress_db.php`
2. This will create all necessary tables

## Environment Variables for Backend

Your backend hosting service needs these environment variables:

```env
DB_HOST=your-mysql-host
DB_PORT=3306
DB_NAME=fluentpath
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```

## CORS Configuration

Your backend already has CORS headers set. If you encounter CORS issues:
1. Check that your backend allows the GitHub Pages origin
2. Update the `Access-Control-Allow-Origin` header in `api.php` and `auth.php`:
   ```php
   header("Access-Control-Allow-Origin: https://YOUR_USERNAME.github.io");
   ```

## Local Testing

For local development:
1. Start XAMPP (ensure Apache and MySQL are running)
2. Create `.env` file with local credentials
3. Navigate to `http://localhost/FluentPath/`
4. No additional configuration needed

## Troubleshooting

**API calls returning errors?**
- Check browser console (F12 → Console tab)
- Verify backend URL is correct
- Ensure backend CORS headers allow your GitHub Pages domain

**Database connection issues?**
- Verify environment variables are set correctly on your backend service
- Check database credentials in `.env`
- Run `setup_progress_db.php` to initialize tables

**Blank page or 404?**
- Verify GitHub Pages is enabled in repository settings
- Check branch is set to `main` or `master` (whichever you're using)
- Clear browser cache (Ctrl+Shift+Delete)

## Support

For issues, check the main [README.md](../README.md) or create an issue on GitHub.
