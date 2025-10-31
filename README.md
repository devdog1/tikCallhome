# Mikrotik Central Management System

This system provides a way to centrally manage Mikrotik routers. It allows routers to "call home" to a central server, which can then execute commands on the routers via SSH.

## Features

-   **Centralized Router Database:** Routers register themselves with the server, providing their serial number, model, and IP address.
-   **Command Management:** A web-based interface allows you to define commands that can be executed on routers.
-   **Targeted Commands:** Commands can be targeted to all routers (generic), routers of a specific model, or a single router by its serial number.
-   **Idempotent Command Execution:** The system can check if a configuration is already in place before executing a command, preventing duplicate or erroneous configurations.
-   **Flexible SSH Credentials:** The system can try multiple username/password combinations when connecting to routers.
-   **Automatic Admin Password Setup:** When a new router checks in for the first time, the system automatically creates a command to set its admin password.

## How to Use

1.  **Set up the server:**
    *   Deploy the files in the `server` directory to a web server with PHP and PostgreSQL.
    *   Import the `server/schema.sql` file into your PostgreSQL database.
    *   Rename `server/config.php.example` to `server/config.php` and edit it to set your database and SSH credentials.
    *   Set up a cron job to run `server/ssh_executor.php` periodically.

2.  **Configure your Mikrotik routers:**
    *   Edit the `mikrotik/call_home.rsc` script and set the `$serverUrl` and `$serverAddress` variables to point to your server.
    *   Upload the script to your routers and schedule it to run periodically.

3.  **Manage commands:**
    *   Access the admin interface at `http://your-server.com/public/admin/`.
    *   Here you can add, view, and manage commands.

## Example Commands

Here are some example commands that you can add through the admin interface.

### Change WiFi SSID and Password

-   **Description:** `Change the WiFi SSID and password for the main wireless interface.`
-   **Command:** `/interface wireless set [find default-name=wlan1] ssid="YourNewSSID" psk="YourNewPassword"`
-   **Check Command:** `/interface wireless get [find default-name=wlan1] ssid` (This will return the current SSID. The main command will run if this is different from your new SSID. For a more robust check, you might need a more complex script on the router.)

### Change the Admin Password

-   **Description:** `Change the password for the 'admin' user.`
-   **Command:** `/user set [find name=admin] password="a_new_strong_password"`
-   **Check Command:** This is tricky to check without storing the password. The initial password setup is handled automatically. For subsequent changes, you can assume the command needs to be run.

### Add a Firewall Rule

-   **Description:** `Block access to a specific IP address.`
-   **Command:** `/ip firewall filter add action=drop chain=forward dst-address=1.2.3.4`
-   **Check Command:** `/ip firewall filter print where dst-address=1.2.3.4` (This will return the firewall rule if it exists.)
