# Admin Verification System for Inventory Operations

## Overview

All inventory operations (restock, adjust stock, bulk restock) now require admin verification before execution. This adds an extra security layer to prevent unauthorized inventory modifications.

## Verification Methods

### 1. Password Verification

- Admin enters their account password
- System verifies against the database
- Only active admin accounts can verify

### 2. QR Code Verification

- Admin generates a time-limited QR code (valid for 5 minutes)
- QR code can be scanned or manually entered
- Format: `ADMIN_<user_id>_<timestamp>_<hash>`
- Hash validation ensures authenticity

## How It Works

### For Regular Stock Operations (Restock/Adjust)

1. User fills out the restock or adjust form
2. User clicks "Submit" or "Update Stock"
3. **Admin Verification Modal appears**
4. Admin verifies using password or QR code
5. Upon successful verification, operation executes
6. Success/Error modal displays result

### For Bulk Restock Operations

1. User selects category and quantity
2. User clicks "Execute Bulk Restock"
3. **Confirmation Modal appears** (shows items to restock)
4. User clicks "Confirm"
5. **Admin Verification Modal appears**
6. Admin verifies using password or QR code
7. Upon successful verification, bulk restock executes
8. Success/Error modal displays result

## Generating QR Codes

### For Admins Only

1. Navigate to **Inventory** page
2. Click **QR Code** button (visible only to admins)
3. QR code is generated with current timestamp
4. Options:
   - **Scan**: Use mobile device camera
   - **Copy Code**: Copy text to clipboard
   - **Download**: Save QR code as PNG image
5. QR code expires in **5 minutes**
6. Generate new code when expired

## Security Features

### Password Method

- Uses existing admin password
- Validates against `users` table
- Checks for:
  - Admin role (`role = 'admin'`)
  - Active status (`status = 'active'`)
  - Password match (MD5 hash)

### QR Code Method

- Time-limited (5 minutes validity)
- Cryptographic hash verification
- Unique per user and timestamp
- Cannot be reused after expiry
- Hash formula: `md5(userId_timestamp_email)`

## User Experience Flow

```
┌─────────────────────────────────┐
│ User Action: Restock Item       │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│ Fill Restock Form               │
│ - Ingredient: Coffee Beans      │
│ - Quantity: 5 kg                │
│ - Notes: Weekly restock         │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│ Click "Update Stock"            │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│ 🛡️ Admin Verification Modal     │
│                                 │
│ Choose Method:                  │
│ ┌──────────┬──────────┐        │
│ │ Password │ QR Code  │        │
│ └──────────┴──────────┘        │
│                                 │
│ [Enter admin password]          │
│                                 │
│ [Cancel] [Verify & Continue]    │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│ Verification with Server        │
│ verify_admin.php                │
└────────────┬────────────────────┘
             │
      ┌──────┴──────┐
      │             │
   Success        Failure
      │             │
      ▼             ▼
┌──────────┐  ┌──────────────┐
│ Execute  │  │ Show Error   │
│ Restock  │  │ Message      │
└─────┬────┘  └──────────────┘
      │
      ▼
┌──────────────────┐
│ ✅ Success Modal │
│ Stock Updated!   │
└──────────────────┘
```

## Files Modified

### Backend

- **`/public/pages/usr/verify_admin.php`** - Admin verification endpoint
  - Handles password and QR verification
  - Returns JSON response with success/failure

### Frontend

- **`/public/pages/usr/inventory.php`** - Main inventory page

  - Added verification modal HTML
  - Added verification JavaScript functions
  - Updated UI to show QR Code button for admins

- **`/public/assets/js/inventory.js`** - Inventory operations
  - Modified `submitRestock()` to require verification
  - Modified `submitAdjust()` to require verification
  - Added `executeRestock()` - actual restock execution
  - Added `executeAdjustment()` - actual adjustment execution

### New Features

- **`/public/pages/usr/generate_qr.php`** - QR Code Generator
  - Admin-only access
  - Generates time-limited QR codes
  - Download/Copy functionality
  - Auto-refresh warning

## API Endpoints

### POST `/public/pages/usr/verify_admin.php`

**Request (Password Method):**

```json
{
  "method": "password",
  "password": "admin_password"
}
```

**Request (QR Method):**

```json
{
  "method": "qr",
  "qr_code": "ADMIN_1_1732567890_abc123def456..."
}
```

**Response (Success):**

```json
{
  "success": true,
  "message": "Admin verified successfully",
  "admin": "john_admin"
}
```

**Response (Failure):**

```json
{
  "success": false,
  "message": "Invalid admin password"
}
```

## Testing the System

### Test Password Verification

1. Navigate to Inventory page
2. Click "Restock" on any item
3. Fill in quantity
4. Click "Update Stock"
5. Verification modal appears
6. Enter admin password
7. Click "Verify & Continue"
8. Should execute or show error

### Test QR Verification

1. As admin, click "QR Code" button
2. Copy the generated QR code text
3. Go back to inventory
4. Try to restock an item
5. In verification modal, switch to "QR Code" tab
6. Paste the code
7. Click "Verify & Continue"
8. Should execute if within 5 minutes

### Test QR Expiry

1. Generate QR code
2. Wait 5+ minutes
3. Try to use the QR code
4. Should show "QR code has expired" error

## Error Messages

| Error                          | Cause                            | Solution             |
| ------------------------------ | -------------------------------- | -------------------- |
| "Please enter admin password"  | Empty password field             | Enter password       |
| "Invalid admin password"       | Wrong password or non-admin user | Check credentials    |
| "Please enter or scan QR code" | Empty QR field                   | Enter/scan QR code   |
| "Invalid QR code format"       | Malformed QR string              | Regenerate QR code   |
| "QR code has expired"          | QR older than 5 minutes          | Generate new QR code |
| "Invalid QR code"              | Hash mismatch                    | Generate new QR code |
| "Admin not found or inactive"  | Account disabled                 | Contact system admin |

## Benefits

✅ **Enhanced Security**: Prevents unauthorized inventory changes
✅ **Audit Trail**: Verifies admin identity for each operation
✅ **Flexible Authentication**: Password or QR code options
✅ **Time-Limited QR**: Prevents QR code reuse
✅ **User-Friendly**: Seamless integration into existing workflow
✅ **Admin Convenience**: Generate QR codes for quick verification

## Future Enhancements

- [ ] Add biometric verification
- [ ] Support for multiple admin approvals
- [ ] Verification history log
- [ ] SMS/Email OTP verification
- [ ] Role-based verification levels
- [ ] Batch operation approvals

---

**Note**: QR codes are for convenience but must be kept secure. Do not share QR codes publicly or save them in insecure locations.
