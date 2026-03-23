# 🧩 Slim Framework CRUD REST API — Product Management

A lightweight **RESTful API** built with **Slim Framework 4** (PHP micro-framework) demonstrating all CRUD operations on a **Product** resource. Uses **Eloquent ORM** (illuminate/database) for database access, **PHP-DI** for dependency injection, and **PHPUnit + Guzzle** for automated testing.

---

## 📌 Why Slim Framework?

| Framework | Type | Best For |
|-----------|------|----------|
| **Slim 4** | Micro-framework | Lightweight REST APIs, no bloat |
| Laravel | Full-stack | Large apps, rapid full-featured dev |
| Symfony | Full-stack | Enterprise, highly configurable |
| CodeIgniter | Lightweight | Simple apps, beginner-friendly |

**Slim** was chosen for its minimalism — it gives you just routing, middleware, and PSR-7 request/response. You add only what you need (like Eloquent for the ORM), keeping the project lean and transparent.

---

## 🗂 Project Structure

```
slim-crud/
├── public/
│   └── index.php              ← Front controller + all route definitions
├── src/
│   ├── Controllers/
│   │   └── ProductController.php   ← CRUD logic (index, store, show, update, destroy)
│   ├── Middleware/
│   │   └── JsonBodyParserMiddleware.php  ← Parses JSON request bodies
│   └── Models/
│       └── Product.php              ← Eloquent model
├── config/
│   └── container.php          ← PHP-DI container (DB + controller wiring)
├── database/
│   └── migrate.php            ← Migration + seeder script
├── tests/
│   └── ProductApiTest.php     ← 12 PHPUnit + Guzzle integration tests
├── .env.example               ← Environment template
├── composer.json
└── phpunit.xml
```

---

## ⚙️ Installation

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL **or** SQLite (SQLite requires no extra setup)

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/slim-crud-api.git
cd slim-crud-api

# 2. Install dependencies
composer install

# 3. Set up environment
cp .env.example .env
```

Edit `.env` for **MySQL**:
```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=slim_crud
DB_USERNAME=root
DB_PASSWORD=your_password
```

Or use **SQLite** (zero setup):
```env
DB_DRIVER=sqlite
DB_DATABASE=/absolute/path/to/slim-crud/database/database.sqlite
```

```bash
# 4. Run migration (creates the products table)
php database/migrate.php

# With sample data:
php database/migrate.php --seed

# Drop and recreate from scratch:
php database/migrate.php --fresh

# 5. Start the development server
composer start
# → http://localhost:8080/api
```

---

## 🌐 API Endpoints

Base URL: `http://localhost:8080/api`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/products` | List all products (paginated) |
| GET | `/products?search=keyboard` | Search name/description |
| GET | `/products?category=Electronics` | Filter by category |
| GET | `/products?page=2&per_page=5` | Pagination controls |
| POST | `/products` | Create a new product |
| GET | `/products/{id}` | Get product by ID |
| PUT | `/products/{id}` | Update a product (partial ok) |
| DELETE | `/products/{id}` | Delete a product |
| GET | `/ping` | Health check |

---

## 📦 Request / Response Examples

### POST /api/products — Create
```json
// Request body (Content-Type: /json)
{
  "name": "Wireless Keyboard",
  "description": "Compact 75% layout with RGB backlight",
  "price": 129.99,
  "quantity": 50,
  "category": "Electronics"
}

{
  "success": true,
  "message": "Product created successfully.",
  "data": {
    "id": 1,
    "name": "Wireless Keyboard",
    "price": 129.99,
    "quantity": 50,
    "category": "Electronics",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

### GET /api/products — List (paginated)
```json
{
  "success": true,
  "message": "Products retrieved successfully.",
  "data": {
    "current_page": 1,
    "per_page": 10,
    "total": 5,
    "last_page": 1,
    "data": [ { "id": 1, "name": "Wireless Keyboard", ... }, ... ]
  }
}
```

### PUT /api/products/1 — Partial Update
```json
{ "price": 99.99, "quantity": 75 }

// Response 200 OK
{
  "success": true,
  "message": "Product updated successfully.",
  "data": { "id": 1, "price": 99.99, "quantity": 75, ... }
}
```

### Validation Error 422
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "price": ["The price field is required."],
    "quantity": ["The quantity must be an integer."]
  }
}
```

### Not Found 404
```json
{ "success": false, "message": "Product not found." }
```

---

## 🧪 Running Tests

> Tests require the server to be running (`composer start`) in a separate terminal.

```bash
# Terminal 1
composer start

# Terminal 2
composer test
# or with verbose output:
./vendor/bin/phpunit --testdox
```

**12 tests covering:**
- ✅ Ping / health check
- ✅ List all products with pagination
- ✅ Search products by name/description
- ✅ Filter products by category
- ✅ Create a product (valid payload)
- ✅ Validation rejects empty payload (422)
- ✅ Validation rejects negative price (422)
- ✅ Get a product by ID
- ✅ Get returns 404 for missing ID
- ✅ Update a product (partial fields)
- ✅ Update returns 404 for missing ID
- ✅ Delete a product (confirm 404 after)

---

## 📬 Postman Testing Guide

1. Open Postman → New Collection → `Slim CRUD API`
2. Add a **Collection Variable**: `base_url = http://localhost:8080/api`
3. For all POST/PUT requests: **Body → raw → JSON**

| Request Name | Method | URL | Body |
|---|---|---|---|
| Health Check | GET | `{{base_url}}/ping` | — |
| List Products | GET | `{{base_url}}/products` | — |
| Search Products | GET | `{{base_url}}/products?search=keyboard` | — |
| Create Product | POST | `{{base_url}}/products` | See example above |
| Get Product | GET | `{{base_url}}/products/1` | — |
| Update Product | PUT | `{{base_url}}/products/1` | `{"price": 99.99}` |
| Delete Product | DELETE | `{{base_url}}/products/1` | — |

**Recommended test order in Postman:**
1. Create → note the `id` in the response
2. Get by that `id`
3. List all — confirm it appears
4. Update — change price/quantity
5. Delete — confirm `success: true`
6. Get again — confirm `404`

---

## 🛡️ Validation Rules

| Field | Rules |
|-------|-------|
| `name` | Required, string, max 255 chars |
| `description` | Optional, string |
| `price` | Required, numeric, min:0 |
| `quantity` | Required, integer, min:0 |
| `category` | Required, string, max 100 chars |

For `PUT` requests, all fields are **optional** — only the fields you include are validated and updated.

---

## 🔄 Slim vs Laravel — Key Differences

| Feature | Slim 4 (this project) | Laravel 11 |
|---|---|---|
| Setup | Manual wiring via PHP-DI | Auto-discovery |
| Routing | Manual `$app->get(...)` | `Route::apiResource(...)` |
| Validation | Custom `validate()` method | `Validator::make(...)` facade |
| ORM | Eloquent (added manually) | Eloquent (built-in) |
| Migration | Plain PHP script | `php artisan migrate` |
| Testing | PHPUnit + Guzzle (HTTP) | PHPUnit (in-process) |
| Lines of code | ~300 total | ~400+ (more config files) |
