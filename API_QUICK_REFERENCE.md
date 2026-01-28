# Product API Quick Reference

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer {your-token}
```

---

## Product Endpoints

### Public (All authenticated users)

| Method | Endpoint | Description | Query Params |
|--------|----------|-------------|--------------|
| GET | `/products` | List all products | `per_page`, `page`, `category_id`, `search`, `sort_by`, `sort_order` |
| GET | `/products/{id}` | Get product details | - |

### Creator Only

| Method | Endpoint | Description | Body Fields |
|--------|----------|-------------|-------------|
| GET | `/products/my-products` | Get creator's products | Query: `per_page`, `page` |
| POST | `/products` | Create product | `category_id`*, `title`*, `price`*, `description` |
| PUT | `/products/{product}` | Update product | `category_id`, `title`, `price`, `description` |
| DELETE | `/products/{product}` | Delete product | - |
| POST | `/products/{product}/publish` | Publish product | - |
| POST | `/products/{product}/unpublish` | Unpublish product | - |
| POST | `/products/{product}/upload-file` | Upload product file | Form: `file`* (max 500MB) |
| POST | `/products/{product}/upload-image` | Upload product image | Form: `image`* (max 5MB) |

*Required fields

---

## Filter & Search Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `category_id` | integer | Filter by category | `?category_id=1` |
| `search` | string | Search title, description, or date | `?search=Laravel` or `?search=2026-01-27` |
| `sort_by` | string | Sort field: `price`, `created_at` | `?sort_by=price` |
| `sort_order` | string | Sort direction: `asc`, `desc` | `?sort_order=asc` |
| `per_page` | integer | Items per page (default: 15) | `?per_page=20` |
| `page` | integer | Page number (default: 1) | `?page=2` |

**Combined Example:**
```
GET /products?category_id=1&search=Laravel&sort_by=price&sort_order=asc&per_page=10
```

---

## Product Object Structure

```json
{
  "id": 1,
  "creator_id": 5,
  "category_id": 1,
  "title": "Advanced Laravel Course",
  "description": "Learn advanced Laravel concepts",
  "price": "49.99",
  "is_active": true,
  "created_at": "2026-01-27T03:16:30.000000Z",
  "updated_at": "2026-01-27T03:16:30.000000Z",
  "creator": { /* User object */ },
  "category": { /* Category object */ },
  "image": { /* ProductImage object or null */ },
  "file": { /* ProductFile object or null */ }
}
```

---

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Successful request |
| 201 | Created - Resource created successfully |
| 204 | No Content - Successful deletion |
| 400 | Bad Request - Validation error or business logic violation |
| 401 | Unauthorized - Missing or invalid authentication |
| 403 | Forbidden - Insufficient permissions or not owner |
| 404 | Not Found - Resource doesn't exist |

---

## Business Rules Summary

### Ownership
✅ Only the product creator can:
- Update the product
- Delete the product
- Publish/unpublish the product
- Upload files and images

### Publishing
✅ Product must have both `image` AND `file` before publishing
✅ Products default to unpublished (`is_active: false`)

### Deletion
❌ Cannot delete products with purchases
✅ Can only delete products you created

### File Uploads
- Files: Max 500MB (~512,000 KB)
- Images: Max 5MB (~5,120 KB)
- Uploading replaces existing file/image

---

## Typical Workflow

1. **Create Product** → `POST /products`
2. **Upload Image** → `POST /products/{id}/upload-image`
3. **Upload File** → `POST /products/{id}/upload-file`
4. **Publish** → `POST /products/{id}/publish`

---

## cURL Examples

### Create Product
```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"category_id":1,"title":"My Product","price":29.99}'
```

### Upload Image
```bash
curl -X POST http://localhost:8000/api/v1/products/1/upload-image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@image.jpg"
```

### Upload File
```bash
curl -X POST http://localhost:8000/api/v1/products/1/upload-file \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@file.zip"
```

### Publish
```bash
curl -X POST http://localhost:8000/api/v1/products/1/publish \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### List with Filters
```bash
curl "http://localhost:8000/api/v1/products?category_id=1&sort_by=price&sort_order=asc" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Category Endpoints (Reference)

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/categories` | All | List categories |
| GET | `/categories/{id}` | All | Get category details |
| POST | `/categories` | Creator | Create category |
| PUT | `/categories/{id}` | Creator | Update category |
| DELETE | `/categories/{id}` | Creator | Delete category |

---

## Need More Details?

See **PRODUCT_API_DOCUMENTATION.md** for comprehensive documentation with:
- Detailed request/response examples
- Complete error responses
- Additional context and explanations