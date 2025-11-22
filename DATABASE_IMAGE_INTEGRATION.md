# Database Image Integration - Bros Cafe

## Overview
This update converts the menu.php and pos.php pages from static content to dynamic database-driven content with image support.

## Changes Made

### 1. New Files Created

#### `/public/pages/get_image.php`
- Helper script to serve images from the database
- Retrieves LONGBLOB image data from the products table
- Returns images with proper MIME type headers
- Usage: `get_image.php?id=<product_id>`

#### `/public/pages/usr/upload_product_image.php`
- Admin interface for uploading product images
- Allows employees to upload images for products
- Supports JPEG, PNG, and GIF formats (max 5MB)
- Includes image preview functionality
- Images are stored as LONGBLOB in the database

### 2. Updated Files

#### `/public/pages/menu.php`
**Before:** Static HTML menu items with hardcoded data
**After:** Dynamic menu that:
- Fetches all products and categories from the database
- Groups products by category
- Displays product images from LONGBLOB field (or default icon if no image)
- Shows prices for both Dodici and Sedici sizes (if applicable)
- Dynamic category filtering based on database categories
- Uses `formatCurrency()` function for consistent price display

**Key Features:**
```php
// Fetches products with categories
$stmt = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.status = 'available'
    ORDER BY c.name, p.name
");

// Image display
<?php if ($product['image']): ?>
    <img src="get_image.php?id=<?php echo $product['id']; ?>" 
         alt="<?php echo htmlspecialchars($product['name']); ?>"
         class="object-cover w-full h-full">
<?php else: ?>
    <span class="text-7xl">☕</span>
<?php endif; ?>
```

#### `/public/pages/usr/pos.php`
**Before:** Product cards with generic coffee icon
**After:** Product cards that:
- Display actual product images from database
- Fall back to default icon if no image exists
- Maintain all existing POS functionality
- Image display code:

```php
<div class="flex items-center justify-center w-full h-32 mb-3 overflow-hidden bg-gray-100 rounded-lg">
    <?php if ($product['image']): ?>
        <img src="../get_image.php?id=<?php echo $product['id']; ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>"
             class="object-cover w-full h-full">
    <?php else: ?>
        <span class="text-4xl">☕</span>
    <?php endif; ?>
</div>
```

## Database Schema

The system uses the existing `products` table with the `image` column:

```sql
CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_dodici` decimal(10,2) DEFAULT NULL,
  `price_sedici` decimal(10,2) DEFAULT NULL,
  `image` longblob DEFAULT NULL,  -- Stores binary image data
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## How to Upload Product Images

### Method 1: Using the Upload Interface
1. Navigate to `/public/pages/usr/upload_product_image.php`
2. Select a product from the dropdown
3. Choose an image file (JPEG, PNG, or GIF, max 5MB)
4. Preview the image before uploading
5. Click "Upload Image"

### Method 2: Using SQL/PHPMyAdmin
You can also directly insert images using SQL:

```php
// Example PHP code to insert image
$imageData = file_get_contents('path/to/image.jpg');
$stmt = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
$stmt->execute([$imageData, $product_id]);
```

## Features

### Menu Page (`menu.php`)
- ✅ Dynamic category filtering
- ✅ Products grouped by category
- ✅ Database-driven product display
- ✅ Image support with fallback icons
- ✅ Responsive grid layout
- ✅ Price display for both sizes

### POS Page (`pos.php`)
- ✅ Product images in grid view
- ✅ Maintains existing cart functionality
- ✅ Stock level display
- ✅ Category filtering
- ✅ All existing POS features preserved

## Benefits

1. **Easy Content Management**: Update products in database instead of editing HTML
2. **Visual Appeal**: Real product images enhance user experience
3. **Consistency**: Same data source for both menu and POS
4. **Scalability**: Easy to add/remove/update products
5. **Flexibility**: Supports products with different pricing models

## Image Best Practices

1. **Recommended Dimensions**: 800x800 pixels (square format works best)
2. **File Format**: JPEG for photos, PNG for images with transparency
3. **File Size**: Keep under 500KB for optimal loading speed
4. **Image Quality**: Medium to high quality (80-90% JPEG quality)
5. **Aspect Ratio**: 1:1 (square) or 4:3 for consistent display

## Notes

- Images are stored as LONGBLOB in MySQL (max ~16MB per image)
- `get_image.php` serves images with proper caching headers
- Default fallback icon (☕) displays when no image is available
- All products must exist in the database to appear on the menu
- Products with `status = 'unavailable'` are hidden from public view

## Future Enhancements

- [ ] Image compression on upload
- [ ] Multiple image support per product
- [ ] Image cropping/resizing tool
- [ ] Bulk image upload feature
- [ ] CDN integration for better performance
- [ ] Image optimization and caching
