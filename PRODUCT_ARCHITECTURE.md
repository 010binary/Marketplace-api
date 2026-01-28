# Product CRUD Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         BlooCode Platform                            │
│                       Product CRUD System                            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Architecture Layers

```
┌─────────────────────────────────────────────────────────────────────┐
│                          CLIENT LAYER                                │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐           │
│  │  Web App │  │  Mobile  │  │   API    │  │  Third   │           │
│  │          │  │   App    │  │  Client  │  │  Party   │           │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘           │
│         │            │             │             │                  │
│         └────────────┴─────────────┴─────────────┘                  │
│                          │                                           │
│                    HTTP/HTTPS                                        │
│                          │                                           │
└──────────────────────────┼───────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      API LAYER (Laravel)                             │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                     Routes (v1.php)                            │  │
│  │  • Authentication Middleware (Sanctum)                        │  │
│  │  • Role Middleware (Creator/Customer)                         │  │
│  └───────────┬───────────────────────────────────────────────────┘  │
│              │                                                       │
│              ▼                                                       │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │              CONTROLLERS (HTTP Layer)                         │  │
│  │  ┌──────────────────┐  ┌──────────────────────────────────┐  │  │
│  │  │ ProductController│  │ ProductFileController            │  │  │
│  │  │                  │  │                                  │  │  │
│  │  │ • index()        │  │ • uploadFile()                   │  │  │
│  │  │ • show()         │  │ • uploadImage()                  │  │  │
│  │  │ • store()        │  │ • authorizeOwnership()           │  │  │
│  │  │ • update()       │  │                                  │  │  │
│  │  │ • destroy()      │  │                                  │  │  │
│  │  │ • publish()      │  │                                  │  │  │
│  │  │ • unpublish()    │  │                                  │  │  │
│  │  │ • myProducts()   │  │                                  │  │  │
│  │  └────────┬─────────┘  └────────────┬─────────────────────┘  │  │
│  │           │                          │                        │  │
│  │           │     ┌────────────────────┘                        │  │
│  │           │     │                                             │  │
│  └───────────┼─────┼─────────────────────────────────────────────┘  │
│              │     │                                                │
│              ▼     ▼                                                │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │              SERVICE LAYER (Business Logic)                   │  │
│  │  ┌──────────────────────┐  ┌──────────────────────────────┐  │  │
│  │  │  ProductService      │  │  ProductFileService          │  │  │
│  │  │                      │  │                              │  │  │
│  │  │ • paginate()         │  │ • uploadProductFile()        │  │  │
│  │  │ • findById()         │  │ • uploadDisplayImage()       │  │  │
│  │  │ • create()           │  │                              │  │  │
│  │  │ • update()           │  │                              │  │  │
│  │  │ • delete()           │  │                              │  │  │
│  │  │ • publish()          │  │                              │  │  │
│  │  │ • unpublish()        │  │                              │  │  │
│  │  │ • getCreatorProducts()│ │                              │  │  │
│  │  │ • authorizeOwnership()│ │                              │  │  │
│  │  └──────────┬───────────┘  └────────────┬─────────────────┘  │  │
│  │             │                            │                    │  │
│  └─────────────┼────────────────────────────┼────────────────────┘  │
│                │                            │                       │
│                ▼                            ▼                       │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                    MODEL LAYER (Eloquent ORM)                 │  │
│  │  ┌──────────┐  ┌────────┐  ┌─────────────┐  ┌─────────────┐  │  │
│  │  │ Product  │  │Category│  │ProductFile  │  │ProductImage │  │  │
│  │  │          │  │        │  │             │  │             │  │  │
│  │  │ Relations│  │        │  │             │  │             │  │  │
│  │  │ • creator│  │        │  │             │  │             │  │  │
│  │  │ • category│ │        │  │             │  │             │  │  │
│  │  │ • file   │──┼────────┼──┼──────►      │  │             │  │  │
│  │  │ • image  │──┼────────┼──┼──────────────┼──┼──────►      │  │  │
│  │  │ • purchases│ │        │  │             │  │             │  │  │
│  │  └──────────┘  └────────┘  └─────────────┘  └─────────────┘  │  │
│  └───────────────────────────────────────────────────────────────┘  │
└──────────────────────────┬────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      DATA LAYER                                      │
│  ┌────────────────────┐              ┌───────────────────────────┐  │
│  │  MySQL Database    │              │   File Storage            │  │
│  │  ┌──────────────┐  │              │   ┌─────────────────────┐ │  │
│  │  │  products    │  │              │   │  Private Disk       │ │  │
│  │  │  categories  │  │              │   │  ┌────────────────┐ │ │  │
│  │  │  product_files│ │              │   │  │ products/      │ │ │  │
│  │  │  product_images│ │             │   │  │   {id}/        │ │ │  │
│  │  │  purchases   │  │              │   │  │     file/      │ │ │  │
│  │  │  users       │  │              │   │  │     image/     │ │ │  │
│  │  └──────────────┘  │              │   │  └────────────────┘ │ │  │
│  └────────────────────┘              │   └─────────────────────┘ │  │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Request Flow Diagram

### Create Product Flow

```
┌──────────┐
│  Client  │
└─────┬────┘
      │ POST /api/v1/products
      │ {category_id, title, description, price}
      ▼
