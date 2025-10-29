# QUICK SETUP GUIDE - Bro's Cafe POS System

## 1. Install XAMPP

- Download from: https://www.apachefriends.org/
- Install and start Apache & MySQL services

## 2. Setup Database

1. Open browser: http://localhost/phpmyadmin
2. Click "Import" tab
3. Select file: `Bros-Cafe/database/schema.sql`
4. Click "Go"

## 3. Install Node Modules & Build CSS

```bash
cd C:\xampp\htdocs\Bros-Cafe
npm install
npx tailwindcss -i ./src/input.css -o ./src/output.css
```

## 4. Access Application

- Home: http://localhost/Bros-Cafe/public/pages/home.php
- Login: http://localhost/Bros-Cafe/public/pages/login.php

## 5. Login Credentials

### Admin Access

Username: admin
Password: admin123

### Employee Access

Username: employee
Password: employee123

### Customer Access

Username: customer
Password: customer123

## 6. File Structure Overview

```
Bros-Cafe/
├── config/          - Database configuration
├── database/        - SQL schema
├── includes/        - Helper functions
├── public/
│   ├── admin/      - Admin dashboard & inventory
│   ├── employee/   - POS system
│   ├── customer/   - Customer orders
│   ├── pages/      - Login, signup, home
│   └── assets/     - Images
└── src/            - CSS files
```

## 7. Main Features

### POS System (Employee/Admin)

- Access: http://localhost/Bros-Cafe/public/employee/pos.php
- Features: Product selection, cart, payment processing

### Inventory Management (Admin)

- Access: http://localhost/Bros-Cafe/public/admin/inventory.php
- Features: Stock tracking, restocking, low stock alerts

### Admin Dashboard

- Access: http://localhost/Bros-Cafe/public/admin/dashboard.php
- Features: Sales analytics, reports, top products

## 8. Troubleshooting

**Database Connection Error?**

- Check MySQL is running in XAMPP
- Verify database `bros_cafe` exists

**CSS Not Loading?**

- Run: `npx tailwindcss -i ./src/input.css -o ./src/output.css`

**Can't Login?**

- Clear browser cookies
- Verify users table has data

## 9. Development Notes

- PHP Sessions used for authentication
- Role-based access control (admin/employee/customer)
- Inventory auto-updates on sales
- Real-time stock tracking

## Need Help?

Check README.md for detailed documentation
