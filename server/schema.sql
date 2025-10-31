CREATE TABLE config_templates (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    content TEXT NOT NULL,
    workflow_type VARCHAR(20) DEFAULT 'incremental' -- 'incremental' or 'complete'
);

CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    base_template_id INTEGER REFERENCES config_templates(id)
);

CREATE TABLE wifi_configs (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    ssid VARCHAR(255) NOT NULL,
    security_protocol VARCHAR(50),
    password TEXT
);

CREATE TABLE routers (
    id SERIAL PRIMARY KEY,
    serial_number VARCHAR(255) UNIQUE NOT NULL,
    model VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    last_seen TIMESTAMP NOT NULL,
    group_id INTEGER REFERENCES groups(id),
    adopted BOOLEAN DEFAULT false,
    execution_method VARCHAR(4) DEFAULT 'push', -- 'push' or 'pull'
    api_key VARCHAR(255) UNIQUE
);

CREATE TABLE commands (
    id SERIAL PRIMARY KEY,
    command TEXT NOT NULL,
    check_command TEXT,
    description TEXT,
    type VARCHAR(50) NOT NULL, -- generic, model, serial, or group
    target VARCHAR(255) -- model name, serial number, or group id
);

CREATE TABLE router_commands (
    id SERIAL PRIMARY KEY,
    router_id INTEGER REFERENCES routers(id),
    command_id INTEGER REFERENCES commands(id),
    executed_at TIMESTAMP,
    status VARCHAR(20), -- e.g., 'success', 'failure', 'delivered'
    output TEXT
);
