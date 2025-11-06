-- Step 1: Create the 'Super Admin' role
INSERT INTO user_roles (name) VALUES ('Super Admin');

-- Step 2: Grant all existing permissions to the 'Super Admin' role
-- This assumes the permissions table is already populated with the desired permissions.
INSERT INTO role_permissions (role_id, permission_id)
SELECT ur.id, p.id
FROM user_roles ur, permissions p
WHERE ur.name = 'Super Admin';

-- Step 3: Create the local_admin user and assign the 'Super Admin' role
INSERT INTO users (username, password_hash, role_id)
SELECT 'local_admin', '$2y$10$w2k.mA9.g/5L5.U2.E2.C.e/n.cE.B.f.G.f.H.i.J.k.L.m.N.o.P', ur.id
FROM user_roles ur
WHERE ur.name = 'Super Admin';
