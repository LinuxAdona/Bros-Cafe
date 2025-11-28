# ✅ Pre-Upload Checklist

## Before Uploading to cPanel

### 1. Configure Email Settings

- [ ] Open `config/email.php`
- [ ] Update `smtp_username` with your email address
- [ ] Update `smtp_password` with your App Password
- [ ] Update `from_email` with your email address
- [ ] Update `to_email` with where you want to receive messages
- [ ] Set `enable_debug` to `false` for production

### 2. Verify Files Exist Locally

- [ ] `lib/PHPMailer/src/PHPMailer.php` exists
- [ ] `lib/PHPMailer/src/SMTP.php` exists
- [ ] `lib/PHPMailer/src/Exception.php` exists
- [ ] `lib.zip` exists (100KB+)
- [ ] `config/email.php` is configured
- [ ] `public/pages/send_contact.php` exists
- [ ] `public/pages/contact.php` exists
- [ ] `test_phpmailer.php` exists

### 3. Test Locally First (Optional)

- [ ] Visit `http://localhost/Bros-Cafe/test_phpmailer.php`
- [ ] All checks should show ✅
- [ ] Try submitting contact form locally

## During cPanel Upload

### 4. Upload Files

- [ ] Upload `lib.zip` to website root
- [ ] Extract `lib.zip` in cPanel File Manager
- [ ] Delete `lib.zip` after extraction
- [ ] Upload `config/email.php` to `/config/`
- [ ] Upload `send_contact.php` to `/public/pages/`
- [ ] Upload `contact.php` to `/public/pages/`
- [ ] Upload `test_phpmailer.php` to website root

### 5. Set Permissions

- [ ] Set `lib/` folder to 755
- [ ] Set `lib/PHPMailer/src/` to 755
- [ ] Set all `.php` files in `lib/PHPMailer/src/` to 644
- [ ] Set `config/email.php` to 600 (important!)
- [ ] Set `public/pages/send_contact.php` to 644
- [ ] Set `public/pages/contact.php` to 644

## After Upload

### 6. Test on Production

- [ ] Visit `https://yourdomain.com/test_phpmailer.php`
- [ ] Verify all tests show ✅
- [ ] Note PHP version shown
- [ ] Check if config is customized (not default values)

### 7. Test Contact Form

- [ ] Visit `https://yourdomain.com/public/pages/contact.php`
- [ ] Fill out all required fields
- [ ] Submit the form
- [ ] Check for success message
- [ ] Check email inbox (and spam!)
- [ ] Verify email received with correct content

### 8. Security Cleanup

- [ ] Delete `test_phpmailer.php` from server
- [ ] Verify `config/email.php` has 600 permissions
- [ ] Ensure `enable_debug` is `false` in production
- [ ] Consider adding `.htaccess` to protect `/config/` folder

### 9. Optional: Protect Config Directory

Create `/config/.htaccess` with:

```apache
Order Deny,Allow
Deny from all
```

## 🎉 If Everything Works

Your contact form is now live and functional!

- Emails will be sent when customers submit the form
- You'll receive emails at the address in `to_email`
- Customers will see a success message
- Reply-to will be set to customer's email

## 🐛 If Something Goes Wrong

1. **Enable Debug Mode:**

   - Set `enable_debug => true` in `config/email.php`
   - Try submitting form again
   - Check browser console (F12) for errors

2. **Check Logs:**

   - Check cPanel error logs
   - Check browser Network tab

3. **Common Fixes:**

   - Verify email credentials are correct
   - For Gmail, ensure using App Password (not regular password)
   - Check SMTP port isn't blocked (try 465 instead of 587)
   - Verify all file paths are correct

4. **Still Not Working?**
   - Re-upload `test_phpmailer.php`
   - Check which test is failing
   - Fix that specific issue

---

**Last Updated:** November 28, 2025
**PHPMailer Version:** 6.9.2
**No Composer Required!** ✅
