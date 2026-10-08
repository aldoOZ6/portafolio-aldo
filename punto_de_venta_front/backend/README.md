# Backend

The application backend code lives in the `app/` folder at the project root. Controllers, helpers and views are organized there and are autoloaded using Composer's PSR-4 mapping (`App\` => `app/`).

Key locations:
- `app/Controllers` - controllers (ProductController, OrderController, AuthController)
- `app/Helpers` - database connection and helper classes
- `app/Views` - PHP view templates
- `public/index.php` - application entrypoint (front controller)

How to run locally (XAMPP on Windows):
1. Place the project under `c:\xampp\htdocs\punto_de_venta_front` (already here).
2. In XAMPP control panel start Apache and MySQL.
3. Point your virtual host document root to the `frontend` folder (recommended) or to `public`.
   - Example Apache VirtualHost DocumentRoot: `C:/xampp/htdocs/punto_de_venta_front/frontend`
4. Ensure `vendor/` and `composer` dependencies are present (they are included in this repo).
5. Import the SQL schema from `sql/schema.sql` into MySQL and adjust `app/Helpers/DB.php` with your DB credentials.
