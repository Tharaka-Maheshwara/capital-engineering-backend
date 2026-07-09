Overview

This document describes step-by-step instructions to deploy the Laravel backend to Render and provision a managed Postgres database (recommended: Neon or Supabase). It assumes you have a Render account and the repo is pushed to GitHub.

Quick plan

1. Create a Render Web Service by importing `capital-engineering-backend/render.yaml` from GitHub.
2. Provision a managed Postgres (Neon or Supabase) and copy credentials.
3. Set environment variables (see `.env.render.example`).
4. Configure build & start commands and deploy. Run migrations.
5. Add a worker service for queues (optional).
6. Update frontend `NEXT_PUBLIC_API_BASE_URL` after backend URL is known.

Render: import service

1. Log in to https://dashboard.render.com.
2. Click "New" → "Import from GitHub" and select this repository.
3. When Render asks which service to import, pick the `render.yaml` definition in `capital-engineering-backend/` (Render will create the Web Service and any workers defined in that file).

Environment variables

Set these environment variables in Render (Settings → Environment → Environment Secrets). Fill values from your DB provider and Cloudinary/Mailer providers.

Required

- APP_NAME=Capital Engineering
- APP_ENV=production
- APP_DEBUG=false
- APP_KEY=base64:GENERATED_BY_PHP_ARTISAN_KEY_GENERATE
- APP_URL=https://<your-backend>.onrender.com

- DB_CONNECTION=pgsql
- DB_HOST=<your-db-host>
- DB_PORT=<your-db-port>
- DB_DATABASE=<your-db-name>
- DB_USERNAME=<your-db-user>
- DB_PASSWORD=<your-db-pass>

- FRONTEND_URLS=https://<your-frontend>.vercel.app,https://<your-domain>.lk
- CLOUDINARY_URL=cloudinary://<key>:<secret>@<cloud_name>

Mail (example using SMTP)

- MAIL_MAILER=smtp
- MAIL_HOST=<smtp-host>
- MAIL_PORT=<smtp-port>
- MAIL_USERNAME=<smtp-user>
- MAIL_PASSWORD=<smtp-pass>
- MAIL_ENCRYPTION=tls
- MAIL_FROM_ADDRESS=hello@your-domain.lk
- MAIL_FROM_NAME="Capital Engineering"

Optional / Queue

- QUEUE_CONNECTION=database
- REDIS_HOST= (if using Redis for queues)

Build & Start commands

Use these in the Render service settings (or let `render.yaml` set them):

Build Command

composer install --no-dev --optimize-autoloader
php artisan key:generate --show > /tmp/appkey && echo "APP_KEY=$(cat /tmp/appkey)" > .env.render
php artisan config:cache

Start Command

php artisan migrate --force && php artisan queue:work --sleep=3 --tries=3

Notes:

- The `key:generate` command above shows a pattern for generating an APP_KEY during build; in production you should set `APP_KEY` as an ENV secret instead of generating on each build.
- Running migrations during start can be OK, but you may prefer to run them manually from the Render dashboard or via a deploy hook once DB creds are set.

Database: Neon / Supabase (recommended)

- Neon (https://neon.tech): Create a free project, create a database, and copy the connection string. Use `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from Neon.
- Supabase (https://supabase.com): Create a project, go to Settings → Database → Connection string.

After deploy

1. Visit your backend URL `https://<your-backend>.onrender.com` and check `/api/health` or `/`.
2. Ensure CORS: `config/cors.php` uses `env('FRONTEND_URLS')` — set it to your frontend domain(s).
3. Set `APP_URL` to the backend URL and update `NEXT_PUBLIC_API_BASE_URL` in Vercel (frontend deploy).
4. Run `php artisan migrate --force` from Render dashboard Console if you didn't run migrations automatically.

Queue worker

If you rely on queued jobs (image uploads, mail), add a separate Render Worker:

- Start command: `php artisan queue:work --sleep=3 --tries=3`.
- Ensure `QUEUE_CONNECTION` and any Redis credentials are set.

Storage & Media

- The app uses Cloudinary for media; set `CLOUDINARY_URL` in env.
- If you need local disk uploads, use an S3-compatible provider and set `FILESYSTEM_DRIVER=s3` and S3 credentials.

DNS and .lk domain

- After the service is live, note the Render service URL (e.g. `your-backend.onrender.com`).
- In your DNS provider for the .lk domain, create a CNAME or A record pointing to Render per Render docs.
- For a custom domain, add it in Render dashboard → Settings → Custom Domains and follow verification steps; Render will provide TLS certificates automatically.

Rollback & logs

- Use Render dashboard Deploys and Logs to inspect failed deploys.
- For problems, open the Console (Render provides SSH-like shell) to run `php artisan tinker`, `php artisan migrate:status`, etc.

Security

- Do not commit `.env` or secret values. Store secrets only in Render environment settings.

Troubleshooting

- If migrations fail, check DB creds and network access.
- If CORS blocks requests, ensure `FRONTEND_URLS` includes your frontend domain.

Want me to continue?

I can:

- Walk through importing `render.yaml` into Render step-by-step and set up exact env values.
- Provision a Postgres instance with Neon or Supabase and show exactly what to paste into Render.
- Add a Render Worker service in `render.yaml` if you want me to create it here.

Tell me which of the above to do next and I'll proceed.
