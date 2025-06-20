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
        .home-title {
            font-size: 2.2rem;
            margin-bottom: 10px;
            color: #007BFF;
        }
        .home-welcome {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        .home-subtitle {
            color: #555;
            margin-bottom: 30px;
        }
        .home-btn {
            display: inline-block;
            margin: 10px 12px 0 12px;
            padding: 12px 32px;
            font-size: 1.1rem;
            border-radius: 8px;
            border: none;
            background: #007BFF;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
        }
        .home-btn:hover {
            background: #0056b3;
        }
        .admin-link {
            display: block;
            margin-top: 24px;
            color: #007BFF;
            font-weight: bold;
            text-decoration: none;
        }
        .admin-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="home-center">
    <div class="home-box">
        <div class="home-welcome">Bienvenue sur MY-SHOP</div>
        <div class="home-title">Votre boutique en ligne, simple, rapide et 100% sécurisée</div>
        <div class="home-subtitle">Accéder au site !</div>
        <a href="signin.php" class="home-btn">Se connecter</a>
        <a href="signup.php" class="home-btn">Créer un compte</a>
    </div>
</body>
</html>