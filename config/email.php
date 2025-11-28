<?php

/**
 * Email Configuration
 * 
 * Configure SMTP settings for sending emails from the contact form.
 * 
 * IMPORTANT: For Gmail, you need to:
 * 1. Enable 2-Factor Authentication
 * 2. Generate an App Password: https://myaccount.google.com/apppasswords
 * 3. Use the App Password (not your regular Gmail password)
 * 
 * For other email providers, check their SMTP settings documentation.
 */

return [
    // SMTP Server Settings
    'smtp_host' => 'smtp.gmail.com',  // For Gmail. Change for other providers
    'smtp_port' => 587,                // 587 for TLS, 465 for SSL
    'smtp_secure' => 'tls',            // 'tls' or 'ssl'

    // SMTP Authentication
    'smtp_username' => 'linuxadona17@gmail.com',  // Your email address
    'smtp_password' => 'rlia yprf shbc aplo',     // Your App Password (for Gmail) or regular password

    // Email Addresses
    'from_email' => 'linuxadona17@gmail.com',     // Email to send FROM
    'from_name' => 'Bro\'s Cafe Contact Form',  // Display name
    'to_email' => '23-74349@g.batstate-u.edu.ph',          // Where to receive contact form messages
    'to_name' => 'Bro\'s Cafe',                 // Recipient name

    // Email Settings
    'enable_debug' => false,  // Set to true to see detailed SMTP debug info (for troubleshooting)
];
