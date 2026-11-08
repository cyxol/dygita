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

