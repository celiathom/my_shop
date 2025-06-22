<?php
require_once __DIR__.'/classes/Category.php';
$catObj = new Category();
$categories = $catObj->getAll();
header('Content-Type: application/json');
echo json_encode($categories);
