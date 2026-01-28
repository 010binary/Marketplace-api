# Product API Documentation

This document describes the Product CRUD API endpoints for the BlooCode platform.

## Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Endpoints](#endpoints)
  - [List Products](#list-products)
  - [Get Product Details](#get-product-details)
  - [Create Product](#create-product)
  - [Update Product](#update-product)
  - [Delete Product](#delete-product)
  - [Publish Product](#publish-product)
  - [Unpublish Product](#unpublish-product)
  - [Get My Products](#get-my-products)
  - [Upload Product File](#upload-product-file)
  - [Upload Product Image](#upload-product-image)

---

## Overview

The Product API allows creators to manage their digital products. Products can have:
- Title, description, and price
- A category association
- A downloadable file (max 500MB)
- A display image (max 5MB)
- Published/unpublished status

### Key Features

- **Filtering**: Filter products by category
- **Searching**: Search by product name, description, or creation date
- **Sorting**: Sort by price or creation date (ascending/descending)
- **Ownership**: Only the creator who created a product can edit, delete, publish, or unpublish it
- **File Management**: Upload and manage product files and images

---

## Authentication

All endpoints require authentication using Laravel Sanctum.

Include the bearer token in the request header:
```
Authorization: Bearer {your-token}
```

### Role Requirements

- **Creator**: Required for creating, updating, deleting products and uploading files
- **Any authenticated user**: Can view products and product details

---

## Endpoints

### List Products

Get a paginated list of products with optional filtering, searching, and sorting.

**Endpoint:** `GET /api/v1/products`

**Authentication:** Required

**Query Parameters:**

| Parameter     | Type    | Required | Description                                          |
|--------------|---------|----------|------------------------------------------------------|
| `per_page`   | integer | No       | Number of items per page (default: 15)               |
| `page`       | integer | No       | Page number (default: 1)                             |
| `category_id`| integer | No       | Filter by category ID                                |
| `search`     | string  | No       | Search by title, description, or date (YYYY-MM-DD)   |
| `sort_by`    | string  | No       | Sort by field: `price` or `created_at`               |
| `sort_order` | string  | No       | Sort order: `asc` or `desc` (default: desc)          |

**Example Request:**
```bash
GET /api/v1/products?category_id=1&search=Laravel&sort_by=price&sort_order=asc&per_page=10
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "creator_id": 5,
      "category_id": 1,
      "title": "Advanced Laravel Course",
      "description": "Learn advanced Laravel concepts and best practices",
      "price": "49.99",
      "is_active": true,
      "created_at": "2026-01-27T03:16:30.000000Z",
      "updated_at": "2026-01-27T03:16:30.000000Z",
      "creator": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "creator"
      },
      "category": {
        "id": 1,
        "name": "E-Books",
        "slug": "e_books"
      },
      "image": {
        "id": 1,
        "product_id": 1,
        "disk": "private",
        "path": "products/1/image/abc123.jpg",
        "mime_type": "image/jpeg",
        "size": 204800
      },
      "file": {
        "id": 1,
        "product_id": 1,
        "disk": "private",
        "path": "products/1/file/xyz789.zip",
        "original_filename": "course-materials.zip",
        "mime_type": "application/zip",
        "size": 1048576
      }
    }
  ],
  "current_page": 1,
  "per_page": 10,
  "total": 50,
  "last_page": 5
}
```

---

### Get Product Details

Retrieve detailed information about a specific product.

**Endpoint:** `GET /api/v1/products/{id}`

**Authentication:** Required

**Path Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| `id`      | integer | Yes      | Product ID  |

**Example Request:**
```bash
GET /api/v1/products/1
```

**Example Response:**
```json
{
  "id": 1,
  "creator_id": 5,
  "category_id": 1,
  "title": "Advanced Laravel Course",
  "description": "Learn advanced Laravel concepts and best practices",
  "price": "49.99",
  "is_active": true,
  "created_at": "2026-01-27T03:16:30.000000Z",
  "updated_at": "2026-01-27T03:16:30.000000Z",
  "creator": { ... },
  "category": { ... },
  "image": { ... },
  "file": { ... }
}
```

---

### Create Product

Create a new product. Products are created as unpublished by default.

**Endpoint:** `POST /api/v1/products`

**Authentication:** Required (Creator role)

**Request Body:**

| Field         | Type    | Required | Description                    |
|---------------|---------|----------|--------------------------------|
| `category_id` | integer | Yes      | Category ID                    |
| `title`       | string  | Yes      | Product title (max: 255 chars) |
| `description` | string  | No       | Product description            |
| `price`       | decimal | Yes      | Product price (min: 0)         |

**Example Request:**
```bash
POST /api/v1/products
Content-Type: application/json

{
  "category_id": 1,
  "title": "Advanced Laravel Course",
  "description": "Learn advanced Laravel concepts and best practices",
  "price": 49.99
}
```

**Example Response:**
```json
{
  "id": 1,
  "creator_id": 5,
  "category_id": 1,
  "title": "Advanced Laravel Course",
  "description": "Learn advanced Laravel concepts and best practices",
  "price": "49.99",
  "is_active": false,
  "created_at": "2026-01-27T03:16:30.000000Z",
  "updated_at": "2026-01-27T03:16:30.000000Z"
}
```

**Status Code:** `201 Created`

---

### Update Product

Update an existing product. Only the creator who owns the product can update it.

**Endpoint:** `PUT /api/v1/products/{product}`

**Authentication:** Required (Creator role + Owner)

**Path Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| `product` | integer | Yes      | Product ID  |

**Request Body:**

| Field         | Type    | Required | Description                    |
|---------------|---------|----------|--------------------------------|
| `category_id` | integer | No       | Category ID                    |
| `title`       | string  | No       | Product title (max: 255 chars) |
| `description` | string  | No       | Product description            |
| `price`       | decimal | No       | Product price (min: 0)         |

**Example Request:**
```bash
PUT /api/v1/products/1
Content-Type: application/json

{
  "title": "Updated Laravel Course",
  "price": 59.99
}
```

**Example Response:**
```json
{
  "id": 1,
  "creator_id": 5,
  "category_id": 1,
  "title": "Updated Laravel Course",
  "description": "Learn advanced Laravel concepts and best practices",
  "price": "59.99",
  "is_active": false,
  "created_at": "2026-01-27T03:16:30.000000Z",
  "updated_at": "2026-01-27T04:20:15.000000Z"
}
```

**Status Code:** `200 OK`

**Error Responses:**
- `403 Forbidden`: Not the product owner
- `404 Not Found`: Product not found

---

### Delete Product

Delete a product. Only the creator who owns the product can delete it. Products with purchases cannot be deleted.

**Endpoint:** `DELETE /api/v1/products/{product}`

**Authentication:** Required (Creator role + Owner)

**Path Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| `product` | integer | Yes      | Product ID  |

**Example Request:**
```bash
DELETE /api/v1/products/1
```

**Example Response:**
No content

**Status Code:** `204 No Content`

**Error Responses:**
- `400 Bad Request`: Product has purchases
- `403 Forbidden`: Not the product owner
- `404 Not Found`: Product not found

---

### Publish Product

Publish a product to make it active and available for purchase. Only the creator who owns the product can publish it. Product must have both an image and a file before publishing.

**Endpoint:** `POST /api/v1/products/{product}/publish`

**Authentication:** Required (Creator role + Owner)

**Path Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| `product` | integer | Yes      | Product ID  |

**Example Request:**
```bash
POST /api/v1/products/1/publish
```

**Example Response:**
```json
{
  "id": 1,
  "creator_id": 5,
  "category_id": 1,
  "title": "Advanced Laravel Course",
  "description": "Learn advanced Laravel concepts and best practices",
  "price": "49.99",
  "is_active": true,
  "created_at": "2026-01-27T03:16:30.000000Z",
  "updated_at": "2026-01-27T05:10:45.000000Z",
  "creator": { ... },
  "category": { ... },
  "image": { ... },
  "file": { ... }
}
```

**Status Code:** `200 OK`

**Error Responses:**
- `400 Bad Request`: Product missing required files (image or file)
- `403 Forbidden`: Not the product owner
- `404 Not Found`: Product not found

---

### Unpublish Product

Unpublish a product to make it inactive and unavailable for purchase. Only the creator who owns the product can unpublish it.

**Endpoint:** `POST /api/v1/products/{product}/unpublish`

**Authentication:** Required (Creator role + Owner)

**Path Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| `product` | integer | Yes      | Product ID  |

**Example Request:**
```bash
POST /api/v1/products/1/unpublish
```

**Example Response:**
```json
{
  "id": 1,
  "creator_id": 5,
  "category_id": 1,
  "title": "Advanced Laravel Course",
  "description": "Learn advanced Laravel concepts and best practices",
  "price": "49.99",
  "is_active": false,
  "created_at": "2026-01-27T03:16:30.000000Z",
  "updated_at": "2026-01-27T06:30:20.000000Z",
  "creator": { ... },
  "category": { ... },
  "image": { ... },
  "file": { ... }
}
```

**Status Code:** `200 OK`

---

### Get My Products

Retrieve a paginated list of products created by the authenticated user.

**Endpoint:** `GET /api/v1/products/my-products`

**Authentication:** Required (Creator role)

**Query Parameters:**

| Parameter  | Type    | Required | Description                        |
|-----------|---------|----------|------------------------------------|
| `per_page`| integer | No       | Number of items per page (default: 15) |
| `page`    | integer | No       | Page number (default: 1)           |

**Example Request:**
```bash
GET /api/v1/products/my-products?per_page=20
```

**Example Response:**
```json
{
  "data": [ ... ],
  "current_page": 1,
  "per_page": 20,
  "total": 15,
  "last_page": 1
}
```

**Status Code:** `200 OK`

---

### Upload Product File

Upload or replace the downloadable file for a product. Only the creator who owns the product can upload files.

**Endpoint:** `POST /api/v1/products/{product}/upload-file`

**Authentication:** Required (Creator role + Owner)

**Content-Type:** `multipart/form-data`

**Path Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| `product` | integer | Yes      | Product ID  |

**Form Data:**

| Field  | Type | Required | Description                    |
|--------|------|----------|--------------------------------|
| `file` | file | Yes      | Product file (max: ~500MB)     |

**Example Request:**
```bash
POST /api/v1/products/1/upload-file
Content-Type: multipart/form-data

file: [binary file data]
```

**Example Response:**
```json
{
  "id": 1,
  "product_id": 1,
  "disk": "private",
  "path": "products/1/file/xyz789.zip",
  "original_filename": "course-materials.zip",
  "mime_type": "application/zip",
  "size": 1048576,
  "created_at": "2026-01-27T03:16:30.000000Z",
  "updated_at": "2026-01-27T03:16:30.000000Z"
}
```

**Status Code:** `201 Created`

**Error Responses:**
- `403 Forbidden`: Not the product owner
- `400 Bad Request`: Validation error (file too large, missing file)
- `404 Not Found`: Product not found

---

### Upload Product Image

Upload or replace the display image for a product. Only the creator who owns the product can upload images.

**Endpoint:** `POST /api/v1/products/{product}/upload-image`

**Authentication:** Required (Creator role + Owner)

**Content-Type:** `multipart/form-data`

**Path Parameters:**

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| `product` | integer | Yes      | Product ID  |

**Form Data:**

| Field   | Type  | Required | Description                |
|---------|-------|----------|----------------------------|
| `image` | image | Yes      | Product image (max: 5MB)   |

**Example Request:**
```bash
POST /api/v1/products/1/upload-image
Content-Type: multipart/form-data

image: [binary image data]
```

**Example Response:**
```json
{
  "id": 1,
  "product_id": 1,
  "disk": "private",
  "path": "products/1/image/abc123.jpg",
  "mime_type": "image/jpeg",
  "size": 204800,
  "created_at": "2026-01-27T03:16:30.000000Z",
  "updated_at": "2026-01-27T03:16:30.000000Z"
}
```

**Status Code:** `201 Created`

**Error Responses:**
- `403 Forbidden`: Not the product owner
- `400 Bad Request`: Validation error (file too large, not an image, missing file)
- `404 Not Found`: Product not found

---

## Business Rules

### Ownership
- Only the creator who created a product can:
  - Update the product
  - Delete the product
  - Publish the product
  - Unpublish the product
  - Upload files and images for the product

### Publishing Requirements
- A product must have both an image and a file before it can be published
- Products are created as unpublished by default (`is_active: false`)

### Deletion Restrictions
- A product cannot be deleted if it has any purchases
- This prevents data integrity issues with customer purchase history

### File Management
- Product files are stored in the `private` disk
- Uploading a new file replaces the existing one
- Uploading a new image replaces the existing one
- Files are automatically deleted when replaced

---

## Common Error Responses

### 401 Unauthenticated
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

### 404 Not Found
```json
{
  "message": "No query results for model [App\\Models\\Product] {id}"
}
```

### 400 Bad Request (Validation Error)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "price": ["The price must be at least 0."]
  }
}
```

### 400 Bad Request (Business Logic)
```json
{
  "message": "Product must have both a file and an image before publishing."
}
```

---

## Typical Product Creation Workflow

1. **Create Product** - `POST /api/v1/products`
   - Product is created as unpublished (`is_active: false`)

2. **Upload Product Image** - `POST /api/v1/products/{product}/upload-image`
   - Upload the display image for the product

3. **Upload Product File** - `POST /api/v1/products/{product}/upload-file`
   - Upload the downloadable file for the product

4. **Publish Product** - `POST /api/v1/products/{product}/publish`
   - Make the product active and available for purchase

5. **Manage Product** - Update, unpublish, or delete as needed

---

## Testing with cURL

### Create a Product
```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": 1,
    "title": "My New Product",
    "description": "Product description",
    "price": 29.99
  }'
```

### Upload Image
```bash
curl -X POST http://localhost:8000/api/v1/products/1/upload-image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@/path/to/image.jpg"
```

### Upload File
```bash
curl -X POST http://localhost:8000/api/v1/products/1/upload-file \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/file.zip"
```

### Publish Product
```bash
curl -X POST http://localhost:8000/api/v1/products/1/publish \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Search and Filter
```bash
curl -X GET "http://localhost:8000/api/v1/products?category_id=1&search=Laravel&sort_by=price&sort_order=asc" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Notes

- All timestamps are in UTC and follow ISO 8601 format
- Prices are stored as decimal values with 2 decimal places
- File paths use forward slashes regardless of OS
- The API uses soft deletes for products (they are not permanently removed from the database)