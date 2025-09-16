# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with Vite for asset bundling and TailwindCSS v4 for styling.

## Common Commands

### Development
```bash
# Start all development services (Laravel server, Vite, queue worker, and logs)
composer run dev

# Start individual services
php artisan serve              # Start Laravel development server
npm run dev                     # Start Vite development server
php artisan queue:listen        # Start queue worker
php artisan pail               # Watch application logs
```

### Build & Testing
```bash
# Build frontend assets
npm run build

# Run tests
composer test                   # Run all tests
php artisan test               # Run tests directly
php artisan test --filter TestName  # Run specific test

# Code formatting
./vendor/bin/pint              # Format PHP code (Laravel Pint)
```

### Database
```bash
php artisan migrate            # Run database migrations
php artisan migrate:rollback   # Rollback last migration
php artisan migrate:fresh      # Drop all tables and re-run migrations
php artisan tinker             # Interactive PHP REPL with Laravel context
```

### Laravel Artisan
```bash
php artisan make:controller ControllerName
php artisan make:model ModelName
php artisan make:migration create_table_name
php artisan make:request RequestName
php artisan make:middleware MiddlewareName
```

## Architecture

### Directory Structure
- `app/` - Core application code
  - `Http/Controllers/` - HTTP controllers
  - `Models/` - Eloquent ORM models
  - `Providers/` - Service providers
- `routes/` - Application routing
  - `web.php` - Web routes
  - `console.php` - Console commands
- `resources/` - Frontend assets
  - `views/` - Blade templates
  - `css/app.css` - Main CSS file (with TailwindCSS)
  - `js/app.js` - Main JavaScript entry point
- `database/` - Database migrations and seeders
- `config/` - Application configuration files
- `tests/` - Test files (PHPUnit)

### Key Technologies
- **PHP 8.2+** with Laravel 12 framework
- **Vite** for asset bundling with hot module replacement
- **TailwindCSS v4** using the new Vite plugin
- **SQLite** as default database (see `database/database.sqlite`)
- **Laravel Pint** for PHP code formatting
- **PHPUnit 11** for testing

### Development Workflow
1. Frontend assets are processed through Vite (configured in `vite.config.js`)
2. TailwindCSS is integrated via `@tailwindcss/vite` plugin
3. Laravel Mix has been replaced with Vite for better performance
4. The `composer dev` command starts all necessary development services concurrently