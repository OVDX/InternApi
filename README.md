# News API



## 📋 Requirements

- PHP = 8.3
- Composer
- MySQL >= 8.0 
- Node.js  = 20 

## ⚙️ Installation

### 1. Clone repo

```bash
git clone https://github.com/OVDX/InternProject.git
cd news-api
```

### 2. Dependencies

```bash
composer install
```

### 3. Configure

```bash
# Copy
cp .env.example .env


php artisan key:generate
```

### 4. Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Create DB

```bash
# MySQL
mysql -u root -p
CREATE DATABASE news_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 6. Migrate

```bash
php artisan migrate
```

### 7. Create link for storage

```bash
php artisan storage:link
```

### 8. Generate Swagger Doc

```bash
php artisan l5-swagger:generate
```

### 9. Serve 

```bash
php artisan serve
```

API will be on : `http://localhost:8000`

## 📖 Doc API

After running open Swagger UI:

```
http://localhost:8000/api/documentation
```

## 🔑 Auth

API uses **Laravel Sanctum** with Bearer tokens.

### Register

```bash
POST /api/register
Content-Type: application/json

{
  "name": "Іван Петренко",
  "email": "ivan@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Іван Петренко",
    "email": "ivan@example.com"
  },
  "token": "1|abcdefghijklmnop..."
}
```

### Log in

```bash
POST /api/login
Content-Type: application/json

{
  "email": "ivan@example.com",
  "password": "password123"
}
```

### Token usage

add token to all secured endpoints
```bash
Authorization: Bearer 1|abcdefghijklmnop...
```

