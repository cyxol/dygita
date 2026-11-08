# Local Typecho deployment

Apply `nginx.conf` to the Nginx server that serves the Typecho root, then run
`nginx -t` before reloading. The Docker setup mounts it as
`/etc/nginx/conf.d/default.conf`.

The configuration denies hidden files, database storage, internal source folders,
deployment metadata and backup artifacts. Keep backups outside the web root.
Never commit `.env`, `config.inc.php`, database dumps or database storage.

Validate with HEAD requests: `/.env`, `/docker-compose.yml`, `/data/typecho/typecho_users.ibd`
and `/usr/themes/dygita/.git/config` must return 404; public CSS must return 200.

After placing the theme, run: php usr/themes/dygita/tools/install-runtime.php
This installs the DygitaRuntime plugin and registers routes explicitly. Re-run after theme updates.


Build frontend assets after pulling: npm ci; npm run build. js/dist is generated and is not tracked.
Search now accepts GET /action/dygita-search-index?q=keyword and returns at most 40 public articles; responses are not cached.


After backing up the database, run php usr/themes/dygita/tools/migrate-counters.php once (idempotent). It preserves legacy displayed counts and adds supporting indexes. Counters require MariaDB/MySQL.

## PostgreSQL migration staging

`postgres.compose.yml` provisions PostgreSQL 18.6 from the official image, pinned
to the digest resolved from `postgres:latest` on 2026-11-08. This is an empty
staging database; starting it does not migrate data or change the Typecho adapter.

Add these entries to the existing Typecho root `docker-compose.yml`:

```yaml
services:
  postgres:
    extends:
      file: ./usr/themes/dygita/deployment/postgres.compose.yml
      service: postgres
volumes:
  postgres-data:
```

Set a randomly generated `POSTGRES_PASSWORD` in the root `.env`; optionally set
`POSTGRES_DB` (default `typecho`). Run from the Typecho root:

```sh
docker compose config --quiet
docker compose up -d --no-deps --wait postgres
```

Connect from the host at `127.0.0.1:5432`, or from another Compose service at
`postgres:5432`. The bootstrap administrator is `postgres`; create a separate
application role before switching the blog. The port is bound to loopback only.
PostgreSQL 18 uses a named volume mounted at `/var/lib/postgresql`, with its
version-specific data directory beneath it. Keep the volume when recreating the
container; `docker compose down -v` deletes it.

Before migrating, add the PHP PostgreSQL driver, adapt the theme's MySQL-specific
counter upsert and migration SQL, convert the schema/data and sequences, then
compare content and associations against a backup. Keep MariaDB available until
the PostgreSQL-backed blog has passed regression checks.

Installation validation: health check, authenticated TCP connection, UTF-8/JSONB
write and read across a container restart, and rejection of an incorrect password.
Remove the probe table/schema after validation.

