# Product CRUD Testing Checklist

## Pre-requisites

- [ ] Database migrations run successfully
- [ ] Application running (php artisan serve)
- [ ] Sanctum authentication configured
- [ ] Two test users created:
  - [ ] Creator User 1 (will create products)
  - [ ] Creator User 2 (for ownership testing)
- [ ] At least 2 categories created
- [ ] Test files prepared:
  - [ ] Test image (< 5MB, valid image format)
  - [ ] Test file (< 500MB, e.g., ZIP file)
  - [ ] Invalid image (> 5MB for validation testing)

---

## Setup Test Data

### 1. Create Test Categories
- [ ] POST `/api/v1/categories` - Create "E-Books" category
- [ ] POST `/api/v1/categories` - Create "Software" category
- [ ] Verify categories exist with GET `/api/v1/categories`

### 2. Obtain Authentication Tokens
- [ ] Login as Creator User 1 - Save token as TOKEN_1
- [ ] Login as Creator User 2 - Save token as TOKEN_2

---

## Test Cases

### A. Product Creation (Creator Only)

#### A1. Create Product Successfully
- [ ] POST `/api/v1/products` with valid data
  - Headers: `Authorization: Bearer TOKEN_1`
  - Body: `{"category_id": 1, "title": "Test Product 1", "description": "Test description", "price": 29.99}`
  - **Expected:** Status 201, product returned with `is_active: false`
  - [ ] Save product ID as PRODUCT_1_ID

#### A2. Create Product with Minimum Fields
- [ ] POST `/api/v1/products` with only required fields
  - Body: `{"category_id": 1, "title": "Minimal Product", "price": 9.99}`
  - **Expected:** Status 201, description is null

#### A3. Create Product - Validation Errors
- [ ] POST `/api/v1/products` without title
  - **Expected:** Status 400, validation error
- [ ] POST `/api/v1/products` without category_id
  - **Expected:** Status 400, validation error
- [ ] POST `/api/v1/products` without price
  - **Expected:** Status 400, validation error
- [ ] POST `/api/v1/products` with negative price
  - **Expected:** Status 400, validation error
- [ ] POST `/api/v1/products` with invalid category_id
  - **Expected:** Status 400, validation error

#### A4. Create Product - Unauthorized
- [ ] POST `/api/v1/products` without authentication
  - **Expected:** Status 401
- [ ] POST `/api/v1/products` as customer user (if available)
  - **Expected:** Status 403

---

### B. List Products

#### B1. List All Products
- [ ] GET `/api/v1/products`
  - **Expected:** Status 200, paginated list of products
  - [ ] Verify pagination structure (data, current_page, per_page, etc.)
  - [ ] Verify products include creator, category, image, file relationships

#### B2. Pagination
- [ ] GET `/api/v1/products?per_page=5`
  - **Expected:** 5 items per page
- [ ] GET `/api/v1/products?page=2`
  - **Expected:** Second page of results

#### B3. Filter by Category
- [ ] GET `/api/v1/products?category_id=1`
  - **Expected:** Only products from category 1
- [ ] GET `/api/v1/products?category_id=2`
  - **Expected:** Only products from category 2

#### B4. Search by Title
- [ ] GET `/api/v1/products?search=Test`
  - **Expected:** Products with "Test" in title or description
- [ ] GET `/api/v1/products?search=Nonexistent`
  - **Expected:** Empty results

