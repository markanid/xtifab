# XT Tab Project Estimation Portal

## Architecture

The portal uses Laravel 12, the existing `nwidart/laravel-modules` package, one shared
`users` table, and separate `Authentication`, `Companies`, `Projects`,
`Deliverables`, `Billing`, `Notifications`, and `Dashboard` modules. A shared user
model keeps authentication simple while role middleware (`super_admin`,
`xt_tab_user`, `customer_user`) and company-scoped queries isolate customer data.

Core tables are `companies`, `projects`, `project_drawings`, `deliverables`,
`project_activities`, `invoices`, `invoice_items`, `invoice_payments`,
`notifications`, and `settings`. Important records use foreign keys, indexes, and
soft deletes where audit retention matters.

Files use the private Laravel `local` disk through
`Modules\Projects\Contracts\ProjectFileStorageInterface`. Controllers never expose storage paths. Set
`PORTAL_FILESYSTEM_DISK` to another configured private disk when migrating to S3,
Azure, SharePoint, or OneDrive, then provide a matching interface implementation.

## Setup

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

No new PDF package was installed. The invoice page is print-ready in the browser.
Database notifications work without mail or queue workers. If queued email channels
are added later, configure `MAIL_*`, keep `QUEUE_CONNECTION=database`, and run:

```bash
php artisan queue:work
```

## Development logins

The development seeder reads `DEMO_PASSWORD`, defaulting locally to
`ChangeMe!2026`. Change it before seeding any shared environment.

- Super administrator: `admin@xttab.test`
- Staff estimator: `staff@xttab.test`
- Staff billing user: `billing@xttab.test`
- Customer users: `customer1@example.test`, `customer2@example.test`

## Routes and permissions

- `/staff/login`, `/staff/dashboard`, `/staff/companies`, `/staff/customers`,
  `/staff/projects`, `/staff/invoices`
- `/customer/login`, `/customer/dashboard`, `/customer/projects`,
  `/customer/invoices`
- `/notifications` is shared, while file downloads are authenticated,
  rate-limited, and ownership checked.

`super_admin` bypasses gates. `xt_tab_user` can manage portal business records.
`customer_user` is limited to its active company and cannot self-register, upload
deliverables, change workflow status, or record payments.

## Verification

```bash
vendor/bin/pint
php artisan route:list --except-vendor
php artisan test
```

The feature suite covers portal separation, disabled registration, project and
drawing creation, tenant isolation, deliverable access, completion notification
deduplication, invoice approval, payment recalculation, and suspended-company login.

## Optional enhancements

- Granular database-backed permissions beyond the current role gates.
- Deliverable version chains and configurable customer access to old versions.
- Customer password-change UI, staff password reset, and credential delivery.
- Scheduled near-due/overdue invoice notifications and queued email channels.
- PDF generation after selecting an approved Laravel 12-compatible package.
- Additional CRUD edit/archive screens and cached dashboard charts.
