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
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="products-list-home" style="margin:48px auto 0 auto;max-width:1100px;">
        <h2 style="text-align:center;color:#007bff;font-size:1.3rem;margin-bottom:18px;">Nos articles</h2>
        <?php
        require_once __DIR__.'/classes/Product.php';
        require_once __DIR__.'/classes/Category.php';
        $productObj = new Product();
        $categoryObj = new Category();
        $products = $productObj->getAll();
        $categories = $categoryObj->getAll();
        $catMap = [];
        foreach($categories as $c) $catMap[$c['id']] = $c['name'];
        if (empty($products)) {
            echo '<div style="text-align:center;color:#b30000;">Aucun article disponible.</div>';
        } else {
            echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">';
            foreach($products as $p) {
                echo '<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px #007bff11;padding:18px 12px;text-align:center;">';
                echo '<div style="font-weight:700;color:#007bff;font-size:1.08rem;">'.htmlspecialchars($p['name']).'</div>';
                echo '<div style="color:#888;font-size:0.98rem;margin:6px 0;">Catégorie : '.htmlspecialchars($catMap[$p['category_id']] ?? '').'</div>';
                echo '<div style="color:#23272f;font-size:1.1rem;font-weight:600;">'.number_format($p['price'],2,',',' ').' €</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
    <?php endif; ?>
</body>
</html>