#### B5. Search by Date
- [ ] Create a product today
- [ ] GET `/api/v1/products?search=2026-01-27` (use today's date)
  - **Expected:** Products created on that date

#### B6. Sort by Price Ascending
- [ ] GET `/api/v1/products?sort_by=price&sort_order=asc`
  - **Expected:** Products sorted by price lowest to highest
  - [ ] Verify first product has lowest price

#### B7. Sort by Price Descending
- [ ] GET `/api/v1/products?sort_by=price&sort_order=desc`
  - **Expected:** Products sorted by price highest to lowest
  - [ ] Verify first product has highest price

#### B8. Sort by Creation Date
- [ ] GET `/api/v1/products?sort_by=created_at&sort_order=desc`
  - **Expected:** Newest products first
- [ ] GET `/api/v1/products?sort_by=created_at&sort_order=asc`
  - **Expected:** Oldest products first

#### B9. Combined Filters
- [ ] GET `/api/v1/products?category_id=1&search=Test&sort_by=price&sort_order=asc`
  - **Expected:** Filtered, searched, and sorted results

---

### C. View Product Details

#### C1. Get Product Successfully
- [ ] GET `/api/v1/products/{PRODUCT_1_ID}`
  - **Expected:** Status 200, complete product with relationships

#### C2. Get Non-existent Product
- [ ] GET `/api/v1/products/99999`
  - **Expected:** Status 404

#### C3. Get Product - Unauthorized
- [ ] GET `/api/v1/products/{PRODUCT_1_ID}` without authentication
  - **Expected:** Status 401

---

### D. Update Product (Owner Only)

#### D1. Update Product Successfully
- [ ] PUT `/api/v1/products/{PRODUCT_1_ID}` with TOKEN_1
  - Body: `{"title": "Updated Product Title", "price": 39.99}`
  - **Expected:** Status 200, product updated

#### D2. Update Single Field
- [ ] PUT `/api/v1/products/{PRODUCT_1_ID}` with TOKEN_1
  - Body: `{"description": "New description only"}`
  - **Expected:** Status 200, only description updated

#### D3. Update Product - Not Owner
- [ ] PUT `/api/v1/products/{PRODUCT_1_ID}` with TOKEN_2 (different creator)
  - **Expected:** Status 403, "not authorized" message

#### D4. Update Product - Validation
- [ ] PUT `/api/v1/products/{PRODUCT_1_ID}` with negative price
  - **Expected:** Status 400, validation error

---

### E. Delete Product (Owner Only)

#### E1. Create Product for Deletion Test
- [ ] POST `/api/v1/products` - Create "To Delete Product"
  - [ ] Save as PRODUCT_DELETE_ID

#### E2. Delete Product Successfully
- [ ] DELETE `/api/v1/products/{PRODUCT_DELETE_ID}` with TOKEN_1
  - **Expected:** Status 204, no content

#### E3. Verify Deletion
- [ ] GET `/api/v1/products/{PRODUCT_DELETE_ID}`
  - **Expected:** Status 404 (soft deleted)

#### E4. Delete Product - Not Owner
- [ ] Create another product with TOKEN_1
- [ ] Try DELETE with TOKEN_2
  - **Expected:** Status 403

#### E5. Delete Product with Purchases (if applicable)
- [ ] Create product → Add purchase → Try delete
  - **Expected:** Status 400, "has purchases" message

---

### F. File Upload (Owner Only)

#### F1. Upload Product Image Successfully
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/upload-image` with TOKEN_1
  - Form data: `image: [valid image file]`
  - **Expected:** Status 201, ProductImage object returned
  - [ ] Verify image details (path, size, mime_type)

#### F2. Replace Product Image
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/upload-image` again
  - **Expected:** Status 201, new image replaces old

#### F3. Upload Product File Successfully
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/upload-file` with TOKEN_1
  - Form data: `file: [valid file]`
  - **Expected:** Status 201, ProductFile object returned
  - [ ] Verify file details (original_filename, size, mime_type)

#### F4. Upload Image - Not Owner
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/upload-image` with TOKEN_2
  - **Expected:** Status 403

#### F5. Upload File - Not Owner
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/upload-file` with TOKEN_2
  - **Expected:** Status 403

#### F6. Upload Invalid Image
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/upload-image` with non-image file
  - **Expected:** Status 400, validation error

#### F7. Upload Oversized Image
- [ ] POST with image > 5MB (if available)
  - **Expected:** Status 400, validation error

#### F8. Upload Without File
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/upload-image` without file
  - **Expected:** Status 400, validation error

---

### G. Publish/Unpublish Product (Owner Only)

#### G1. Publish Product Without Files
- [ ] Create new product
- [ ] POST `/api/v1/products/{id}/publish` immediately
  - **Expected:** Status 400, "must have both file and image" message

#### G2. Publish Product with Only Image
- [ ] Create new product
- [ ] Upload image only
- [ ] POST `/api/v1/products/{id}/publish`
  - **Expected:** Status 400, missing file

#### G3. Publish Product with Only File
- [ ] Create new product
- [ ] Upload file only
- [ ] POST `/api/v1/products/{id}/publish`
  - **Expected:** Status 400, missing image

#### G4. Publish Product Successfully
- [ ] Verify PRODUCT_1_ID has both image and file
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/publish` with TOKEN_1
  - **Expected:** Status 200, `is_active: true`

#### G5. Verify Published Product
- [ ] GET `/api/v1/products/{PRODUCT_1_ID}`
  - **Expected:** `is_active: true`

#### G6. Unpublish Product Successfully
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/unpublish` with TOKEN_1
  - **Expected:** Status 200, `is_active: false`

#### G7. Publish/Unpublish - Not Owner
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/publish` with TOKEN_2
  - **Expected:** Status 403
- [ ] POST `/api/v1/products/{PRODUCT_1_ID}/unpublish` with TOKEN_2
  - **Expected:** Status 403

---

### H. Get My Products (Creator Only)

#### H1. Get Own Products
- [ ] GET `/api/v1/products/my-products` with TOKEN_1
  - **Expected:** Status 200, only products created by TOKEN_1 user
  - [ ] Verify all products have matching creator_id

#### H2. Get My Products - Pagination
- [ ] GET `/api/v1/products/my-products?per_page=5`
  - **Expected:** Status 200, 5 items per page

#### H3. Get My Products - Customer Role
- [ ] GET `/api/v1/products/my-products` as customer (if applicable)
  - **Expected:** Status 403

---

### I. Edge Cases and Security

#### I1. SQL Injection Protection
- [ ] GET `/api/v1/products?search='; DROP TABLE products; --`
  - **Expected:** Status 200, no SQL injection

#### I2. XSS Protection
- [ ] POST `/api/v1/products` with XSS in title
  - Body: `{"title": "<script>alert('xss')</script>", ...}`
  - **Expected:** Title stored/escaped safely

#### I3. Rate Limiting (if implemented)
- [ ] Make 100+ rapid requests
  - **Expected:** Rate limit response if configured

#### I4. Large Pagination
- [ ] GET `/api/v1/products?per_page=10000`
  - **Expected:** Handled gracefully (max limit applied)

---

## Complete Workflow Test

### J. End-to-End Product Creation Workflow

- [ ] **Step 1:** Login as Creator (TOKEN_1)
- [ ] **Step 2:** Create product
  - POST `/api/v1/products`
  - Verify `is_active: false`
  - Save product ID
- [ ] **Step 3:** Upload image
  - POST `/api/v1/products/{id}/upload-image`
  - Verify image returned
- [ ] **Step 4:** Upload file
  - POST `/api/v1/products/{id}/upload-file`
  - Verify file returned
- [ ] **Step 5:** Publish product
  - POST `/api/v1/products/{id}/publish`
  - Verify `is_active: true`
- [ ] **Step 6:** Update product
  - PUT `/api/v1/products/{id}`
  - Verify changes applied
- [ ] **Step 7:** View in listings
  - GET `/api/v1/products`
  - Verify product appears
- [ ] **Step 8:** View in my products
  - GET `/api/v1/products/my-products`
  - Verify product appears
- [ ] **Step 9:** Unpublish product
  - POST `/api/v1/products/{id}/unpublish`
  - Verify `is_active: false`

---

## Performance Tests (Optional)

- [ ] List products with 100+ products in database
- [ ] Search with complex queries
- [ ] Upload large files (close to limit)
- [ ] Multiple filters applied simultaneously

---

## Summary

**Total Test Cases:** ~70+

**Required Pass Rate:** 100% for core functionality

**Critical Tests:**
- Product CRUD operations
- Ownership validation
- Publishing requirements
- File upload functionality
- Filtering and sorting

---

## Notes

- Replace `{PRODUCT_1_ID}` with actual product ID from creation step
- Replace `TOKEN_1` and `TOKEN_2` with actual authentication tokens
- Use tools like Postman, Insomnia, or cURL for testing
- Check Laravel logs for any unexpected errors
- Verify database state after destructive operations

---

## Test Results Template

```
Test Date: __________
Tester: __________

Total Tests: 70+
Passed: ___
Failed: ___
Skipped: ___

Critical Issues Found:
1. 
2. 
3. 

Minor Issues Found:
1.
2.
3.

Overall Status: [ ] PASS [ ] FAIL
```
