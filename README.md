# Mikrotik Manager

This project is a centralized management system for Mikrotik routers using a PHP/PostgreSQL or PHP/MySQL backend.

## Requirements

* PHP >= 7.2
* PostgreSQL or MySQL
* `php-ssh2` extension

## Installation

1. Clone the repository.
2. Create a database.
3. Copy `server/config.php.example` to `server/config.php`.
4. Edit `server/config.php` with your database credentials.
5. Set the `$dbType` variable to either `pgsql` or `mysql`.
6. Import the appropriate schema file into your database:
   * For PostgreSQL: `psql -U your_user -d your_database -f server/schema.sql`
   * For MySQL: `mysql -u your_user -p your_database < server/schema.mysql.sql`
7. Run `composer install` in the `server` directory.

## Usage

* The admin interface is located at `server/public/admin`.
* Routers will call home to `server/public/index.php`.
* Scripts for the routers are located in the `mikrotik` directory.
