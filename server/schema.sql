CREATE TABLE routers (
    id SERIAL PRIMARY KEY,
    serial_number VARCHAR(255) UNIQUE NOT NULL,
    model VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    last_seen TIMESTAMP NOT NULL
);

CREATE TABLE commands (
    id SERIAL PRIMARY KEY,
    command TEXT NOT NULL,
    check_command TEXT,
    description TEXT,
    type VARCHAR(50) NOT NULL, -- generic, model, serial
    target VARCHAR(255) -- model name or serial number
);

CREATE TABLE router_commands (
    id SERIAL PRIMARY KEY,
    router_id INTEGER REFERENCES routers(id),
    command_id INTEGER REFERENCES commands(id),
    executed_at TIMESTAMP
);
