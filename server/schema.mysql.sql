CREATE TABLE config_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    content TEXT NOT NULL,
    workflow_type VARCHAR(20) DEFAULT 'incremental' -- 'incremental' or 'complete'
);

CREATE TABLE groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    base_template_id INT,
    FOREIGN KEY (base_template_id) REFERENCES config_templates(id)
);

CREATE TABLE wifi_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    ssid VARCHAR(255) NOT NULL,
    security_protocol VARCHAR(50),
    password TEXT
);

CREATE TABLE routers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    serial_number VARCHAR(255) UNIQUE NOT NULL,
    model VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    last_seen DATETIME NOT NULL,
    group_id INT,
    adopted BOOLEAN DEFAULT false,
    execution_method VARCHAR(4) DEFAULT 'push', -- 'push' or 'pull'
    api_key VARCHAR(255) UNIQUE,
    local_admin_password VARCHAR(255),
    initial_pull_complete BOOLEAN DEFAULT false,
    FOREIGN KEY (group_id) REFERENCES groups(id)
);

CREATE TABLE commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    command TEXT NOT NULL,
    check_command TEXT,
    description TEXT,
    type VARCHAR(50) NOT NULL, -- generic, model, serial, or group
    target VARCHAR(255) -- model name, serial number, or group id
);

CREATE TABLE router_commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    router_id INT,
    command_id INT,
    executed_at DATETIME,
    status VARCHAR(20), -- e.g., 'success', 'failure', 'delivered'
    output TEXT,
    FOREIGN KEY (router_id) REFERENCES routers(id),
    FOREIGN KEY (command_id) REFERENCES commands(id)
);

CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT,
    sso_provider VARCHAR(50),
    sso_id VARCHAR(255),
    FOREIGN KEY (role_id) REFERENCES user_roles(id)
);

CREATE TABLE command_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    router_id INT,
    command TEXT NOT NULL,
    executed_at DATETIME NOT NULL,
    status VARCHAR(20) NOT NULL,
    output TEXT,
    user_id INT,
    FOREIGN KEY (router_id) REFERENCES routers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(255) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES user_roles(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
);

-- Default Permissions
INSERT INTO permissions (permission_key, description) VALUES
('manage_users', 'Allows creating, editing, and deleting users and their permissions.'),
('view_routers', 'Allows viewing the list of routers and their details.'),
('manage_routers', 'Allows adopting, editing, and deleting routers.'),
('view_commands', 'Allows viewing commands, logs, and pending commands.'),
('manage_commands', 'Allows creating, editing, and deleting commands and templates.');
