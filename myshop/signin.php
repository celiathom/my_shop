<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// ✔️ Connexion BDD
$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=my_shop;charset=utf8',
    'myshop_user',
    'password'
);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $errors[] = "Tous les champs sont requis.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['admin'] ?? 0;
                if ($user['admin'] == 1) {
                    header("Location: admin.php?page=categories");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $errors[] = "Email ou mot de passe incorrect.";
            }
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur : " . $e->getMessage();
    }
}
?>

<!-- ✔️ ICI LE HTML ET LE CSS -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - MY SHOP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <h1>Se connecter</h1>
        <?php
        if (!empty($errors)) {
            echo '<div class="error-box">';
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
            echo '</div>';
        }
        ?>
        <form method="post" class="auth-form">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Entrez votre email" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>

            <button type="submit" class="btn-primary">Se connecter</button>
            <p class="auth-link">Pas encore de compte ? <a href="signup.php">S'inscrire</a></p>
        </form>
    </div>
</body>
</html>