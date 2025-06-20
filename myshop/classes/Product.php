<?php
require_once 'Database.php';

class Product {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT products.*, categories.name AS cat_name FROM products LEFT JOIN categories ON products.category_id = categories.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($name, $price, $category_id) {
        $stmt = $this->pdo->prepare("INSERT INTO products (name, price, category_id) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $price, $category_id]);
    }

    public function update($id, $name, $price, $category_id) {
        $stmt = $this->pdo->prepare("UPDATE products SET name = ?, price = ?, category_id = ? WHERE id = ?");
        return $stmt->execute([$name, $price, $category_id, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
