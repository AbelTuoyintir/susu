# Susu SaaS Multi-Tenant Platform Architecture

This application is built as a highly scalable, multi-tenant Software as a Service (SaaS) Susu savings and loan platform. This document describes the architecture, key SaaS components, tiered limits, and how multi-tenancy is enforced.

---

## 1. Multi-Tenancy Architecture

We employ a single-database multi-tenancy model. Isolation is accomplished logically using unique identifier columns (`tenant_id`) across tenant-owned database tables, scoped transparently with Laravel Eloquent Global Scopes.

### A. Tenant Scoping (`BelongsToTenant` Trait)
Most models utilize the `App\Traits\BelongsToTenant` trait. This trait:
1. **Applies global query scopes:** Automatically filters query results to include only records belonging to the active tenant (`where('tenant_id', $tenantId)`).
2. **Assigns Tenant IDs on Creation:** Intercepts Eloquent `creating` events and automatically injects the active/current `tenant_id` if missing, preventing cross-tenant data leaks.

Scoped models include:
- `User`, `Book`, `Contribution`, `Loan`, `LoanPayment`, `Ledger`, `Payment`, `Setting`, `Announcement`

### B. Tenant Context Identification (`IdentifyTenant` Middleware)
The `App\Http\Middleware\IdentifyTenant` middleware dynamically sets the active tenant context for every HTTP request:
1. **Subdomain Detection:** Looks at the request host to extract subdomains (e.g., `company.susu.com` where `company` is the tenant slug).
2. **Session / Auth Context Fallback:** If subdomains are not used or are running on local loopbacks, the middleware falls back to the authenticated user's `tenant_id`.
3. **Execution Context Binding:** Sets the detected tenant ID into `Tenant::setTenantId($id)` so that the `BelongsToTenant` Eloquent trait query scopes operate seamlessly.

---

## 2. Plan Tiers & Limits

The application supports three plans with ascending usage limits to drive SaaS monetization:

| Plan | User Limit | Book Limit | Loan Limit |
|---|---|---|---|
| **Free** | 10 Users | 10 Books | 5 Loans |
| **Premium** | 100 Users | 200 Books | 100 Loans |
| **Enterprise** | Unlimited (999,999) | Unlimited (999,999) | Unlimited (999,999) |

These limits are programmatically configured in the `App\Models\Tenant` model (`Tenant::$tierLimits`).

### Enforcement Mechanism
Before a tenant creates users, books, or loans, the application checks usage against the current subscription tier limits:
- **`Tenant::hasReachedLimit($feature)`:** Queries the database without the global tenant scope to calculate actual usage metrics of a tenant, returning a boolean.
- Usage validation occurs at the Livewire or Controller level (e.g., in `UserController@store` or Livewire Volt page save actions) to gracefully trigger validation errors and prevent creation on overshoot.

---

## 3. Organization Onboarding (SaaS Registration Flow)

The registration form (`/register`) acts as a frictionless SaaS onboarding funnel:
1. **Simultaneous Creation:** A user fills in organization credentials alongside their administrative credentials.
2. **Organization (Tenant) Setup:** Creates a `Tenant` record with a unique slug and plan designated as `'free'` by default.
3. **Admin User Generation:** Dynamically registers the user, links them to the brand-new `Tenant` ID, and assigns the `'admin'` role.
4. **Environment Seeding:** Bootstraps default, customizable settings for the specific tenant.
5. **Dashboard Redirection:** Redirects seamlessly to the tenant-scoped dashboard with full tenant branding loaded.

---

## 4. Super Admin Management Dashboard

A Super Admin role can log in to view and manage all tenants on the platform:
- **Plan Modification:** Upgrade/Downgrade tenants between Free, Premium, and Enterprise plans.
- **Account Status (Active vs. Suspended):** Easily toggle status. If a tenant is marked `inactive` (suspended), the `IdentifyTenant` middleware intercepts future requests and throws a `403 Forbidden` response preventing any tenant users from logging in or using the app (with a bypass rule reserved exclusively for Super Admins).

---

## 5. Automated E2E SaaS Verification

The application includes an end-to-end user registration and redirection verification suite:
- **Playwright Test Script (`verify_saas.py`):** Drives a headless browser to complete the multi-tenant onboarding process, registering a new company and admin user, checking the dashboard, and capturing screenshots in the verification directory.
- Run it with:
  ```bash
  python3 verify_saas.py
  ```