┌──────────────────────┐
│ Sanctum Middleware   │ ──► Verify Bearer Token
└──────┬───────────────┘
       │ ✓ Authenticated
       ▼
┌──────────────────────┐
│ Role Middleware      │ ──► Check if Creator
└──────┬───────────────┘
       │ ✓ Is Creator
       ▼
┌──────────────────────┐
│ ProductController    │
│   store()            │ ──► Validate Request
└──────┬───────────────┘
       │ ✓ Valid Data
       ▼
┌──────────────────────┐
│ ProductService       │
│   create()           │ ──► Business Logic
└──────┬───────────────┘      • Set creator_id
       │                      • Set is_active = false
       ▼
┌──────────────────────┐
│ Product Model        │
│   create()           │ ──► Insert to Database
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Response             │ ──► Return Product JSON
│ Status: 201 Created  │      with relationships
└──────────────────────┘
```

### Publish Product Flow

```
┌──────────┐
│  Client  │
└─────┬────┘
      │ POST /api/v1/products/{id}/publish
      ▼
┌──────────────────────┐
│ Authentication       │ ──► Verify Token
└──────┬───────────────┘
       │ ✓
       ▼
┌──────────────────────┐
│ ProductController    │
│   publish()          │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ ProductService       │
│   publish()          │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Authorize Ownership  │ ──► product.creator_id == user.id?
└──────┬───────────────┘
       │ ✓ Is Owner
       ▼
┌──────────────────────┐
│ Validate Requirements│ ──► Has image AND file?
└──────┬───────────────┘
       │ ✓ Has Both
       ▼
┌──────────────────────┐
│ Update Product       │ ──► Set is_active = true
│ product.update()     │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Response             │ ──► Return Updated Product
│ Status: 200 OK       │
└──────────────────────┘
```

### File Upload Flow

```
┌──────────┐
│  Client  │
└─────┬────┘
      │ POST /api/v1/products/{id}/upload-file
      │ multipart/form-data: file
      ▼
┌──────────────────────┐
│ Authentication       │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ ProductFileController│
│   uploadFile()       │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Authorize Ownership  │ ──► Verify Owner
└──────┬───────────────┘
       │ ✓
       ▼
┌──────────────────────┐
│ Validate File        │ ──► Size, Type, Required
└──────┬───────────────┘
       │ ✓
       ▼
┌──────────────────────┐
│ ProductFileService   │
│ uploadProductFile()  │
└──────┬───────────────┘
       │
       ├──► Delete Old File (if exists)
       │
       ├──► Store New File (Private Disk)
       │
       └──► Create ProductFile Record
              │
              ▼
       ┌──────────────────┐
       │ Response         │ ──► Return ProductFile JSON
       │ Status: 201      │
       └──────────────────┘
```

---

## Database Schema

```
┌─────────────────────────┐
│       products          │
├─────────────────────────┤
│ id (PK)                 │
│ creator_id (FK→users)   │◄─────┐
│ category_id (FK→categories)│    │
│ title                   │      │
│ description             │      │ One-to-Many
│ price                   │      │
│ is_active               │      │
│ created_at              │      │
│ updated_at              │      │
│ deleted_at              │      │
└──┬──────────────────┬───┘      │
   │                  │          │
   │ One-to-One       │          │
   │                  │          │
   ▼                  ▼          │
