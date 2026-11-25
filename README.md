# Ad_Dreamwear.Co

Ad_Dreamwear.Co is a Laravel 13 fashion catalogue. Visitors can browse collections, see new and featured dresses, view photos and prices, and contact the business. The starter Lounge Dresses collection uses the eight supplied photographs, with each dress listed at ₦6,000.

## Open this local installation

- [Public catalogue](http://127.0.0.1:8017/)
- [Owner sign-in](http://127.0.0.1:8017/admin/login)
- [Owner dashboard](http://127.0.0.1:8017/admin)

The local `.env` points to the project’s isolated MySQL 8 instance on `127.0.0.1:3317`. The schema is `ad_dreamwear`. Keep `.env` private and keep the instance on loopback. The initial MySQL data directory is under `work/mysql-data` in the workspace outside the application folder.

If you need to restart that database on this computer, open PowerShell in the application folder and run the following in its own terminal:

```powershell
$db = (Resolve-Path ../../work/mysql-data).Path
& 'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqld.exe' --no-defaults --console --bind-address=127.0.0.1 --port=3317 --mysqlx=OFF "--datadir=$db"
```

In a second terminal, start the site with `php artisan serve --host=127.0.0.1 --port=8017`. Configure the PHP upload limits below if you intend to upload large photographs.

## Create the owner account

Run this command in the application folder. You can replace the email with the owner’s preferred sign-in address.

```sh
php artisan catalogue:admin titilolaraji20@gmail.com
```

Enter and confirm a password at the hidden prompts. Choose at least 14 characters, with a maximum of 72 bytes. Then sign in at `/admin/login`. Use the same command with `--reset` to change the password and revoke database sessions for that account.

Once signed in, choose **Change password** in the admin navigation. Enter your current password and confirm the new one. The current session stays signed in and other sessions are revoked.

## Manage dresses and website content

- **Dresses → Add dress.** Choose a collection. Enter a name, description and price in naira. Add a main photograph and optionally select up to 12 gallery photos. Accepts JPEG, PNG and WebP up to 6 MB each, with dimensions up to 4096 × 4096.
- **Dresses → Edit dress.** Replace the main image, add or remove gallery photos, edit details, price and available sizes, or delete the dress. Enter sizes separated by commas and leave the field blank until you can confirm them. Select **Published on the website** to make the dress public. **Available**, **Show in Featured** and **Show in New Arrivals** control how it is presented. Lower display positions appear first.
- **Collections.** Add or rename a collection and edit its description. Move or delete its dresses before removing a collection.
- **Website.** Edit homepage headings, introductory copy, About and contact text, phone numbers, WhatsApp enquiry number, email, social links, footer and the announcement. Add a main banner and an optional mobile version. Clear the announcement to hide it. Select **Save website changes** to publish the edits.
- **Overview.** Check product and collection counts and see recently added main and gallery photos.

The launch names and descriptions were written from what is visible in the photos. The starter records mark all eight dresses available and use 08122965207 for WhatsApp enquiries. The owner can update these details before launch.

## Set up a fresh installation

Use PHP 8.4.1 or later, Composer 2, Node.js 22.12 or later, and MySQL 8. Enable Laravel’s required PHP extensions plus PDO MySQL and GD with JPEG, PNG and WebP support. Enable PDO SQLite for the default test suite and EXIF for JPEG orientation metadata.

1. Create a dedicated MySQL schema and a schema-scoped account. Connect as a MySQL administrator and replace the password placeholder before running:

   ```sql
   CREATE DATABASE ad_dreamwear CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'dreamwear'@'127.0.0.1' IDENTIFIED BY 'REPLACE_WITH_A_STRONG_PASSWORD';
   GRANT ALL PRIVILEGES ON ad_dreamwear.* TO 'dreamwear'@'127.0.0.1';
   ```

2. Copy `.env.example` to `.env`. Set `APP_URL` and the `DB_*` values for your installation, matching the database user host to the application host.
3. From the application folder run:

   ```sh
   composer install
   npm ci
   npm run build
   php artisan key:generate
   php artisan migrate --seed
   ```

4. Create the owner account and start the local server as described above. The seeder creates missing starter records. Use it on the initial installation to avoid reintroducing any starter style the owner has since deleted.

## Run tests

```sh
npm run build
php artisan test
```

The PHPUnit suite defaults to an in-memory SQLite database. Use a dedicated, disposable database if you override the test database configuration.

Verification for this delivery passed on SQLite and MySQL 8.0.46 with 8 tests and 179 assertions per database. Chromium checks passed at widths from 320 to 1440 pixels. The browser run also verified CSRF protection, owner sign-in and sign-out, multipart photo upload, publishing, gallery navigation and deletion against the local MySQL site.


## Deploy to production

1. Set the virtual host document root to the application’s `public/` directory and route requests through `public/index.php`. Use HTTPS. If you use a reverse proxy, configure Laravel to trust only the intended proxy and forward the original HTTPS scheme.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to your HTTPS hostname, and `SESSION_SECURE_COOKIE=true`. Keep the database credentials and the generated, stable `APP_KEY` in the deployment’s secret storage. Use the database session driver.
3. Allow the PHP service user to write `storage/` and `bootstrap/cache/`. Persist `storage/app/private/catalogue` across releases. The application serves uploaded images through Laravel, so a public storage symlink is unnecessary.
4. Set `upload_max_filesize=6M`, `post_max_size=84M`, `max_file_uploads=20` and `memory_limit=256M` in web PHP. Set the web-server request body limit to at least 84 MB for multi-image forms.
5. Run the following against the production database. Run the key-generation command only when provisioning a new application without a key. Use the seed command on the first launch if you want the supplied dresses loaded.

   ```sh
   composer install --no-dev --prefer-dist --optimize-autoloader
   npm ci
   npm run build
   php artisan migrate --force
   php artisan db:seed --force
   php artisan optimize
   ```

6. Create the production owner account through the console. Check `/up`, the home and detail pages, sign-in and an image upload over HTTPS. Back up the MySQL database and the private catalogue images as one set.

See [Laravel deployment documentation](https://laravel.com/docs/13.x/deployment) for web-server examples.

## NairaHost deployment

- [Live catalogue](https://162.35.56.229/)
- [Live owner sign-in](https://162.35.56.229/admin/login)
- [Live health check](https://162.35.56.229/up)

The release is installed at `/var/www/ad-dreamwear` on the Ubuntu VPS. MySQL uses the separate `ad_dreamwear` schema. The `ad-dreamwear-web` systemd service serves HTTPS on port 443 and renews the Let’s Encrypt IP certificate. Its Caddy configuration is at `/etc/ad-dreamwear/Caddyfile`. PHP runs through a separate pool at `/etc/php/8.4/fpm/pool.d/ad-dreamwear.conf`. Open the live catalogue with HTTPS. Port 80 serves an existing site on this machine.

To provision the owner from a private PowerShell window, use:

```powershell
ssh -t root@162.35.56.229 "cd /var/www/ad-dreamwear && runuser -u ad-dreamwear -- /usr/bin/php8.4 artisan catalogue:admin titilolaraji20@gmail.com"
```

The ED25519 server fingerprint is `SHA256:Nxqli72W8OabbVY34qpZ/jiZGOy26CAw7TmuDrbPiZ4`. The SSH prompt takes the existing server password. The next two hidden prompts take the new owner password and its confirmation. After setup, use the live sign-in link above.
