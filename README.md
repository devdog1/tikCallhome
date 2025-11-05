# Mikrotik Centralized Management System

This project provides a web-based interface to centrally manage a fleet of Mikrotik routers. It allows administrators to adopt new routers, apply configuration commands, and monitor their network in a scalable way.

## Features

- **Centralized Dashboard:** A single pane of glass to view the status of all adopted routers.
- **Router Adoption:** A secure workflow for bringing new routers under management.
- **Group-Based Management:** Assign routers to groups to apply configurations at scale.
- **Command & Template Engine:** Create reusable configuration templates and apply them to routers, groups, or models. Idempotent `check_command` support ensures commands only run when needed.
- **Push & Pull Configuration:** Supports both server-initiated (push) and router-initiated (pull) configuration methods.
- **User & Permissions Management:**
    - Full user authentication with local accounts.
    - Role-Based Access Control (RBAC) with three default roles: Admin, Editor, and Viewer.
    - Support for Single Sign-On (SSO) with Office 365 / Azure AD.
- **Security:**
    - Unique API keys are generated for each router.
    - Passwords in command views are obfuscated with a "Reveal" feature for authorized users.
    - Automated creation of a managed admin user with a unique password on each router during adoption.
- **Auditing & Logging:**
    - A detailed, per-router command log provides a complete history of all executed commands.
    - A "Pending Commands" view shows which commands are queued for the next run.

## Requirements

- **Web Server:** A standard web server like Nginx or Apache.
- **PHP:** Version 7.4 or newer.
- **PHP Extensions:**
    - `pdo`: For database connectivity.
    - `pdo_pgsql` or `pdo_mysql`: Depending on your chosen database.
    - `ssh2`: For the push execution method.
- **Database:**
    - PostgreSQL 10+
    - MySQL 5.7+ / MariaDB 10.2+

## Installation

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/your-repo/mikrotik-manager.git
    cd mikrotik-manager
    ```

2.  **Set up the Database:**
    - Choose the appropriate schema file for your database (`server/schema.sql` for PostgreSQL or `server/schema.mysql.sql` for MySQL/MariaDB).
    - Import the schema into your database. For example:
      ```bash
      # For PostgreSQL
      psql -U youruser -d yourdb -f server/schema.sql
      # For MySQL/MariaDB
      mysql -u youruser -p yourdb < server/schema.mysql.sql
      ```

3.  **Configure the Application:**
    - Copy the example configuration file:
      ```bash
      cp server/config.php.example server/config.php
      ```
    - Edit `server/config.php` with your database credentials, SSH credentials, server IP, and SSO provider details.

4.  **Set up Web Server:**
    - Configure your web server's document root to point to the `server/public` directory.
    - Ensure that the web server can write to the log files if you choose to enable them.

## Usage

1.  **Initial Login:**
    - You will need to manually create the first admin user and user roles in the database.
    - Insert the roles: `INSERT INTO user_roles (name) VALUES ('admin'), ('editor'), ('viewer');`
    - Create your first user and assign them the admin role ID.

2.  **Adopting a Router:**
    - A new router configured to call home (via a script) will appear in the "Pending Adoption" list on the `routers.php` page.
    - Assign a name to the router and click "Adopt".
    - The system will generate the necessary commands to set the router's name and create a managed admin user.

3.  **Applying Commands:**
    - Create new commands or templates via the `commands.php` or `templates.php` pages.
    - These will be automatically picked up and applied to the relevant routers based on their execution method (push or pull).
