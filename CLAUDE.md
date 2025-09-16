# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with Vite for asset bundling and TailwindCSS v4 for styling.
## Miscellaneous instructions
Keep all important values in config files for easy edits. 
Use declared agents when needed.
Keep track of the history of the features you develop in CHANGELOG.md. You can add note to yourself in it if feature are not done yet or unfinished or any other reason. 
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
