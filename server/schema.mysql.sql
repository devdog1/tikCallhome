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
    serial_number VARCHAR(255) UNIQUE NOT NULL,
    model VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    last_seen DATETIME NOT NULL,
    group_id INT,
    adopted BOOLEAN DEFAULT false,
    execution_method VARCHAR(4) DEFAULT 'push', -- 'push' or 'pull'
    api_key VARCHAR(255) UNIQUE,
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
