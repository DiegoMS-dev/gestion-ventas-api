<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# ⚙️ Sales Management System – Backend API

A RESTful API developed with **Laravel** and **PHP**, designed to handle the business logic, data management and database operations of a sales and inventory management system.

This backend serves as the data layer for a Vue 3 Single Page Application (SPA), providing endpoints for communication between the frontend and the database.

## ✨ Features

- **Products:** Product registration, editing, deletion and retrieval.
- **Categories:** Category management and product relationships.
- **Clients:** Customer information management.
- **Suppliers:** Supplier management.
- **Purchases:** Purchase operations.
- **Inventory:** Stock management and control.
- **Sales:** Sales operations.
- **Reports:** Data retrieval for business analysis.
- **Dashboard:** Data endpoints for displaying business metrics and activity.

## 🚀 Technologies

| Technology | Purpose |
|---|---|
| PHP | Main programming language |
| Laravel 13 | Backend framework |
| MariaDB / MySQL | Relational database |
| Eloquent ORM | Database interaction and relationships |
| Laravel Migrations | Database structure management |
| REST API | Communication with the frontend |
| Laravel Sanctum | API authentication support |

## 🏗️ Architecture

The backend follows a RESTful API architecture.

It is responsible for:

- Processing HTTP requests.
- Validating incoming data.
- Executing business logic.
- Managing database operations.
- Handling relationships between entities.
- Returning JSON responses to the frontend.

The API is consumed by the Vue 3 frontend, which follows the Single Page Application (SPA) architecture.

## 📋 Requirements

- PHP 8.3 or higher.
- Composer.
- MariaDB or MySQL.
- Git.
- PHP extensions required by Laravel.

## ⚙️ Installation

**1. Clone the repository**

```bash
git clone https://github.com/DiegoMS-dev/gestion-ventas-api
cd gestion-ventas-api
```

**2. Install dependencies**

```bash
composer install
```

**3. Configure environment variables**

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

**4. Configure the database**

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_ventas
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database before running the migrations.

**5. Configure the application timezone**

In `config/app.php`:

```php
'timezone' => 'America/Lima',
```

**6. Run database migrations**

```bash
php artisan migrate
```

**7. Start the development server**

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000
```

API endpoints are available under:

```text
http://127.0.0.1:8000/api
```

## 🔗 Frontend

This API is designed to work with the following Vue 3 application:

[Sales Management System – Frontend](YOUR_FRONTEND_REPOSITORY_URL)

## 👨‍💻 Author

**Diego Mendieta Saucedo**

GitHub: [@DiegoMS-dev](https://github.com/DiegoMS-dev)

---

Developed as a software development project using PHP, Laravel and relational database technologies.
