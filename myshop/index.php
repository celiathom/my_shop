<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - MY SHOP</title>
    <meta name="description" content="Découvrez et achetez les meilleurs produits sur MY SHOP, votre boutique en ligne moderne, rapide et sécurisée.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .filters-form {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            background: #f8f9fa;
            padding: 18px 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 28px;
            justify-content: center;
        }
        .filters-form input, .filters-form select {
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            min-width: 120px;
        }
        .filters-form button {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s;
        }
        .filters-form button:hover {
            background: #0056b3;
        }
        @media (max-width: 700px) {
            .filters-form { flex-direction: column; align-items: stretch; }
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
        $categories = $categoryObj->getAll();
        $catMap = [];
        foreach($categories as $c) $catMap[$c['id']] = $c['name'];
        // --- Filtres ---
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $cat = isset($_GET['cat']) ? $_GET['cat'] : '';
        $min = isset($_GET['min']) ? floatval($_GET['min']) : '';
        $max = isset($_GET['max']) ? floatval($_GET['max']) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        // Formulaire de filtres
        echo '<form method="get" class="filters-form">';
        echo '<input type="text" name="search" placeholder="Recherche..." value="'.htmlspecialchars($search).'" title="Nom ou description">';
        echo '<select name="cat"><option value="">Catégorie</option>';
        foreach($categories as $c) {
            $sel = ($cat == $c['id']) ? 'selected' : '';
            echo '<option value="'.$c['id'].'" '.$sel.'>'.htmlspecialchars($c['name']).'</option>';
        }
        echo '</select>';
        echo '<input type="number" name="min" min="0" step="0.01" placeholder="Prix min" value="'.htmlspecialchars($min).'">';
        echo '<input type="number" name="max" min="0" step="0.01" placeholder="Prix max" value="'.htmlspecialchars($max).'">';
        echo '<select name="sort">';
        echo '<option value="">Tri</option>';
        echo '<option value="az"'.($sort=="az"?' selected':'').'>Nom A-Z</option>';
        echo '<option value="za"'.($sort=="za"?' selected':'').'>Nom Z-A</option>';
        echo '<option value="pricelow"'.($sort=="pricelow"?' selected':'').'>Prix croissant</option>';
        echo '<option value="pricehigh"'.($sort=="pricehigh"?' selected':'').'>Prix décroissant</option>';
        echo '</select>';
        echo '<button type="submit">Filtrer</button>';
        echo '</form>';
        // --- Récupération et filtrage des produits ---
        $products = $productObj->getAll();
        // Filtrage PHP (nom, description, catégorie, prix)
        $filtered = array_filter($products, function($p) use ($search, $cat, $min, $max) {
            $ok = true;
            if ($search) {
                $haystack = strtolower($p['name'].(isset($p['description'])?$p['description']:''));
                $ok = $ok && (strpos($haystack, strtolower($search)) !== false);
            }
            if ($cat) {
                $ok = $ok && ($p['category_id'] == $cat);
            }
            if ($min !== '' && is_numeric($min)) {
                $ok = $ok && ($p['price'] >= $min);
            }
            if ($max !== '' && is_numeric($max)) {
                $ok = $ok && ($p['price'] <= $max);
            }
            return $ok;
        });
        // Tri
        if ($sort === 'az') {
            usort($filtered, function($a, $b) { return strcmp($a['name'], $b['name']); });
        } elseif ($sort === 'za') {
            usort($filtered, function($a, $b) { return strcmp($b['name'], $a['name']); });
        } elseif ($sort === 'pricelow') {
            usort($filtered, function($a, $b) { return $a['price'] <=> $b['price']; });
        } elseif ($sort === 'pricehigh') {
            usort($filtered, function($a, $b) { return $b['price'] <=> $a['price']; });
        }
        if (empty($filtered)) {
            echo '<div style="text-align:center;color:#b30000;">Aucun article ne correspond à vos critères.</div>';
        } else {
            echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">';
            foreach($filtered as $p) {
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