<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Susu SaaS Application Architecture

This is a modern multi-tenant Susu SaaS application engineered using Laravel, Livewire, Livewire Volt, and Tailwind CSS. Below is a detailed breakdown of the application's multi-tenant architecture, tiered subscription plan limits, and super-admin controls.

### 1. Multi-Tenant Architecture & Database Layout
The application leverages **single-database multi-tenancy** designed around the `Tenant` model.
* **Scoping & Middlewares**:
  - `IdentifyTenant` middleware dynamically determines the active tenant context using either the host subdomain/slug or the authenticated user's `tenant_id`.
  - To prevent tenant data leaks, the `BelongsToTenant` scope is automatically applied across crucial models: `User`, `Book`, `Contribution`, `Loan`, `LoanPayment`, `Ledger`, `Payment`, `Setting`, and `Announcement`.
  - Static scoping utility `Tenant::setTenantId($id)` governs the active tenant context. For console tasks or queued jobs, `Tenant::forTenant($tenantId, $callback)` allows dynamic tenant switching.
* **Tenant Lifecycle & Isolation**:
  - Registering a new organization via `/register` creates a new `Tenant` with a unique name and URL slug.
  - The registering user is created as an administrative user with the `'admin'` role under that tenant, and default settings are seeded.

### 2. Tiered Subscription Plans & Usage Limits
SaaS subscription plans are handled programmatically to limit the resources available to each organization. There are three primary tiers:

* **Free**:
  - Max users: 10
  - Max books: 10
  - Max loans: 5
* **Premium**:
  - Max users: 100
  - Max books: 200
  - Max loans: 100
* **Enterprise**:
  - Virtually unlimited (999,999) users, books, and loans.

The limits are enforced on the backend via `$tenant->hasReachedLimit($feature)` checks before new users, books, or loans can be added. If a tenant hits a limit, appropriate validation errors are triggered.

### 3. Super Admin Dashboard & Controls
The application features a built-in Super Admin role (`'super_admin'`) and a dedicated management dashboard `/super-admin`.
* **Access Control**: Regular tenant admins (`'admin'`) and standard users are blocked with a `403 Forbidden` response. Only authenticated `'super_admin'` users can access this page.
* **Management Operations**:
  - **Modify Plan**: Super-admins can upgrade or downgrade any tenant's plan (`free`, `premium`, `enterprise`) instantly.
  - **Tenant Suspension / Status Control**: Super-admins can suspend an entire organization's access by toggling their status to `inactive`.
  - **Suspension Enforcement**: Inactive tenants are blocked from any regular application flow with a 403 response indicating suspension. Super-admins themselves are allowed to bypass this block for management.

---

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
