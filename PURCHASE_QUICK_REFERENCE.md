# Purchase & Library Quick Reference

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All endpoints require Bearer token:
```
Authorization: Bearer {your-token}
```

---

## Customer Endpoints

### Purchase & Library

| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| POST | `/checkout/{product}` | Purchase a product | Customer |
| GET | `/library` | View purchased products | Customer |
| GET | `/library/{purchase}` | View purchase details | Customer |

### Secure Downloads

| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| POST | `/products/{productId}/generate-download-url` | Generate signed URL | Customer |
| GET | `/download/{productId}` | Download file (signed URL) | Customer |
| GET | `/products/{productId}/download-info` | Get download info | Customer |

---

## Creator Endpoints

| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| GET | `/sales` | View sales | Creator |
| GET | `/revenue` | View revenue stats | Creator |

---

## Quick Examples

### 1. Purchase Product
```bash
POST /api/v1/checkout/5
Authorization: Bearer YOUR_TOKEN
```

**Response:**
```json
{
  "message": "Product purchased successfully",
  "purchase": {
    "id": 42,
    "reference": "PUR-AB12CD34EF56",
    "status": "completed",
    "product": { ... }
  }
}
```

---

### 2. View Library
```bash
GET /api/v1/library?per_page=15&page=1
Authorization: Bearer YOUR_TOKEN
```

**Response:** Paginated list of purchases with product details

---

### 3. Generate Download URL
```bash
POST /api/v1/products/5/generate-download-url
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "expiration_minutes": 60
}
```

**Response:**
```json
{
  "download_url": "http://localhost:8000/api/v1/download/5?userId=10&expires=1706335800&signature=abc123...",
  "expires_at": "2026-01-27T12:30:00+00:00",
  "expires_in_minutes": 60
}
```

---

### 4. Download File
```bash
GET /api/v1/download/5?userId=10&expires=1706335800&signature=abc123...
Authorization: Bearer YOUR_TOKEN
```

**Response:** Binary file stream

**Process:**
- ✅ Validates signature
- ✅ Checks expiration
- ✅ Verifies purchase
- ✅ Logs download
- ✅ Streams file

---

### 5. Get Download Info
```bash
GET /api/v1/products/5/download-info
Authorization: Bearer YOUR_TOKEN
```

**Response:**
```json
{
  "product_id": 5,
  "product_title": "Advanced Laravel Course",
  "file_name": "course-materials.zip",
  "file_size_formatted": "1.0 MB",
  "purchased_at": "2026-01-27T10:30:00Z",
  "download_stats": {
    "total_downloads": 3,
    "last_download": "2026-01-28T09:30:00Z",
    "recent_downloads": [ ... ]
  }
}
```

---

### 6. View Sales (Creator)
```bash
GET /api/v1/sales?per_page=20
Authorization: Bearer YOUR_CREATOR_TOKEN
```

**Response:** Paginated list of sales with customer info

---

### 7. View Revenue (Creator)
```bash
GET /api/v1/revenue
Authorization: Bearer YOUR_CREATOR_TOKEN
```

**Response:**
```json
{
  "total_sales": 150,
  "total_revenue": "7499.50",
  "average_order_value": "49.99"
}
```

---

## Query Parameters

### Library & Sales
- `per_page` - Items per page (default: 15)
- `page` - Page number (default: 1)

### Generate Download URL
- `expiration_minutes` - URL expiration (default: 60, max: 1440)

---

## Complete Workflow

### Customer Purchase & Download
```
1. POST /checkout/5
   → Purchase product

2. GET /library
   → See purchase in library

3. POST /products/5/generate-download-url
   → Get signed URL (expires in 60 min)

4. GET /download/5?userId=10&expires=...&signature=...
   → Download file
   → Download logged automatically

5. Repeat step 3-4 to re-download
   → Generate new URL each time
```

---

## Security Features

### Signed URLs
- ✅ Cryptographically signed (HMAC-SHA256)
- ✅ Time-limited expiration
- ✅ User-specific (cannot share)
- ✅ Tamper-proof
- ✅ Automatically expires

### Access Control
- ✅ Authentication required
- ✅ Role-based access
- ✅ Purchase verification
- ✅ Owner validation
- ✅ Signature validation

### Download Tracking
- ✅ IP address logged
- ✅ Timestamp recorded
- ✅ Purchase reference tracked
- ✅ Audit trail maintained

---

## Business Rules

### Purchase Rules
- ✅ Only active products can be purchased
- ✅ Cannot purchase same product twice
- ✅ Unique reference generated (PUR-XXXXXXXXXXXX)
- ✅ Payment simulated (instant completion)

### Download Rules
- ✅ Must have completed purchase
- ✅ Product must have attached file
- ✅ Download URL expires after time limit
- ✅ URL is user-specific
- ✅ Every download is logged

---

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Purchase successful |
| 400 | Bad Request - Business logic error |
| 401 | Unauthorized - Missing/invalid auth |
| 403 | Forbidden - Not purchased or expired URL |
| 404 | Not Found - Product/purchase not found |

---

## Common Errors

### Checkout
```json
{"message": "You have already purchased this product."}
{"message": "This product is not available for purchase."}
```

### Download
```json
{"message": "You have not purchased this product."}
{"message": "Invalid or expired download URL."}
{"message": "Invalid download URL for this user."}
{"message": "Product file is not available."}
```

---

## cURL Examples

### Purchase
```bash
curl -X POST http://localhost:8000/api/v1/checkout/5 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Library
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
curl -X GET "http://localhost:8000/api/v1/download/5?userId=10&expires=1706335800&signature=abc..." \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output downloaded-file.zip
```

### Sales (Creator)
```bash
curl -X GET http://localhost:8000/api/v1/sales \
  -H "Authorization: Bearer YOUR_CREATOR_TOKEN"
```

### Revenue (Creator)
```bash
curl -X GET http://localhost:8000/api/v1/revenue \
  -H "Authorization: Bearer YOUR_CREATOR_TOKEN"
```

---

## Models & Relationships

```
Purchase
├── user (BelongsTo User)
├── product (BelongsTo Product)
└── downloadLogs (HasMany DownloadLog)

DownloadLog
└── purchase (BelongsTo Purchase)
```

---

## Purchase Status Constants

```php
Purchase::STATUS_PENDING    = 'pending'
Purchase::STATUS_COMPLETED  = 'completed'
Purchase::STATUS_FAILED     = 'failed'
```

---

## URL Expiration Options

| Minutes | Hours | Max Duration |
|---------|-------|--------------|
| 1 | 0.02 | Minimum |
| 60 | 1 | Default |
| 120 | 2 | Recommended |
| 720 | 12 | Half day |
| 1440 | 24 | Maximum |

---

## Download Info Fields

| Field | Description | Type |
|-------|-------------|------|
| `product_id` | Product identifier | integer |
| `product_title` | Product name | string |
| `file_name` | Original filename | string |
| `file_size` | Size in bytes | integer |
| `file_size_formatted` | Human-readable size | string |
| `mime_type` | File MIME type | string |
| `purchased_at` | Purchase timestamp | datetime |
| `download_stats` | Download statistics | object |

---

## Need More Details?

See **PURCHASE_LIBRARY_API_DOCUMENTATION.md** for:
- Complete API documentation
- Security architecture details
- Error handling guide
- Integration examples
- Testing scenarios