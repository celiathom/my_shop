<?php
require_once 'Database.php';

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT id, username, email, admin FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($username, $email, $password, $admin) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, admin) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $hash, $admin]);
    }

    public function update($id, $username, $email, $admin, $password = null) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, admin = ? WHERE id = ?");
            return $stmt->execute([$username, $email, $hash, $admin, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET username = ?, email = ?, admin = ? WHERE id = ?");
            return $stmt->execute([$username, $email, $admin, $id]);
        }
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        return $stmt->fetch() ? true : false;
    }
}
