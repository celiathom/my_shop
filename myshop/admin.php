<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
// Redirige automatiquement vers la page de connexion ou d'inscription si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}
$errors = [];
// Vérifie si l'utilisateur est admin
$pdo = new PDO('mysql:host=127.0.0.1;dbname=my_shop;charset=utf8', 'myshop_user', 'password');
$stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['admin'] != 1) {
    // Accès refusé si pas admin
    echo "Accès refusé. Cette page est réservée aux administrateurs.";
    exit();
}
if (isset($_GET['page']) && $_GET['page'] === 'categories') {
    echo '<div style="max-width:900px;margin:120px auto 0 auto;">';
    // --- AJOUT D'UNE CATÉGORIE ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_name'])) {
        $cat_name = trim($_POST['cat_name']);
        if (!empty($cat_name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$cat_name]);
            echo "<p style='color:green;'>Catégorie ajoutée !</p>";
        } else {
            echo "<p style='color:red;'>Le nom ne doit pas être vide.</p>";
        }
    }
    // --- MODIFICATION D'UNE CATÉGORIE ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'], $_POST['edit_name'])) {
        $edit_id = intval($_POST['edit_id']);
        $edit_name = trim($_POST['edit_name']);
        if (!empty($edit_name)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$edit_name, $edit_id]);
            header("Location: admin.php?page=categories");
            exit();
        } else {
            echo "<p style='color:red;'>Le nom ne doit pas être vide.</p>";
        }
    }
    // --- SUPPRESSION D'UNE CATÉGORIE ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: admin.php?page=categories");
        exit();
    }
    // --- AFFICHAGE DU FORMULAIRE ET DE LA LISTE ---
    echo '<h2 style="text-align:center;margin-bottom:24px;color:#007bff;font-size:2rem;">Catégories</h2>';
    echo '<form method="post" style="margin-bottom:24px;display:flex;gap:12px;align-items:flex-end;justify-content:center;background:#f8f9fa;padding:18px 24px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">';
    echo '<input type="text" name="cat_name" placeholder="Nom de la catégorie" required style="padding:10px 14px;border-radius:6px;border:1px solid #ccc;"> ';
    echo '<button type="submit" style="background:#007bff;color:#fff;padding:10px 24px;border:none;border-radius:6px;font-weight:600;cursor:pointer;">Ajouter</button>';
    echo '</form>';
    $stmt = $pdo->query("SELECT * FROM categories");
    echo "<table class='admin-table'>";
    echo "<tr>"
        ."<th class='center'>ID</th>"
        ."<th>Nom</th>"
        ."<th class='center'>Actions</th>"
        ."</tr>";
    foreach ($stmt as $cat) {
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == $cat['id']) {
            echo "<tr>"
                ."<td class='center'>{$cat['id']}</td>"
                ."<td>"
                ."<form method='post' style='display:flex;gap:10px;align-items:center;'>"
                ."<input type='hidden' name='edit_id' value='{$cat['id']}'>"
                ."<input type='text' name='edit_name' value='".htmlspecialchars($cat['name'], ENT_QUOTES)."' required style='padding:8px 12px;border-radius:6px;border:1px solid #ccc;'> "
                ."<button type='submit' class='admin-btn save'>Enregistrer</button>"
                ."</form>"
                ."</td>"
                ."<td class='center'></td>"
                ."</tr>";
        } else {
            echo "<tr>"
                ."<td class='center' style=''>{$cat['id']}</td>"
                ."<td>".htmlspecialchars($cat['name'])."</td>"
                ."<td class='center'>"
                ."<form method='post' style='display:inline;'>"
                ."<input type='hidden' name='edit_mode' value='{$cat['id']}'>"
                ."<button type='submit' class='admin-btn edit'>Modifier</button>"
                ."</form> "
                ."<form method='post' style='display:inline; margin-left:5px;'>"
                ."<input type='hidden' name='delete_id' value='{$cat['id']}'>"
                ."<button type='submit' onclick=\"return confirm('Supprimer cette catégorie ?');\" class='admin-btn delete'>Supprimer</button>"
                ."</form>"
                ."</td>"
                ."</tr>";
        }
    }
    echo "</table>";
    echo '</div>';
} else if (isset($_GET['page']) && $_GET['page'] === 'users') {
    echo '<div style="max-width:900px;margin:120px auto 0 auto;">';
    // --- SUPPRESSION D'UN UTILISATEUR ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['user_action']) && $_POST['user_action'] === 'delete' &&
        isset($_POST['delete_id'])
    ) {
        $delete_id = intval($_POST['delete_id']);
        // On évite de supprimer son propre compte admin
        if ($delete_id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            echo "<p style='color:green;'>Utilisateur supprimé !</p>";
        } else {
            echo "<p style='color:red;'>Vous ne pouvez pas supprimer votre propre compte.</p>";
        }
    }
    // --- CHANGEMENT DE STATUT ADMIN ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['user_action']) && $_POST['user_action'] === 'toggle_admin' &&
        isset($_POST['toggle_id'], $_POST['current_admin'])
    ) {
        $toggle_id = intval($_POST['toggle_id']);
        $current_admin = intval($_POST['current_admin']);
        $new_admin = $current_admin ? 0 : 1;
        // On évite de retirer son propre statut admin
        if ($toggle_id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("UPDATE users SET admin = ? WHERE id = ?");
            $stmt->execute([$new_admin, $toggle_id]);
            echo "<p style='color:green;'>Statut admin modifié !</p>";
        } else {
            echo "<p style='color:red;'>Vous ne pouvez pas modifier votre propre statut admin.</p>";
        }
    }
    // --- AJOUT D'UN UTILISATEUR PAR L'ADMIN ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['user_action']) && $_POST['user_action'] === 'add_user' &&
        isset($_POST['new_username'], $_POST['new_email'], $_POST['new_password'])
    ) {
        $new_username = trim($_POST['new_username']);
        $new_email = trim($_POST['new_email']);
        $new_password = $_POST['new_password'];
        $new_admin = isset($_POST['new_admin']) ? 1 : 0;
        if (!empty($new_username) && !empty($new_email) && !empty($new_password)) {
            // Vérifie si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$new_email]);
            if ($stmt->fetch()) {
                echo "<p style='color:red;'>Cet email est déjà utilisé.</p>";
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, admin) VALUES (?, ?, ?, ?)");
                $stmt->execute([$new_username, $new_email, $hash, $new_admin]);
                echo "<p style='color:green;'>Nouvel utilisateur ajouté !</p>";
            }
        } else {
            echo "<p style='color:red;'>Tous les champs sont obligatoires.</p>";
        }
    }
    // --- MODIFICATION D'UN UTILISATEUR PAR L'ADMIN ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['user_action']) && $_POST['user_action'] === 'edit_user' &&
        isset($_POST['edit_id'], $_POST['edit_username'], $_POST['edit_email'])
    ) {
        $edit_id = intval($_POST['edit_id']);
        $edit_username = trim($_POST['edit_username']);
        $edit_email = trim($_POST['edit_email']);
        $edit_admin = isset($_POST['edit_admin']) ? 1 : 0;
        $edit_password = isset($_POST['edit_password']) && $_POST['edit_password'] !== '' ? $_POST['edit_password'] : null;
        // On évite de modifier son propre statut admin ici aussi
        if ($edit_id == $_SESSION['user_id'] && $edit_admin == 0) {
            echo "<p style='color:red;'>Vous ne pouvez pas retirer votre propre statut admin.</p>";
        } else if (!empty($edit_username) && !empty($edit_email)) {
            // Vérifie si l'email est déjà utilisé par un autre utilisateur
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$edit_email, $edit_id]);
            if ($stmt->fetch()) {
                echo "<p style='color:red;'>Cet email est déjà utilisé par un autre utilisateur.</p>";
            } else {
                if ($edit_password) {
                    $hash = password_hash($edit_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, admin = ? WHERE id = ?");
                    $stmt->execute([$edit_username, $edit_email, $hash, $edit_admin, $edit_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, admin = ? WHERE id = ?");
                    $stmt->execute([$edit_username, $edit_email, $edit_admin, $edit_id]);
                }
                echo "<p style='color:green;'>Utilisateur modifié !</p>";
            }
        } else {
            echo "<p style='color:red;'>Le nom et l'email sont obligatoires.</p>";
        }
    }
    // --- AFFICHAGE DES UTILISATEURS ---
    echo '<h2 style="text-align:center;margin-bottom:24px;color:#007bff;font-size:2rem;">Utilisateurs</h2>';
    echo '<form method="post" autocomplete="off" style="margin-bottom:24px;display:flex;gap:12px;align-items:flex-end;justify-content:center;background:#f8f9fa;padding:18px 24px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">';
    echo '<input type="hidden" name="user_action" value="add_user">';
    echo '<input type="text" name="new_username" placeholder="Nom d\'utilisateur" required autocomplete="off" style="padding:10px 14px;border-radius:6px;border:1px solid #ccc;"> ';
    echo '<input type="email" name="new_email" placeholder="Email" required autocomplete="off" style="padding:10px 14px;border-radius:6px;border:1px solid #ccc;"> ';
    echo '<input type="password" name="new_password" placeholder="Mot de passe" required autocomplete="new-password" style="padding:10px 14px;border-radius:6px;border:1px solid #ccc;"> ';
    echo '<label style="font-weight:500;"><input type="checkbox" name="new_admin"> Admin</label> ';
    echo '<button type="submit" style="background:#007bff;color:#fff;padding:10px 24px;border:none;border-radius:6px;font-weight:600;cursor:pointer;">Ajouter</button>';
    echo '</form>';
    $stmt = $pdo->query("SELECT id, username, email, admin FROM users");
    echo "<table class='admin-table' style='width: 100%; min-width: 700px;'>";
    echo "<tr>"
        ."<th class='center'>ID</th>"
        ."<th>Nom</th>"
        ."<th class='center'>Email</th>"
        ."<th class='center'>Admin</th>"
        ."<th class='center'>Actions</th>"
        ."</tr>";
    foreach ($stmt as $user) {
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == $user['id']) {
            // Formulaire de modification inline
            echo "<tr style='background:#f6faff;'>"
                ."<td>{$user['id']}</td>"
                ."<td colspan='4'>"
                ."<form method='post' style='display:flex;gap:10px;align-items:center;'>"
                ."<input type='hidden' name='user_action' value='edit_user'>"
                ."<input type='hidden' name='edit_id' value='{$user['id']}'>"
                ."<input type='text' name='edit_username' value='".htmlspecialchars($user['username'], ENT_QUOTES)."' required style='padding:8px 12px;border-radius:6px;border:1px solid #ccc;'> "
                ."<input type='email' name='edit_email' value='".htmlspecialchars($user['email'], ENT_QUOTES)."' required style='padding:8px 12px;border-radius:6px;border:1px solid #ccc;'> "
                ."<input type='password' name='edit_password' placeholder='Nouveau mot de passe (laisser vide pour ne pas changer)' style='padding:8px 12px;border-radius:6px;border:1px solid #ccc;'> "
                ."<label style='font-weight:500;'><input type='checkbox' name='edit_admin' ".($user['admin'] ? 'checked' : '')."> Admin</label> "
                ."<button type='submit' class='admin-btn save'>Enregistrer</button>"
                ."</form>"
                ."<form method='post' style='display:inline; margin-left:5px;'>"
                ."<button type='submit' name='cancel_edit' value='1' class='admin-btn cancel'>Annuler</button>"
                ."</form>"
                ."</td>"
                ."</tr>";
        } else {
            echo "<tr style='border-bottom:1px solid #e3e3e3;'>"
                ."<td class='center'>{$user['id']}</td>"
                ."<td>".htmlspecialchars($user['username'])."</td>"
                ."<td class='center'>".htmlspecialchars($user['email'])."</td>"
                ."<td class='center'>".($user['admin'] ? '<span style=\'color:#007bff;font-weight:700;\'>Oui</span>' : 'Non')."</td>"
                ."<td class='center' style='min-width:340px;'>"
                ."<div style='display:flex;justify-content:space-between;align-items:center;width:100%;gap:8px;'>"
                ."<div style='display:flex;gap:8px;'>"
                ."<form method='post' style='display:inline;'>"
                ."<input type='hidden' name='edit_mode' value='{$user['id']}'>"
                ."<button type='submit' class='admin-btn edit'>Modifier</button>"
                ."</form> "
                ."<form method='post' style='display:inline;'>"
                ."<input type='hidden' name='user_action' value='toggle_admin'>"
                ."<input type='hidden' name='toggle_id' value='{$user['id']}'>"
                ."<input type='hidden' name='current_admin' value='{$user['admin']}'>"
                ."<button type='submit' class='admin-btn admin'>".($user['admin'] ? 'Retirer admin' : 'Passer admin')."</button>"
                ."</form>"
                ."</div>"
                ."<form method='post' style='display:inline;'>"
                ."<input type='hidden' name='user_action' value='delete'>"
                ."<input type='hidden' name='delete_id' value='{$user['id']}'>"
                ."<button type='submit' onclick=\"return confirm('Supprimer cet utilisateur ?');\" class='admin-btn delete'>Supprimer</button>"
                ."</form>"
                ."</div>"
                ."</td>"
                ."</tr>";
        }
    }
    echo "</table>";
    echo '</div>';
} else if (isset($_GET['page']) && $_GET['page'] === 'products') {
    echo '<div style="max-width:900px;margin:120px auto 0 auto;">';
    // --- AJOUT D'UN PRODUIT ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['prod_action']) && $_POST['prod_action'] === 'add'
    ) {
        $prod_name = trim($_POST['prod_name']);
        $prod_price = intval($_POST['prod_price']);
        $prod_category = intval($_POST['prod_category']);
        if (!empty($prod_name) && $prod_price > 0 && $prod_category > 0) {
            $stmt = $pdo->prepare("INSERT INTO products (name, price, category_id) VALUES (?, ?, ?)");
            $stmt->execute([$prod_name, $prod_price, $prod_category]);
            echo "<p style='color:green;'>Produit ajouté !</p>";
        } else {
            echo "<p style='color:red;'>Tous les champs sont obligatoires et le prix doit être positif.</p>";
        }
    }

    // --- MODIFICATION D'UN PRODUIT ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['prod_action']) && $_POST['prod_action'] === 'edit' &&
        isset($_POST['edit_id'], $_POST['edit_name'], $_POST['edit_price'], $_POST['edit_category'])
    ) {
        $edit_id = intval($_POST['edit_id']);
        $edit_name = trim($_POST['edit_name']);
        $edit_price = intval($_POST['edit_price']);
        $edit_category = intval($_POST['edit_category']);
        if (!empty($edit_name) && $edit_price > 0 && $edit_category > 0) {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, category_id = ? WHERE id = ?");
            $stmt->execute([$edit_name, $edit_price, $edit_category, $edit_id]);
            header("Location: admin.php?page=products");
            exit();
        } else {
            echo "<p style='color:red;'>Tous les champs sont obligatoires et le prix doit être positif.</p>";
        }
    }

    // --- SUPPRESSION D'UN PRODUIT ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['prod_action']) && $_POST['prod_action'] === 'delete' &&
        isset($_POST['delete_id'])
    ) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: admin.php?page=products");
        exit();
    }

    // --- AFFICHAGE DES PRODUITS ---
    echo "<h2 style='text-align:center;margin-bottom:24px;color:#007bff;font-size:2rem;'>Produits</h2>";
    // Récupère les catégories pour le select
    $cat_stmt = $pdo->query("SELECT * FROM categories");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    // Formulaire d'ajout
    echo '<form method="post" style="margin-bottom:24px;display:flex;gap:12px;align-items:flex-end;justify-content:center;background:#f8f9fa;padding:18px 24px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">';
    echo '<input type="hidden" name="prod_action" value="add">';
    echo '<input type="text" name="prod_name" placeholder="Nom du produit" required style="padding:10px 14px;border-radius:6px;border:1px solid #ccc;"> ';
    echo '<input type="number" name="prod_price" placeholder="Prix" min="1" required style="padding:10px 14px;border-radius:6px;border:1px solid #ccc;"> ';
    echo '<select name="prod_category" required style="padding:10px 14px;border-radius:6px;border:1px solid #ccc;">';
    echo '<option value="">Catégorie</option>';
    foreach ($categories as $cat) {
        echo '<option value="'.$cat['id'].'">'.htmlspecialchars($cat['name']).'</option>';
    }
    echo '</select> ';
    echo '<button type="submit" style="background:#007bff;color:#fff;padding:10px 24px;border:none;border-radius:6px;font-weight:600;cursor:pointer;">Ajouter</button>';
    echo '</form>';
    // Affichage des produits
    $stmt = $pdo->query("SELECT products.*, categories.name AS cat_name FROM products LEFT JOIN categories ON products.category_id = categories.id");
    echo "<table class='admin-table'>";
    echo "<tr style='background:#e9f1fb;color:#007bff;font-size:1.1rem;'>"
        ."<th style='padding:12px 8px;text-align:center;'>ID</th>"
        ."<th style='padding:12px 32px 12px 32px;text-align:right;'>Nom</th>"
        ."<th style='padding:12px 48px 12px 48px;text-align:center;'>Prix</th>"
        ."<th style='padding:12px 32px 12px 32px;text-align:right;'>Catégorie</th>"
        ."<th style='padding:12px 8px;text-align:center;'>Actions</th>"
        ."</tr>";
    foreach ($stmt as $prod) {
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == $prod['id']) {
            echo "<tr style='background:#f6faff;'>"
                ."<td>{$prod['id']}</td>"
                ."<td colspan='4'>"
                ."<form method='post' style='display:flex;gap:10px;align-items:center;'>"
                ."<input type='hidden' name='prod_action' value='edit'>"
                ."<input type='hidden' name='edit_id' value='{$prod['id']}'>"
                ."<input type='text' name='edit_name' value='".htmlspecialchars($prod['name'], ENT_QUOTES)."' required style='padding:8px 12px;border-radius:6px;border:1px solid #ccc;text-align:right;'> "
                ."<input type='number' name='edit_price' value='{$prod['price']}' min='1' required style='padding:8px 48px;border-radius:6px;border:1px solid #ccc;text-align:center;width:90px;'> "
                ."<select name='edit_category' required style='padding:8px 12px;border-radius:6px;border:1px solid #ccc;'>";
            foreach ($categories as $cat) {
                $selected = ($cat['id'] == $prod['category_id']) ? 'selected' : '';
                echo "<option value='{$cat['id']}' $selected>".htmlspecialchars($cat['name'])."</option>";
            }
            echo    "</select>"
                ."<button type='submit' class='admin-btn save'>Enregistrer</button>"
                ."</form>"
                ."</td>"
                ."</tr>";
        } else {
            echo "<tr style='border-bottom:1px solid #e3e3e3;'>"
                ."<td style='padding:10px 8px;text-align:center;'>{$prod['id']}</td>"
                ."<td style='padding:10px 32px 10px 32px;text-align:right;'>".htmlspecialchars($prod['name'])."</td>"
                ."<td style='padding:10px 48px 10px 48px;text-align:center;width:90px;'>".htmlspecialchars($prod['price'])."</td>"
                ."<td style='padding:10px 32px 10px 32px;text-align:right;'>".htmlspecialchars($prod['cat_name'])."</td>"
                ."<td style='padding:10px 8px;text-align:center;'>"
                ."<form method='post' style='display:inline;'>"
                ."<input type='hidden' name='edit_mode' value='{$prod['id']}'>"
                ."<button type='submit' class='admin-btn edit'>Modifier</button>"
                ."</form> "
                ."<form method='post' style='display:inline; margin-left:5px;'>"
                ."<input type='hidden' name='prod_action' value='delete'>"
                ."<input type='hidden' name='delete_id' value='{$prod['id']}'>"
                ."<button type='submit' onclick=\"return confirm('Supprimer ce produit ?');\" class='admin-btn delete'>Supprimer</button>"
                ."</form>"
                ."</td>"
                ."</tr>";
        }
    }
    echo "</table>";
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - MY SHOP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="margin:0;padding:0;">
    <nav style="position:fixed;top:0;left:0;width:100vw;background:#f8f9fa;box-shadow:0 2px 12px rgba(0,0,0,0.04);display:flex;align-items:center;justify-content:center;min-height:60px;z-index:100;">
        <?php
        $current = isset($_GET['page']) ? $_GET['page'] : '';
        ?>
        <ul style="list-style:none;padding:0;margin:0;display:flex;gap:32px;">
            <li><a href="admin.php?page=users" style="display:block;padding:16px 24px;color:#007bff;font-weight:600;text-decoration:none;border-radius:8px;transition:background 0.18s;<?php if($current==='users'){echo 'background:#e9f1fb;box-shadow:0 2px 8px #007bff22;';} ?>">Utilisateurs</a></li>
            <li><a href="admin.php?page=products" style="display:block;padding:16px 24px;color:#007bff;font-weight:600;text-decoration:none;border-radius:8px;transition:background 0.18s;<?php if($current==='products'){echo 'background:#e9f1fb;box-shadow:0 2px 8px #007bff22;';} ?>">Produits</a></li>
            <li><a href="admin.php?page=categories" style="display:block;padding:16px 24px;color:#007bff;font-weight:600;text-decoration:none;border-radius:8px;transition:background 0.18s;<?php if($current==='categories'){echo 'background:#e9f1fb;box-shadow:0 2px 8px #007bff22;';} ?>">Catégories</a></li>
            <li><a href="index.php" style="display:block;padding:16px 24px;color:#23272f;font-weight:700;text-decoration:none;border-radius:8px;">Retour à l'accueil</a></li>
            <li><a href="logout.php" style="display:block;padding:16px 24px;color:#dc3545;font-weight:600;text-decoration:none;border-radius:8px;">Déconnexion</a></li>
        </ul>
    </nav>
    <div style="max-width:1100px;margin:120px auto 0 auto;padding:0 32px;">
        <?php
        // Suppression de l'affichage 'Gestion des catégories'
        ?>
    </div>
</body>
</html>