┌─────────────┐  ┌──────────────┐│
│product_files│  │product_images││
├─────────────┤  ├──────────────┤│
│ id (PK)     │  │ id (PK)      ││
│ product_id  │  │ product_id   ││
│ disk        │  │ disk         ││
│ path        │  │ path         ││
│ original_   │  │ mime_type    ││
│   filename  │  │ size         ││
│ mime_type   │  │ created_at   ││
│ size        │  │ updated_at   ││
│ created_at  │  └──────────────┘│
│ updated_at  │                  │
└─────────────┘                  │
                                 │
┌─────────────────────────────┐  │
│         users               │  │
├─────────────────────────────┤  │
│ id (PK)                     │──┘
│ name                        │
│ email                       │
│ password                    │
│ role (creator/customer)     │
│ created_at                  │
│ updated_at                  │
└─────────────────────────────┘

┌─────────────────────────────┐
│        categories           │
├─────────────────────────────┤
│ id (PK)                     │
│ name                        │
│ slug                        │
│ created_at                  │
│ updated_at                  │
└─────────────────────────────┘

┌─────────────────────────────┐
│        purchases            │
├─────────────────────────────┤
│ id (PK)                     │
│ user_id (FK→users)          │
│ product_id (FK→products)    │
│ reference                   │
│ status                      │
│ created_at                  │
│ updated_at                  │
└─────────────────────────────┘
```

---

## Security Architecture

```
┌────────────────────────────────────────────────────────────┐
│                    Security Layers                         │
└────────────────────────────────────────────────────────────┘

Layer 1: Authentication
┌─────────────────────────────────────────┐
│ Laravel Sanctum                         │
│ • Token-based authentication            │
│ • Bearer token in Authorization header  │
│ • Validates user identity               │
└─────────────────────────────────────────┘
                    │
                    ▼
Layer 2: Authorization
┌─────────────────────────────────────────┐
│ Role Middleware                         │
│ • Creator role for mutations            │
│ • Customer role for purchases           │
└─────────────────────────────────────────┘
                    │
                    ▼
Layer 3: Ownership
┌─────────────────────────────────────────┐
│ ProductService::authorizeOwnership()    │
│ • Verify creator_id == user.id          │
│ • Throws 403 if not owner               │
│ • Applied to: update, delete,           │
│   publish, unpublish, file uploads      │
└─────────────────────────────────────────┘
                    │
                    ▼
Layer 4: Validation
┌─────────────────────────────────────────┐
│ Laravel Request Validation              │
│ • Required fields                       │
│ • Data types                            │
│ • Min/max values                        │
│ • File size limits                      │
└─────────────────────────────────────────┘
                    │
                    ▼
Layer 5: Business Rules
┌─────────────────────────────────────────┐
│ Service Layer                           │
│ • Cannot delete products with purchases │
│ • Cannot publish without files          │
│ • Products default to unpublished       │
└─────────────────────────────────────────┘
                    │
                    ▼
Layer 6: Storage Security
┌─────────────────────────────────────────┐
│ Private Disk Storage                    │
│ • Files not publicly accessible         │
│ • Controlled download access            │
│ • Separate from public assets           │
└─────────────────────────────────────────┘
```

---

## Filter & Search Architecture

```
┌──────────────────────────────────────────────────────────┐
│              ProductService::paginate()                   │
└──────────────────────────────────────────────────────────┘
                        │
        ┌───────────────┼───────────────┐
        │               │               │
        ▼               ▼               ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   Filter     │ │   Search     │ │    Sort      │
│              │ │              │ │              │
│ category_id  │ │ • title      │ │ • price      │
│ WHERE        │ │ • description│ │ • created_at │
│ category_id  │ │ • created_at │ │              │
│   = ?        │ │   (date)     │ │ ASC / DESC   │
│              │ │              │ │              │
│              │ │ LIKE %?%     │ │ ORDER BY     │
│              │ │ OR           │ │              │
│              │ │ DATE(?) = ?  │ │              │
└──────────────┘ └──────────────┘ └──────────────┘
        │               │               │
        └───────────────┼───────────────┘
                        │
                        ▼
                ┌──────────────┐
                │  Eloquent    │
                │  Query       │
                │  Builder     │
                └──────┬───────┘
                       │
                       ▼
                ┌──────────────┐
                │  Paginate    │
                │  (per_page)  │
                └──────────────┘
