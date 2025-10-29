# Bro's Cafe - POS & Inventory Management System

A comprehensive web application for managing a small coffee business with integrated POS system, inventory management, order tracking, and analytics.

## Features

### Customer-Facing

- **Home Page**: Displays featured products, promotions, and announcements
- **Product Showcase**: Browse coffee menu with pricing
- **Promos & Updates**: Latest offers and news
- **User Registration & Login**: Customer account creation

### POS System (Employee/Admin)

- **Real-time Order Processing**: Quick product selection and cart management
- **Multiple Payment Methods**: Cash, Card, GCash support
- **Order Types**: Dine-in, Takeout, Delivery
- **Order Tracking**: View and manage order status
- **Automatic Inventory Updates**: Stock decreases with each sale

### Inventory Management (Admin)

- **Stock Tracking**: Real-time inventory levels
- **Low Stock Alerts**: Automatic notifications when stock is low
- **Restock Management**: Easy restocking with history tracking
- **Inventory Adjustments**: Manual stock adjustments with notes
- **Transaction History**: Complete audit trail of all inventory changes

### Admin Dashboard

- **Sales Analytics**: Daily, weekly, and monthly sales reports
- **Revenue Tracking**: Total sales and average order value
- **Top Products**: Best-selling items analysis
- **Order Management**: View all orders and their status
- **User Management**: Manage employees and customers
- **Performance Metrics**: Employee shifts and performance data

## Technology Stack

- **Frontend**: HTML, TailwindCSS, JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL (via XAMPP)
- **Server**: Apache (via XAMPP)

## Installation & Setup

### Prerequisites

- XAMPP installed on your system
- Web browser (Chrome, Firefox, Edge, etc.)
- Text editor (VS Code recommended)

### Step 1: Setup XAMPP

1. Install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Clone/Download Project

```bash
# Place the Bros-Cafe folder in your XAMPP htdocs directory
# Path should be: C:\xampp\htdocs\Bros-Cafe
```

### Step 3: Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on "Import" tab
3. Choose file: `Bros-Cafe/database/schema.sql`
4. Click "Go" to import the database

**OR manually:**

1. Create a new database named `bros_cafe`
2. Copy the SQL from `database/schema.sql`
3. Run it in the SQL tab

### Step 4: Configure Database Connection

The database configuration is already set in `config/database.php`:

```php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = ''
DB_NAME = 'bros_cafe'
```

Modify if your MySQL has different credentials.

### Step 5: Install Dependencies (TailwindCSS)

```bash
cd C:\xampp\htdocs\Bros-Cafe
npm install
npx tailwindcss -i ./src/input.css -o ./src/output.css --watch
```

### Step 6: Access the Application

Open your browser and navigate to:

- **Home Page**: http://localhost/Bros-Cafe/public/pages/home.php
- **Login**: http://localhost/Bros-Cafe/public/pages/login.php

## Default Login Credentials

### Admin Account

- **Username**: admin
- **Password**: admin123
- **Access**: Full system access (Dashboard, POS, Inventory, Users)

### Employee Account

Create via admin panel or use SQL:

```sql
INSERT INTO users (username, email, password, full_name, role)
VALUES ('employee', 'employee@broscafe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee User', 'employee');
```

- **Username**: employee
- **Password**: employee123
- **Access**: POS and Inventory only

### Customer Account

Register through the signup page or:

- **Username**: (create your own)
- **Password**: (minimum 6 characters)

## Project Structure

```
Bros-Cafe/
├── config/
│   └── database.php          # Database configuration
├── database/
│   └── schema.sql           # Database schema and sample data
├── includes/
│   └── functions.php        # Utility functions and auth
├── public/
│   ├── admin/
│   │   ├── dashboard.php    # Admin dashboard
│   │   ├── inventory.php    # Inventory management
│   │   └── update_inventory.php
│   ├── employee/
│   │   ├── pos.php          # POS system
│   │   └── process_order.php
│   ├── customer/
│   │   └── orders.php       # Customer order history
│   ├── pages/
│   │   ├── home.php         # Landing page
│   │   ├── login.php        # Login page
│   │   ├── signup.php       # Registration page
│   │   └── logout.php       # Logout handler
│   └── assets/
│       └── images/          # Images and logos
├── src/
│   ├── input.css           # TailwindCSS source
│   └── output.css          # Compiled CSS
├── package.json
├── tailwind.config.js
└── README.md
```

## Database Schema

### Main Tables

- **users**: User accounts (admin, employee, customer)
- **products**: Coffee products and menu items
- **categories**: Product categories
- **inventory**: Stock levels and management
- **orders**: Customer orders
- **order_items**: Individual items in orders
- **inventory_transactions**: Audit trail for stock changes
- **sales_summary**: Daily sales aggregation
- **shifts**: Employee shift management

## Features in Detail

### POS System

1. Browse products by category
2. Add items to cart with size selection
3. Adjust quantities
4. Select payment method and order type
5. Process order (creates order, updates inventory)
6. Print receipt (future enhancement)

### Inventory Management

1. View all products with current stock
2. Low stock alerts (highlighted in red)
3. Restock products with quantity and notes
4. Adjust stock levels manually
5. View transaction history
6. Set reorder levels

### Order Tracking

1. Real-time order status
2. Order history for customers
3. Filter orders by date, status
4. Update order status (pending → preparing → ready → completed)

### Analytics

1. Today's sales and orders
2. Revenue trends
3. Top-selling products
4. Employee performance
5. Inventory turnover

## User Roles & Permissions

### Admin

- Full access to all features
- Dashboard and analytics
- User management
- Product management
- Inventory management
- POS access

### Employee

- POS system access
- View inventory
- Restock items
- Process orders

### Customer

- View products
- Place orders (future)
- Order history
- Account management

## API Endpoints (AJAX)

- `POST /public/employee/process_order.php` - Process new order
- `POST /public/admin/update_inventory.php` - Update stock levels

## Troubleshooting

### Database Connection Failed

- Ensure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database `bros_cafe` exists

### CSS Not Loading

- Run TailwindCSS build: `npx tailwindcss -i ./src/input.css -o ./src/output.css`
- Check file path in HTML files

### Session Issues

- Ensure PHP session is enabled
- Clear browser cookies
- Check PHP session configuration

### Images Not Loading

- Verify images exist in `public/assets/images/`
- Check file paths (relative vs absolute)

## Future Enhancements

- [ ] Mobile app integration
- [ ] Online ordering for customers
- [ ] QR code menu
- [ ] SMS notifications
- [ ] Receipt printing
- [ ] Multi-branch support
- [ ] Advanced reporting (PDF/Excel export)
- [ ] Employee scheduling
- [ ] Customer loyalty program management
- [ ] Email notifications
- [ ] API for third-party integrations

## Contributing

This is a project for Bro's Cafe. For suggestions or issues, contact the development team.

## License

Proprietary - All rights reserved to Bro's Cafe

## Support

For technical support or questions:

- Email: support@broscafe.com
- Phone: +63 123 456 7890

---

**Version**: 1.0.0  
**Last Updated**: October 29, 2025  
**Developed for**: Bro's Cafe
