# Backend Deployment Checklist

Use this checklist when deploying the FluentPath backend to a service like Heroku, Railway, or Render.

## Before Deployment

- [ ] Ensure all PHP files are updated (using `config.php` for database credentials)
- [ ] Create `.env` file with production database credentials
- [ ] Test locally with `.env` file to ensure it works
- [ ] Verify all database tables are created (run `setup_progress_db.php`)

## Heroku Deployment

### 1. Create Procfile
Create a file named `Procfile` in your project root:
```
web: vendor/bin/heroku-php-apache2 ./
```

### 2. Create composer.json (if not exists)
```bash
composer init
# or create manually with:
```
```json
{
    "require": {
        "php": "^7.4"
    }
}
```

### 3. Deploy
```bash
heroku create your-app-name
heroku addons:create cleardb:ignite
heroku config:set DB_HOST=your-db-host
heroku config:set DB_PORT=3306
heroku config:set DB_NAME=your-db-name
heroku config:set DB_USERNAME=your-db-user
heroku config:set DB_PASSWORD=your-db-password
git push heroku main
```

### 4. Initialize Database
```bash
heroku run php setup_progress_db.php
```

## Railway Deployment

### 1. Connect Repository
- Go to [Railway.app](https://railway.app)
- Connect your GitHub repository

### 2. Add MySQL Service
- Click "+ New Service"
- Select MySQL
- Railway will auto-populate `DB_HOST`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`

### 3. Set Environment Variables
- Go to your service settings
- Add variables: `APP_ENV=production`, `APP_DEBUG=false`
- Railway MySQL service variables are auto-set

### 4. Deploy
- Railway auto-deploys on git push
- Check deployment status in dashboard

## Render Deployment

### 1. Connect Repository
- Go to [Render.com](https://render.com)
- Connect your GitHub repository
- Select "Web Service"

### 2. Configure
- Runtime: PHP (or Node with PHP runtime)
- Build command: `composer install`
- Start command: `vendor/bin/heroku-php-apache2 ./`

### 3. Add Environment Variables
```
DB_HOST=your-mysql-host
DB_PORT=3306
DB_NAME=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
APP_ENV=production
```

### 4. Deploy
- Render auto-deploys on git push

## Post-Deployment

- [ ] Run `https://your-backend-url.com/setup_progress_db.php`
- [ ] Test login/registration
- [ ] Check backend logs for errors
- [ ] Verify database tables created successfully
- [ ] Update frontend API URL (see [QUICK_SETUP.md](./QUICK_SETUP.md))

## Troubleshooting

**"Connection error" on frontend**
- Verify backend is running: `https://your-backend-url.com/`
- Check CORS headers in `api.php` and `auth.php`
- Verify database credentials in `.env`

**Database not found**
- Run `setup_progress_db.php` to create tables
- Check `DB_NAME` environment variable matches actual database

**500 errors on backend**
- Check service logs for PHP errors
- Verify `config.php` can load `.env` file
- Ensure PDO extension is enabled in PHP

## Security Notes

- [ ] Never commit `.env` file (only `.env.example`)
- [ ] Use strong database passwords in production
- [ ] Enable HTTPS (most services do by default)
- [ ] Update CORS headers to only allow your frontend domain:
  ```php
  header("Access-Control-Allow-Origin: https://your-username.github.io");
  ```
- [ ] Keep PHP and MySQL updated

## Monitoring

Set up monitoring/alerts for:
- Backend uptime
- Database connectivity
- Error rates
- Most services offer built-in monitoring dashboards
