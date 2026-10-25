# Simple Time Attendance

PHP (PDO) + MySQL backend, HTML/CSS/vanilla JS frontend.
Staff enter their details and press **Check in** or **Check out**. The server records the date and time (Africa/Lagos), so the browser clock cannot change it.

## Run with Docker (recommended)

```
cp .env.example .env        # then edit the passwords
docker compose up --build -d
```
Open http://localhost:8080 (or the `WEB_PORT` you set).

- `docker compose logs -f web` / `docker compose logs -f db` – logs
- `docker compose down` – stop; the `db_data` volume keeps your records
- `docker compose down -v` – stop AND permanently delete all records
- Backup: `docker compose exec db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" attendance_db' > backup.sql`
- Restore: `docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" attendance_db' < backup.sql`
- The schema is loaded only the first time the volume is created. Keep `DB_NAME=attendance_db` (the schema file creates that name).

## Setup without Docker

1. Create the database and table:
   ```
   mysql -u root -p < sql/schema.sql
   ```
2. Set your MySQL login in `includes/config.php` (or via the `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` environment variables).
3. Run it (PHP 8.1+ with `pdo_mysql`), serving the `public/` folder:
   ```
   php -S localhost:8000 -t public
   ```
4. Open http://localhost:8000

On Apache/Nginx, point the document root at `public/` so `includes/` and `sql/` are never web-accessible.

## Rules
- One check-in per employee per day; check-out only after check-in, once per day.
- All queries use prepared statements; the table is rendered with `textContent` (no HTML injection).

## Files
- `public/index.html`, `style.css`, `app.js` – the page
- `public/api/record.php` – POST: save a check-in/out
- `public/api/records.php` – GET: list records by date and search text
- `includes/` – config and shared helpers (outside the web root)
- `sql/schema.sql` – database schema

## Not included (kept simple)
No login, so anyone with the page can record for any employee ID. Add authentication before using this for real payroll or compliance purposes.
