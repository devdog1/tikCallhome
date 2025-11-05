<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function createUser($username, $password, $role_id) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash, role_id) VALUES (?, ?, ?)');
        return $stmt->execute([$username, $password_hash, $role_id]);
    }

    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }

    public function updateUser($id, $username, $role_id) {
        $stmt = $this->pdo->prepare('UPDATE users SET username = ?, role_id = ? WHERE id = ?');
        return $stmt->execute([$username, $role_id, $id]);
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getRole($user_id) {
        $stmt = $this->pdo->prepare('SELECT user_roles.name FROM users JOIN user_roles ON users.role_id = user_roles.id WHERE users.id = ?');
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function hasPermission($user_id, $permission) {
        $role = $this->getRole($user_id);
        $permissions = require 'permissions.php';
        return in_array($permission, $permissions[$role] ?? []);
    }
}
