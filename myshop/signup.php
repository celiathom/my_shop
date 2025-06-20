<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Connexion à la base de données avec le bon utilisateur
$pdo = new PDO('mysql:host=127.0.0.1;dbname=my_shop;charset=utf8', 'myshop_user', 'password');

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm'];

        // Vérification des champs
        if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
            $errors[] = "Tous les champs sont obligatoires.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }

        if ($password !== $confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        // Vérifie si l'email existe déjà
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé.";
            }
        }

        // Si tout est ok, insérer l'utilisateur
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, admin) VALUES (?, ?, ?, 0)");
            $stmt->execute([$username, $email, $hashedPassword]);

            // Redirige vers la page de connexion
            header("Location: signin.php");
            exit();
        }

    } catch (PDOException $e) {
        $errors[] = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - MY SHOP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <h1>Créer un compte</h1>
        <?php
        if (!empty($errors)) {
            echo '<div class="error-box">';
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
            echo '</div>';
        }
        ?>
        <form action="signup.php" method="POST" class="auth-form">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" placeholder="Entrez votre nom" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Entrez votre email" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Entrez un mot de passe" required>

            <label for="confirm">Confirmer le mot de passe</label>
            <input type="password" id="confirm" name="confirm" placeholder="Confirmez le mot de passe" required>

            <button type="submit" class="btn-primary">S'inscrire</button>
            <p class="auth-link">Déjà inscrit ? <a href="signin.php">Se connecter</a></p>
        </form>
    </div>
</body>
</html>
