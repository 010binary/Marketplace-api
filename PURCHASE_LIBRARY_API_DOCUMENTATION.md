# Purchase & Library API Documentation

## Table of Contents

- [Overview](#overview)
- [Security Features](#security-features)
- [Authentication](#authentication)
- [Endpoints](#endpoints)
  - [Checkout Product](#checkout-product)
  - [View Library](#view-library)
  - [View Purchase Details](#view-purchase-details)
  - [Generate Download URL](#generate-download-url)
  - [Download Product File](#download-product-file)
  - [Get Download Info](#get-download-info)
  - [Creator Sales](#creator-sales)
  - [Creator Revenue](#creator-revenue)
- [Workflows](#workflows)
- [Security Architecture](#security-architecture)
- [Download Tracking](#download-tracking)

---

## Overview

The Purchase & Library system allows customers to:
- Purchase products (simulated payment)
- View their library of purchased products
- Download purchased product files securely using temporary signed URLs
- Track download history

Creators can:
- View sales of their products
- Track revenue statistics

### Key Features

✅ **Secure Downloads** - Temporary signed URLs that expire  
✅ **Access Control** - Only buyers can download  
✅ **Download Tracking** - IP address and timestamp logging  
✅ **Purchase History** - Complete library management  
✅ **Payment Simulation** - Instant checkout (payment gateway integration ready)  
✅ **Revenue Tracking** - Creator statistics and analytics  

---

## Security Features

### 1. Temporary Signed URLs
- URLs are cryptographically signed
- Default expiration: 60 minutes (configurable up to 24 hours)
- Cannot be forged or tampered with
- Invalid after expiration time

### 2. Access Verification
- User must have completed purchase
- User ID embedded in signed URL
- Purchase status validated before download
- Product file existence verified

### 3. Download Logging
- Every download is logged with:
  - Timestamp
  - IP address
  - Purchase reference
- Prevents abuse and enables auditing

### 4. Role-Based Access
- Customers can purchase and download
- Creators can view sales and revenue
- Proper middleware enforcement

---

## Authentication

All endpoints require authentication using Laravel Sanctum.

```
Authorization: Bearer {your-token}
```

### Role Requirements

| Feature | Role Required |
|---------|---------------|
| Checkout | Customer |
| View Library | Customer |
| Download Files | Customer (must own) |
| View Sales | Creator |
| View Revenue | Creator |

---

## Endpoints

### Checkout Product

Purchase a product with simulated payment processing.

**Endpoint:** `POST /api/v1/checkout/{product}`

**Authentication:** Required (Customer role)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `product` | integer | Yes | Product ID to purchase |

**Business Rules:**
- Product must be active (`is_active: true`)
- Customer cannot purchase same product twice
- Purchase status set to `completed` (payment simulated)
- Unique reference generated for each purchase

**Example Request:**
```bash
POST /api/v1/checkout/5
Authorization: Bearer YOUR_TOKEN
```

**Example Response:**
```json
{
  "message": "Product purchased successfully",
  "purchase": {
    "id": 42,
    "user_id": 10,
    "product_id": 5,
    "reference": "PUR-AB12CD34EF56",
    "status": "completed",
    "created_at": "2026-01-27T10:30:00.000000Z",
    "updated_at": "2026-01-27T10:30:00.000000Z",
    "product": {
      "id": 5,
      "title": "Advanced Laravel Course",
      "description": "Complete Laravel mastery",
      "price": "49.99",
      "is_active": true,
      "creator": { ... },
      "category": { ... },
      "image": { ... },
      "file": { ... }
    }
  }
}
```

**Status Code:** `201 Created`

**Error Responses:**

```json
// Already purchased
{
  "message": "You have already purchased this product."
}
```

```json
// Product not active
{
  "message": "This product is not available for purchase."
}
```

---

### View Library

Get paginated list of purchased products.

**Endpoint:** `GET /api/v1/library`

**Authentication:** Required (Customer role)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 15) |
| `page` | integer | No | Page number (default: 1) |

**Example Request:**
```bash
GET /api/v1/library?per_page=10&page=1
Authorization: Bearer YOUR_TOKEN
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 42,
      "user_id": 10,
      "product_id": 5,
      "reference": "PUR-AB12CD34EF56",
      "status": "completed",
      "created_at": "2026-01-27T10:30:00.000000Z",
      "updated_at": "2026-01-27T10:30:00.000000Z",
      "product": {
        "id": 5,
        "title": "Advanced Laravel Course",
        "price": "49.99",
        "creator": {
          "id": 3,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "category": { ... },
        "image": { ... },
        "file": {
          "id": 8,
          "original_filename": "course-materials.zip",
          "size": 1048576
        }
      }
    }
  ],
  "current_page": 1,
  "per_page": 10,
  "total": 25,
  "last_page": 3
}
```

**Status Code:** `200 OK`

---

### View Purchase Details

Get detailed information about a specific purchase.

**Endpoint:** `GET /api/v1/library/{purchase}`

**Authentication:** Required (Customer role + ownership)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `purchase` | integer | Yes | Purchase ID |

**Example Request:**
```bash
GET /api/v1/library/42
Authorization: Bearer YOUR_TOKEN
```

**Example Response:**
```json
{
  "id": 42,
  "user_id": 10,
  "product_id": 5,
  "reference": "PUR-AB12CD34EF56",
  "status": "completed",
  "created_at": "2026-01-27T10:30:00.000000Z",
  "updated_at": "2026-01-27T10:30:00.000000Z",
  "product": { ... },
  "download_logs": [
    {
      "id": 15,
      "purchase_id": 42,
      "ip_address": "192.168.1.100",
      "downloaded_at": "2026-01-27T11:00:00.000000Z"
    },
    {
      "id": 16,
      "purchase_id": 42,
      "ip_address": "192.168.1.100",
      "downloaded_at": "2026-01-28T09:30:00.000000Z"
    }
  ]
}
```

**Status Code:** `200 OK`

**Error Response:**
```json
// Not your purchase
{
  "message": "You do not have access to this purchase."
}
```

---

### Generate Download URL

Generate a temporary signed URL for downloading a purchased product.

**Endpoint:** `POST /api/v1/products/{productId}/generate-download-url`

**Authentication:** Required (Customer role + must have purchased)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `productId` | integer | Yes | Product ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `expiration_minutes` | integer | No | URL expiration (default: 60, max: 1440) |

**Example Request:**
```bash
POST /api/v1/products/5/generate-download-url
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "expiration_minutes": 120
}
```

**Example Response:**
```json
{
  "download_url": "http://localhost:8000/api/v1/download/5?userId=10&expires=1706335800&signature=abc123def456...",
  "expires_at": "2026-01-27T12:30:00+00:00",
  "expires_in_minutes": 120
}
```

**Status Code:** `200 OK`

**Security Features:**
- ✅ URL is cryptographically signed
- ✅ User ID embedded in URL
- ✅ Expiration timestamp included
- ✅ Cannot be modified or forged
- ✅ Automatically expires after time limit

**Error Responses:**

```json
// Not purchased
{
  "message": "You have not purchased this product."
}
```

```json
// File not available
{
  "message": "Product file is not available."
}
```

---

### Download Product File

Download a purchased product file using a signed URL.

**Endpoint:** `GET /api/v1/download/{productId}`

**Authentication:** Required + Valid Signed URL

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `productId` | integer | Yes | Product ID |

**Query Parameters (Auto-generated by signed URL):**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `userId` | integer | Yes | User ID (embedded in signed URL) |
| `expires` | integer | Yes | Expiration timestamp |
| `signature` | string | Yes | Cryptographic signature |

**Example Request:**
```bash
GET /api/v1/download/5?userId=10&expires=1706335800&signature=abc123...
Authorization: Bearer YOUR_TOKEN
```

**Response:**
- Binary file stream
- Content-Type: Original file MIME type
- Content-Disposition: attachment with original filename

**Status Code:** `200 OK`

**Download Process:**
1. Validates signature and expiration
2. Verifies user ID matches authenticated user
3. Confirms purchase status
4. Logs download (IP + timestamp)
5. Streams file to client

**Error Responses:**

```json
// Invalid or expired URL
{
  "message": "Invalid or expired download URL."
}
```

```json
// Wrong user
{
  "message": "Invalid download URL for this user."
}
```

```json
// Not purchased
{
  "message": "You have not purchased this product."
}
```

---

### Get Download Info

Get information about a downloadable product without downloading.

**Endpoint:** `GET /api/v1/products/{productId}/download-info`

**Authentication:** Required (Customer role + must have purchased)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `productId` | integer | Yes | Product ID |

**Example Request:**
```bash
GET /api/v1/products/5/download-info
Authorization: Bearer YOUR_TOKEN
```

**Example Response:**
```json
{
  "product_id": 5,
  "product_title": "Advanced Laravel Course",
  "file_name": "course-materials.zip",
  "file_size": 1048576,
  "file_size_formatted": "1.0 MB",
  "mime_type": "application/zip",
  "purchased_at": "2026-01-27T10:30:00.000000Z",
  "download_stats": {
    "total_downloads": 3,
    "first_download": "2026-01-27T11:00:00.000000Z",
    "last_download": "2026-01-28T09:30:00.000000Z",
    "recent_downloads": [
      {
        "downloaded_at": "2026-01-28T09:30:00.000000Z",
        "ip_address": "192.168.1.100"
      },
      {
        "downloaded_at": "2026-01-27T15:45:00.000000Z",
        "ip_address": "192.168.1.100"
      },
      {
        "downloaded_at": "2026-01-27T11:00:00.000000Z",
        "ip_address": "192.168.1.100"
      }
    ]
  }
}
```

**Status Code:** `200 OK`

---

### Creator Sales

View sales of products created by the authenticated creator.

**Endpoint:** `GET /api/v1/sales`

**Authentication:** Required (Creator role)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 15) |
| `page` | integer | No | Page number (default: 1) |

**Example Request:**
```bash
GET /api/v1/sales?per_page=20
Authorization: Bearer YOUR_CREATOR_TOKEN
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 42,
      "user_id": 10,
      "product_id": 5,
      "reference": "PUR-AB12CD34EF56",
      "status": "completed",
      "created_at": "2026-01-27T10:30:00.000000Z",
      "product": {
        "id": 5,
        "title": "Advanced Laravel Course",
        "price": "49.99"
      },
      "user": {
        "id": 10,
        "name": "Jane Smith",
        "email": "jane@example.com"
      }
    }
  ],
  "current_page": 1,
  "per_page": 20,
  "total": 150,
  "last_page": 8
}
```

**Status Code:** `200 OK`

---

### Creator Revenue

Get revenue statistics for the authenticated creator.

**Endpoint:** `GET /api/v1/revenue`

**Authentication:** Required (Creator role)

**Example Request:**
```bash
GET /api/v1/revenue
Authorization: Bearer YOUR_CREATOR_TOKEN
```

**Example Response:**
```json
{
  "total_sales": 150,
  "total_revenue": "7499.50",
  "average_order_value": "49.99"
}
```

**Status Code:** `200 OK`

**Calculations:**
- `total_sales`: Count of completed purchases
- `total_revenue`: Sum of all sale prices
- `average_order_value`: Total revenue / Total sales

---

## Workflows

### Customer Purchase & Download Flow

```
1. Browse Products
   GET /api/v1/products

2. View Product Details
   GET /api/v1/products/5

3. Checkout Product
   POST /api/v1/checkout/5
   → Receive purchase confirmation with reference

4. View Library
   GET /api/v1/library
   → See purchased product in library

5. Get Download Info (optional)
   GET /api/v1/products/5/download-info
   → Check file size, format, download history

6. Generate Download URL
   POST /api/v1/products/5/generate-download-url
   → Receive temporary signed URL (expires in 60 min)

7. Download File
   GET /api/v1/download/5?userId=10&expires=...&signature=...
   → File download starts
   → Download logged automatically

8. Re-download (if needed)
   Repeat steps 6-7 to generate new URL
   → Previous downloads tracked in history
```

### Creator Revenue Tracking Flow

```
1. View Sales
   GET /api/v1/sales
   → See all purchases of your products

2. View Revenue Statistics
   GET /api/v1/revenue
   → See total sales, revenue, average order value

3. Monitor Product Performance
   Compare sales across different products
```

---

## Security Architecture

### Signed URL Protection

**How it works:**

1. **URL Generation**
   ```
   POST /products/5/generate-download-url
   ↓
   System creates URL with:
   - Product ID
   - User ID
   - Expiration timestamp
   - Cryptographic signature
   ```

2. **Signature Creation**
   ```
   signature = HMAC-SHA256(
     key: APP_KEY,
     message: "productId=5&userId=10&expires=1706335800"
   )
   ```

3. **URL Validation**
   ```
   GET /download/5?userId=10&expires=1706335800&signature=abc123
   ↓
   System verifies:
   ✓ Signature is valid
   ✓ URL hasn't expired
   ✓ User ID matches authenticated user
   ✓ User has purchased product
   ```

**Security Benefits:**
- ✅ Cannot be forged without APP_KEY
- ✅ Tampering invalidates signature
- ✅ Time-limited access
- ✅ User-specific (can't share)
- ✅ Product-specific

### Access Control Layers

```
Layer 1: Authentication
  ↓ Laravel Sanctum token validation

Layer 2: Role Check
  ↓ Customer/Creator role verification

Layer 3: Purchase Verification
  ↓ Confirm completed purchase exists

Layer 4: Signed URL Validation
  ↓ Verify signature and expiration

Layer 5: File Access
  ↓ Stream file from private storage

Layer 6: Download Logging
  ↓ Record download event
```

---

## Download Tracking

Every download is automatically logged with:

```json
{
  "id": 15,
  "purchase_id": 42,
  "ip_address": "192.168.1.100",
  "downloaded_at": "2026-01-27T11:00:00.000000Z"
}
```

### Use Cases

**For Customers:**
- Track when they last downloaded a file
- Verify download history
- Monitor access to purchased products

**For Platform:**
- Audit trail for security
- Detect suspicious activity
- Usage analytics
- Support customer inquiries

**For Creators:**
- (Future feature) Track download counts per product
- (Future feature) Engagement analytics

---

## Error Responses

### 400 Bad Request

```json
{
  "message": "You have already purchased this product."
}
```

```json
{
  "message": "This product is not available for purchase."
}
```

```json
{
  "message": "Product file is not available."
}
```

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
  "message": "Forbidden"
}
```

```json
{
  "message": "You have not purchased this product."
}
```

```json
{
  "message": "You do not have access to this purchase."
}
```

```json
{
  "message": "Invalid or expired download URL."
}
```

```json
{
  "message": "Invalid download URL for this user."
}
```

### 404 Not Found

```json
{
  "message": "No query results for model [App\\Models\\Product] 5"
}
```

```json
{
  "message": "No query results for model [App\\Models\\Purchase] 42"
}
```

---

## Testing with cURL

### Purchase Product
```bash
curl -X POST http://localhost:8000/api/v1/checkout/5 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### View Library
```bash
curl -X GET http://localhost:8000/api/v1/library \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Generate Download URL
```bash
curl -X POST http://localhost:8000/api/v1/products/5/generate-download-url \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"expiration_minutes": 120}'
```

### Download File
```bash
# Use the URL from the previous response
curl -X GET "http://localhost:8000/api/v1/download/5?userId=10&expires=1706335800&signature=abc123..." \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output downloaded-file.zip
```

### View Sales (Creator)
```bash
curl -X GET http://localhost:8000/api/v1/sales \
  -H "Authorization: Bearer YOUR_CREATOR_TOKEN"
```

### View Revenue (Creator)
```bash
curl -X GET http://localhost:8000/api/v1/revenue \
  -H "Authorization: Bearer YOUR_CREATOR_TOKEN"
```

---

## Business Rules

### Purchase Rules
- ✅ Only active products can be purchased
- ✅ Cannot purchase same product twice
- ✅ Payment is simulated (status: completed)
- ✅ Unique reference generated for each purchase

### Download Rules
- ✅ Must have completed purchase
- ✅ Product must have attached file
- ✅ File must exist in storage
- ✅ Download URL expires after specified time
- ✅ URL is user-specific (cannot be shared)
- ✅ Every download is logged

### Access Rules
- ✅ Customers can checkout and download
- ✅ Creators can view sales and revenue
- ✅ Cannot access other users' purchases
- ✅ Cannot download without valid signed URL

---

## Integration with Payment Gateway

The current implementation simulates payment processing. To integrate with a real payment gateway (Stripe, PayPal, etc.):

1. **Update Checkout Process**
   ```php
   // In PurchaseService::checkout()
   
   // Create pending purchase
   $purchase = Purchase::create([
       'status' => Purchase::STATUS_PENDING,
       // ...
   ]);
   
   // Integrate with payment gateway
   $payment = PaymentGateway::charge([
       'amount' => $product->price,
       'customer' => $user,
       'reference' => $purchase->reference,
   ]);
   
   // Update status based on payment result
   if ($payment->successful()) {
       $purchase->update(['status' => Purchase::STATUS_COMPLETED]);
   } else {
       $purchase->update(['status' => Purchase::STATUS_FAILED]);
   }
   ```

2. **Add Webhook Handler**
   - Handle payment confirmation webhooks
   - Update purchase status asynchronously
   - Send confirmation emails

3. **Add Refund Support**
   - Revoke download access
   - Update purchase status
   - Handle partial refunds

---

## Notes

- All timestamps are in UTC (ISO 8601 format)
- File downloads use streaming for memory efficiency
- Private disk prevents direct file access
- Download logs have no timestamps field (uses `downloaded_at` only)
- Purchase references are unique and human-readable
- Signed URLs use Laravel's built-in URL signing
- IP addresses logged for security audit trail

---

## Future Enhancements

Potential features for expansion:

1. **Download Limits**
   - Limit number of downloads per purchase
   - Time-based download windows

2. **Advanced Analytics**
   - Creator dashboard with charts
   - Download trends and patterns
   - Revenue forecasting

3. **Subscription Model**
   - Recurring payments
   - Access period management

4. **Bulk Downloads**
   - Download multiple products as ZIP
   - Bundle purchases

5. **Download Resume**
   - Support for interrupted downloads
   - Range requests for large files

6. **Notifications**
   - Purchase confirmations via email
   - New sales alerts for creators
   - Download reminders