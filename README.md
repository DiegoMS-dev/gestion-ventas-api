<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# ⚙️ Sistema de Gestión de Ventas – Backend API

API REST desarrollada con **Laravel** y **PHP**, diseñada para gestionar la lógica de negocio, el procesamiento de datos y las operaciones con la base de datos de un sistema de gestión de ventas e inventario.

Este backend funciona como la capa de datos de una aplicación frontend desarrollada con Vue 3 bajo la arquitectura **SPA (Single Page Application)**, proporcionando endpoints para la comunicación entre la interfaz de usuario y la base de datos.

## ✨ Características

- **Productos:** Registro, edición, eliminación y consulta de productos.
- **Categorías:** Administración de categorías y relaciones con productos.
- **Clientes:** Gestión de información de clientes.
- **Proveedores:** Administración de proveedores.
- **Compras:** Gestión de operaciones de compra.
- **Inventario:** Control y administración de existencias.
- **Ventas:** Gestión de operaciones de venta.
- **Reportes:** Obtención de información para el análisis del negocio.
- **Dashboard:** Endpoints para proporcionar indicadores y datos de la actividad del negocio.

## 🚀 Tecnologías utilizadas

| Tecnología | Descripción |
|---|---|
| PHP | Lenguaje de programación principal |
| Laravel 13 | Framework de desarrollo backend |
| MariaDB / MySQL | Sistema de gestión de bases de datos relacional |
| Eloquent ORM | Gestión de consultas y relaciones con la base de datos |
| Laravel Migrations | Administración de la estructura de la base de datos |
| API REST | Comunicación entre el frontend y el backend |
| Laravel Sanctum | Soporte para autenticación mediante API |

## 🏗️ Arquitectura

El backend utiliza una arquitectura basada en **API REST**, encargada de procesar las solicitudes del frontend y gestionar las operaciones del sistema.

Sus principales responsabilidades son:

- Procesamiento de solicitudes HTTP.
- Validación de los datos recibidos.
- Ejecución de la lógica de negocio.
- Gestión de operaciones con la base de datos.
- Administración de relaciones entre entidades.
- Envío de respuestas en formato JSON al frontend.

La API es consumida por una aplicación frontend desarrollada con Vue 3, que utiliza la arquitectura Single Page Application (SPA).

## 📋 Requisitos previos

- PHP 8.3 o superior.
- Composer.
- MariaDB o MySQL.
- Git.
- Extensiones de PHP requeridas por Laravel.

## ⚙️ Instalación y ejecución

**1. Clonar el repositorio**

```bash
git clone https://github.com/DiegoMS-dev/gestion-ventas-api
cd gestion-ventas-api
```

**2. Instalar las dependencias**

```bash
composer install
```

**3. Configurar las variables de entorno**

Copiar el archivo de configuración de ejemplo:

```bash
cp .env.example .env
```

Generar la clave de la aplicación:

```bash
php artisan key:generate
```

**4. Configurar la base de datos**

Editar el archivo `.env` con las credenciales de la base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_ventas
DB_USERNAME=root
DB_PASSWORD=your_password
```

Crear la base de datos antes de ejecutar las migraciones.

**5. Configurar la zona horaria**

En `config/app.php`, configurar la zona horaria correspondiente:

```php
'timezone' => 'America/Lima',
```

**6. Ejecutar las migraciones**

```bash
php artisan migrate
```

**7. Iniciar el servidor de desarrollo**

```bash
php artisan serve
```

La API estará disponible en:

```text
http://127.0.0.1:8000
```

Los endpoints de la API estarán disponibles bajo la ruta:

```text
http://127.0.0.1:8000/api
```

## 🔗 Frontend

Esta API está diseñada para trabajar junto con la siguiente aplicación desarrollada con Vue 3:

[Ver repositorio del Frontend – Sistema de Gestión de Ventas](https://github.com/DiegoMS-dev/gestion-ventas-web)

## 👨‍💻 Autor

**Diego Mendieta Saucedo**

GitHub: [@DiegoMS-dev](https://github.com/DiegoMS-dev)

---

Desarrollado como proyecto de desarrollo de software utilizando PHP, Laravel y tecnologías de bases de datos relacionales.
