# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with Vite for asset bundling and TailwindCSS v4 for styling.
## Miscellaneous instructions
Keep all important values in config files for easy edits. 
Use declared agents when needed.
Keep track of the history of the features you develop in CHANGELOG.md. You can add note to yourself in it if feature are not done yet or unfinished or any other reason.

## Important Rules
- Stylistic vision: Calm UI. 
- Use methods that support failure like instead of "find()" use "findOrFail()"
- No fat controllers or business logic in models (use service layer)
- Never call `env()` directly, always use `config()`
- Zero tolerance for sloppy code, unnecessary changes, or backward compatibility breaks
- Every line goes to production - be smart about decisions
- Keep it simple - angry senior engineers will review every line, no overkill
- No profanity in generated code
- Don't open suspiciously large files
- Use Playwright only to validate significant progress milestones
- Remove Laravel boilerplate comments from artisan-generated files
- Run `vendor/bin/pint --dirty` before finalizing

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

## Playwright
- Desktop resolution: 1920x1080
- Wait for loading components to finish
- Check laravel.log for errors when tests fail

## Architecture Patterns
- **Service Layer**: Controllers stay thin, business logic lives in services with dependency injection
- **Repository Pattern**: All DB queries go through repositories with interfaces for testability

## Performance Optimization
- **N+1 queries**: Use eager loading (`with`, `withCount`) and test queries in tinker
- **Large datasets**: Chunk in batches of 200
- **Caching**: Use tagged cache for related data with appropriate TTL
- 
## Security Essentials
- **Form Requests**: Always validate input with FormRequest classes, sanitize in `prepareForValidation()`
- **Mass Assignment**: Define `$fillable` on all models
- **Auth**: Use policies for authorization checks

### Authorization
```php
// Policy
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}

// Controller
public function update(UpdatePostRequest $request, Post $post): JsonResponse
{
    $this->authorize('update', $post);
    // Update logic
}
```
## File Creation
- Use artisan commands to generate files (check available options with `list-artisan-commands`)

## Frontend Issues
- If changes not reflecting: confirm `npm run dev` is running
- Check JS errors with `browser-logs` tool
- 
## Type Hints & Conventions
- Always use return types and constructor promotion
- Descriptive boolean method names: `isRegisteredForDiscounts()` not `discount()`
- Check sibling files for existing patterns before creating new ones

- Remember: Leverage Boost tools, follow Laravel conventions, separate concerns, optimize queries.
- Don't use comments unless the line is difficult to understand.
