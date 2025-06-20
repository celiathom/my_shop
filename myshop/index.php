<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - MY SHOP</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .home-center {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #f0f2f5;
        }
        .home-box {
            background: #fff;
            padding: 40px 32px;
            border-radius: 16px;
            box-shadow: 0 0 24px rgba(0,0,0,0.08);
            text-align: center;
            min-width: 320px;
        }
        .home-welcome {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        .home-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #666;
        }
        .home-subtitle {
            font-size: 1rem;
            margin-bottom: 20px;
            color: #999;
        }
        .home-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            transition: background-color 0.3s;
        }
        .home-btn:hover {
            background-color: #0056b3;
        }
        .logout-topright {
            position: absolute;
            top: 32px;
            right: 48px;
            z-index: 10;
        }
        @media (max-width: 600px) {
            .logout-topright {
                top: 12px;
                right: 12px;
            }
        }
    </style>
</head>
<body class="home-center">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php" class="home-btn logout-topright">Déconnexion</a>
    <?php endif; ?>
    <div class="home-box">
        <div class="home-welcome">Bienvenue sur MY-SHOP</div>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="home-title">Votre boutique en ligne, simple, rapide et 100% sécurisée</div>
            <div class="home-subtitle">Accéder au site !</div>
            <a href="signin.php" class="home-btn">Se connecter</a>
            <a href="signup.php" class="home-btn">Créer un compte</a>
        <?php endif; ?>
    </div>
</body>
</html>