```

---

## Component Responsibilities

### Controllers (HTTP Layer)
✓ Handle HTTP requests/responses
✓ Validate input data
✓ Call service methods
✓ Return JSON responses
✗ NO business logic
✗ NO direct database access

### Services (Business Logic Layer)
✓ Implement business rules
✓ Authorization checks
✓ Data manipulation
✓ Coordinate between models
✗ NO HTTP concerns
✗ NO direct request/response handling

### Models (Data Layer)
✓ Database table representation
✓ Relationships
✓ Attribute casting
✓ Query scopes
✗ NO business logic
✗ NO authorization

### Middleware
✓ Authentication verification
✓ Role-based access control
✓ Request preprocessing
✗ NO business logic

---

## API Endpoint Matrix

```
┌─────────────────────────────────────────────────────────────┐
│  Endpoint                  │ Method │ Auth │ Role │ Owner   │
├────────────────────────────┼────────┼──────┼──────┼─────────┤
│ /products                  │  GET   │  ✓   │  Any │   -     │
│ /products/{id}             │  GET   │  ✓   │  Any │   -     │
│ /products                  │  POST  │  ✓   │ Cr   │   -     │
│ /products/{id}             │  PUT   │  ✓   │ Cr   │   ✓     │
│ /products/{id}             │ DELETE │  ✓   │ Cr   │   ✓     │
│ /products/{id}/publish     │  POST  │  ✓   │ Cr   │   ✓     │
│ /products/{id}/unpublish   │  POST  │  ✓   │ Cr   │   ✓     │
│ /products/my-products      │  GET   │  ✓   │ Cr   │   -     │
│ /products/{id}/upload-file │  POST  │  ✓   │ Cr   │   ✓     │
│ /products/{id}/upload-image│  POST  │  ✓   │ Cr   │   ✓     │
└────────────────────────────┴────────┴──────┴──────┴─────────┘

Legend:
  Auth = Authentication Required
  Cr = Creator Role
  Owner = Must be product owner
```

---

## Technology Stack

```
┌─────────────────────────────────────────┐
│         Framework & Core                │
│  • Laravel 10.x/11.x                    │
│  • PHP 8.1+                             │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│         Authentication                  │
│  • Laravel Sanctum                      │
│  • Token-based auth                     │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│         Database                        │
│  • MySQL                                │
│  • Eloquent ORM                         │
│  • Migrations                           │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│         File Storage                    │
│  • Laravel Storage                      │
│  • Private Disk                         │
│  • Local/S3 compatible                  │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│         API Documentation               │
│  • OpenAPI 3.0                          │
│  • PHP Attributes (darkaonline/swagger) │
│  • Swagger UI                           │
└─────────────────────────────────────────┘
```

---

## Scalability Considerations

```
Current Architecture → Optimized Architecture

┌──────────────────┐      ┌──────────────────┐
│ Single Server    │      │ Load Balanced    │
│ • App + DB       │  →   │ • Multiple App   │
│ • File Storage   │      │   Servers        │
└──────────────────┘      │ • Separate DB    │
                          │ • S3 Storage     │
                          └──────────────────┘

┌──────────────────┐      ┌──────────────────┐
│ No Caching       │      │ Redis Cache      │
│ • Every request  │  →   │ • Product lists  │
│   hits DB        │      │ • Query results  │
└──────────────────┘      └──────────────────┘

┌──────────────────┐      ┌──────────────────┐
│ Synchronous      │      │ Queue Jobs       │
│ File Processing  │  →   │ • File uploads   │
│                  │      │ • Image resize   │
└──────────────────┘      └──────────────────┘
```

---

## Design Patterns Used

1. **Repository Pattern** (via Services)
   - Abstracts data access
   - Business logic separation

2. **Dependency Injection**
   - Constructor injection in controllers
   - Services injected

3. **Single Responsibility Principle**
   - Controllers handle HTTP only
   - Services handle business logic
   - Models handle data

4. **Factory Pattern**
   - Eloquent model creation
   - Service instantiation

5. **Strategy Pattern**
   - Different sorting strategies
   - Different search strategies