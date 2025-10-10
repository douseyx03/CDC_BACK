# CDC Back Office API

Backend Laravel application powering the CDC customer demand platform. It exposes public endpoints for end users (demands, documents, services) as well as protected back-office features for agents, roles, and permissions. Notifications (email) and other long running tasks are dispatched through Laravel queues.

## Requirements

- PHP 8.2+
- Composer 2
- Node.js 18+ (for frontend asset builds used during local development)
- MySQL 8 (default `.env` values assume `root:root@127.0.0.1:8889`)
- Redis is optional; default queue driver uses MySQL via the `jobs` table

## Getting Started

1. Install PHP dependencies:
   ```bash
   composer install
   ```
2. Copy the environment file and adjust settings (DB, mail, URLs):
   ```bash
   cp .env.example .env
   ```
3. Generate an application key:
   ```bash
   php artisan key:generate
   ```
4. Run the migrations (includes tables for queues, roles/permissions, and domain models):
   ```bash
   php artisan migrate
   ```
5. Seed base roles/permissions (créés avec le guard `sanctum`) si besoin :
   ```bash
   php artisan db:seed --class=RolePermissionSeeder
   ```
6. Install JavaScript dependencies (optional for API only usage, required for Vite dev tooling):
   ```bash
   npm install
   ```

## Running the Application

- Serve the API locally:
  ```bash
  php artisan serve
  ```
- Optionally run the helper script defined in `composer.json` to launch the server, queue listener, log tailing, and Vite in parallel:
  ```bash
  composer dev
  ```

## Queues & Notifications

- Email notifications such as `AgentCredentialsNotification` and `DemandeUpdatedNotification` implement `ShouldQueue`. Jobs are stored in the `jobs` table (database driver).
- Start a worker and keep it running in the background so queued notifications are processed:
  ```bash
  php artisan queue:work --queue=default
  ```
- In development you can switch to synchronous dispatch by setting `QUEUE_CONNECTION=sync` in `.env` if you do not wish to run a worker.
- Failed jobs are logged in `failed_jobs`. Inspect failures via:
  ```bash
  php artisan queue:failed
  ```
  To retry all failed jobs:
  ```bash
  php artisan queue:retry all
  ```
- Mail delivery defaults to Mailtrap. The free plan is rate limited; if you hit errors such as `550 5.7.0 Too many emails per second`, pause/resume after a delay or upgrade your SMTP provider.

## API Overview

All routes live in `routes/api.php`. Highlights:

- Public (authenticated via Sanctum) endpoints for demands, documents, and services.
- Back-office routes prefixed with `/backoffice` guarded by the `super-admin` role: manage services, demands, documents, agents, roles, and permissions.
- Request validation classes live under `app/Http/Requests` (e.g., `StoreAgentRequest`, `UpdateAgentRequest`), ensuring role names exist for the `sanctum` guard and normalising string role payloads.
- Business logic for agents is handled in `app/Services/Agent/AgentService.php` where user creation, role assignment, and notification dispatch occur inside DB transactions.

## Testing

Run the application test suite with:
```bash
php artisan test
```

When testing queued notifications, either process queued jobs with `queue:work` or temporarily set `QUEUE_CONNECTION=sync`.

## Troubleshooting

- **Mailtrap rate limits**: Reduce the number of immediate notifications or switch to the `log` mail driver (`MAIL_MAILER=log`) during development.
- **Missing back-office roles**: Ensure roles intended for agent assignment exist with `guard_name = sanctum`. You can sync roles via Artisan Tinker:
  ```bash
  php artisan tinker --execute="Spatie\\Permission\\Models\\Role::where('name', 'verificateur')->update(['guard_name' => 'sanctum']);"
  ```
  Pour aligner toutes les permissions/roles existants :
  ```bash
  php artisan tinker --execute="Spatie\\Permission\\Models\\Role::query()->update(['guard_name' => 'sanctum']); Spatie\\Permission\\Models\\Permission::query()->update(['guard_name' => 'sanctum']);"
  ```
- **Queue not processing**: Confirm the worker is running and that the `jobs` table is emptying. Use `php artisan queue:flush` to clear stuck jobs during local development.

## License

This project is licensed under the MIT license.
