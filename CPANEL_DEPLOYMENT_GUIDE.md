# cPanel Git Deployment Guide for Bros Cafe

## Overview
This guide will help you set up automatic deployment from GitHub to your cPanel hosting using Git Version Control.

## Prerequisites
- cPanel account with Git Version Control feature
- GitHub repository (LinuxAdona/Bros-Cafe)
- Database credentials from your hosting provider

## Step-by-Step Setup

### 1. Access cPanel Git Version Control

1. Log in to your cPanel account
2. Navigate to **Files** section
3. Click on **Git™ Version Control**

### 2. Create Repository in cPanel

1. Click **Create** button
2. Fill in the following details:
   - **Clone URL**: `https://github.com/LinuxAdona/Bros-Cafe.git`
   - **Repository Path**: `/home2/broscafe/repositories/Bros-Cafe`
   - **Repository Name**: `Bros-Cafe`
   - **Branch**: `linux` (or your main branch)

3. Click **Create** button

### 3. Deploy to Public HTML

After the repository is cloned:

1. Go back to **Git™ Version Control** in cPanel
2. Find your `Bros-Cafe` repository
3. Click **Manage** button
4. Click **Pull or Deploy** tab
5. Click **Update from Remote** to pull latest changes
6. The `.cpanel.yml` file will automatically deploy files to `/home2/broscafe/public_html/`

### 4. Configure Database Connection

1. In cPanel, go to **Databases** → **MySQL Databases**
2. Create a new database or note your existing one
3. Create a database user with password
4. Add user to database with ALL PRIVILEGES

5. Edit the database configuration file:
   - Navigate to `/home2/broscafe/public_html/config/database.php`
   - Update with your cPanel database credentials:

```php
<?php
class Database {
    private $host = "localhost";  // Usually 'localhost' on cPanel
    private $db_name = "broscafe_yourusername_db";  // Your cPanel database name
    private $username = "broscafe_dbuser";  // Your cPanel database username
    private $password = "your_secure_password";  // Your database password
    private $conn;
    
    // ... rest of the file
}
```

### 5. Import Database

1. In cPanel, go to **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Choose file: `/home2/broscafe/public_html/database/broscafe_db.sql`
5. Click **Go** to import

### 6. Verify Permissions

The `.cpanel.yml` file automatically sets these permissions:
- `/public/assets/images/products/` → 777 (for product image uploads)
- `/config/database.php` → 644 (secure config)
- All other directories → 755
- All other files → 644

If needed, verify in cPanel **File Manager**:
1. Navigate to `/home2/broscafe/public_html/public/assets/images/products/`
2. Right-click → **Permissions**
3. Ensure it's set to `777` (rwxrwxrwx)

### 7. Configure .htaccess (if needed)

Create or verify `.htaccess` in `/home2/broscafe/public_html/`:

```apache
# Enable error reporting (disable in production)
# php_flag display_errors on

# Set default index
DirectoryIndex index.php index.html

# Prevent directory browsing
Options -Indexes

# Enable rewrite engine
RewriteEngine On

# Redirect to HTTPS (recommended for production)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<Files "database.php">
    Order allow,deny
    Deny from all
</Files>
```

### 8. Test Your Deployment

1. Visit your domain: `http://yourdomain.com` or `http://broscafe.com`
2. You should see the Bros Cafe homepage
3. Test login functionality
4. Verify database connectivity

### 9. Future Updates

To deploy new changes:

1. **Push changes to GitHub** from your local machine:
   ```bash
   git add .
   git commit -m "Your commit message"
   git push origin linux
   ```

2. **Deploy in cPanel**:
   - Go to **Git™ Version Control**
   - Click **Manage** on Bros-Cafe repository
   - Click **Pull or Deploy** tab
   - Click **Update from Remote**
   - The `.cpanel.yml` will automatically copy files to public_html

## Automatic Deployment (Optional)

To enable automatic deployment on every push:

1. In cPanel Git interface, click **Manage** on your repository
2. Find the **Deployment Script** section
3. Enable **Run deployment script automatically after 'pull'**

Now whenever you push to GitHub and manually pull in cPanel, it will auto-deploy!

## SSH Deployment (Advanced - If SSH Access Available)

If you have SSH access, you can set up a webhook:

1. Generate SSH key in cPanel Terminal
2. Add deploy key to GitHub repository
3. Set up webhook in GitHub pointing to your server
4. Configure automatic pull on webhook trigger

## Troubleshooting

### Issue: Files not deploying
- **Solution**: Check `.cpanel.yml` syntax, ensure paths are correct

### Issue: Database connection failed
- **Solution**: Verify database credentials in `config/database.php`
- Check database user has proper privileges

### Issue: Images not uploading
- **Solution**: Check `/public/assets/images/products/` has 777 permissions

### Issue: 500 Internal Server Error
- **Solution**: Check PHP version compatibility (requires PHP 7.4+)
- Enable error reporting temporarily to see specific errors
- Check file permissions

### Issue: Styles/Scripts not loading
- **Solution**: Clear browser cache
- Verify all asset files were copied
- Check file paths are correct

## Security Recommendations

1. **Change default credentials** immediately after deployment
2. **Use HTTPS** - Enable SSL certificate in cPanel (Let's Encrypt is free)
3. **Regular backups** - Use cPanel Backup feature
4. **Update database password** - Use strong, unique password
5. **Disable error display** in production - Set `display_errors = Off` in PHP
6. **Keep software updated** - Regularly pull latest updates from GitHub

## File Structure After Deployment

```
/home2/broscafe/public_html/
├── config/
│   └── database.php
├── database/
│   └── broscafe_db.sql
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   │       └── products/  (777 permissions)
│   └── pages/
│       ├── usr/
│       └── ...
├── src/
│   ├── services/
│   ├── fpdf.php
│   └── fpdf_fonts/
├── index.php
├── .htaccess
└── *.shtml (error pages)
```

## Support

For issues specific to:
- **Hosting**: Contact your hosting provider's support
- **Application**: Check GitHub repository issues
- **Database**: Verify credentials and table structure

---

**Last Updated**: November 25, 2025
**Version**: 1.0
