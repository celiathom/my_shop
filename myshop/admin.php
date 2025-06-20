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
    echo '<h2>Catégories</h2>';
    echo '<form method="post" style="margin-bottom:20px;">';
    echo '<input type="text" name="cat_name" placeholder="Nom de la catégorie" required> ';
    echo '<button type="submit">Ajouter</button>';
    echo '</form>';
    $stmt = $pdo->query("SELECT * FROM categories");
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nom</th><th>Actions</th></tr>";
    foreach ($stmt as $cat) {
        // Formulaire de modification inline
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == $cat['id']) {
            echo "<tr>
                <td>{$cat['id']}</td>
                <td>
                    <form method='post' style='display:inline;'>
                        <input type='hidden' name='edit_id' value='{$cat['id']}'>
                        <input type='text' name='edit_name' value='".htmlspecialchars($cat['name'], ENT_QUOTES)."' required>
                        <button type='submit'>Enregistrer</button>
                    </form>
                </td>
                <td></td>
            </tr>";
        } else {
            echo "<tr>
                <td>{$cat['id']}</td>
                <td>{$cat['name']}</td>
                <td>
                    <form method='post' style='display:inline;'>
                        <input type='hidden' name='edit_mode' value='{$cat['id']}'>
                        <button type='submit'>Modifier</button>
                    </form>
                    <form method='post' style='display:inline; margin-left:5px;'>
                        <input type='hidden' name='delete_id' value='{$cat['id']}'>
                        <button type='submit' onclick=\"return confirm('Supprimer cette catégorie ?');\">Supprimer</button>
                    </form>
                </td>
            </tr>";
        }
    }
    echo "</table>";
} else if (isset($_GET['page']) && $_GET['page'] === 'products') {
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
    echo "<h2>Produits</h2>";
    // Récupère les catégories pour le select
    $cat_stmt = $pdo->query("SELECT * FROM categories");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formulaire d'ajout
    echo '<form method="post" style="margin-bottom:20px;">';
    echo '<input type="hidden" name="prod_action" value="add">';
    echo '<input type="text" name="prod_name" placeholder="Nom du produit" required> ';
    echo '<input type="number" name="prod_price" placeholder="Prix" min="1" required> ';
    echo '<select name="prod_category" required><option value="">Catégorie</option>';
    foreach ($categories as $cat) {
        echo '<option value="'.$cat['id'].'">'.htmlspecialchars($cat['name']).'</option>';
    }
    echo '</select> ';
    echo '<button type="submit">Ajouter</button>';
    echo '</form>';

    // Affichage des produits
    $stmt = $pdo->query("SELECT products.*, categories.name AS cat_name FROM products LEFT JOIN categories ON products.category_id = categories.id");
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nom</th><th>Prix</th><th>Catégorie</th><th>Actions</th></tr>";
    foreach ($stmt as $prod) {
        // Formulaire de modification inline
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == $prod['id']) {
            echo "<tr>
                <td>{$prod['id']}</td>
                <td colspan='4'>
                    <form method='post' style='display:inline;'>
                        <input type='hidden' name='prod_action' value='edit'>
                        <input type='hidden' name='edit_id' value='{$prod['id']}'>
                        <input type='text' name='edit_name' value='".htmlspecialchars($prod['name'], ENT_QUOTES)."' required>
                        <input type='number' name='edit_price' value='{$prod['price']}' min='1' required>
                        <select name='edit_category' required>";
            foreach ($categories as $cat) {
                $selected = ($cat['id'] == $prod['category_id']) ? 'selected' : '';
                echo "<option value='{$cat['id']}' $selected>".htmlspecialchars($cat['name'])."</option>";
            }
            echo    "</select>
                        <button type='submit'>Enregistrer</button>
                    </form>
                </td>
            </tr>";
        } else {
            echo "<tr>
                <td>{$prod['id']}</td>
                <td>{$prod['name']}</td>
                <td>{$prod['price']}</td>
                <td>{$prod['cat_name']}</td>
                <td>
                    <form method='post' style='display:inline;'>
                        <input type='hidden' name='edit_mode' value='{$prod['id']}'>
                        <button type='submit'>Modifier</button>
                    </form>
                    <form method='post' style='display:inline; margin-left:5px;'>
                        <input type='hidden' name='prod_action' value='delete'>
                        <input type='hidden' name='delete_id' value='{$prod['id']}'>
                        <button type='submit' onclick=\"return confirm('Supprimer ce produit ?');\">Supprimer</button>
                    </form>
                </td>
            </tr>";
        }
    }
    echo "</table>";
} else if (isset($_GET['page']) && $_GET['page'] === 'users') {
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
    echo '<h2>Utilisateurs</h2>';
    // Formulaire d'ajout d'utilisateur (toujours vide, sans autocomplétion sur chaque champ)
    echo '<form method="post" autocomplete="off" style="margin-bottom:20px;">';
    echo '<input type="hidden" name="user_action" value="add_user">';
    echo '<input type="text" name="new_username" placeholder="Nom d\'utilisateur" required autocomplete="off"> ';
    echo '<input type="email" name="new_email" placeholder="Email" required autocomplete="off"> ';
    echo '<input type="password" name="new_password" placeholder="Mot de passe" required autocomplete="new-password"> ';
    echo '<label><input type="checkbox" name="new_admin"> Admin</label> ';
    echo '<button type="submit">Ajouter</button>';
    echo '</form>';
    $stmt = $pdo->query("SELECT id, username, email, admin FROM users");
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nom</th><th>Email</th><th>Admin</th><th>Actions</th></tr>";
    foreach ($stmt as $user) {
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == $user['id']) {
            // Formulaire de modification inline
            echo "<tr><td>{$user['id']}</td><td colspan='4'>
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='user_action' value='edit_user'>
                    <input type='hidden' name='edit_id' value='{$user['id']}'>
                    <input type='text' name='edit_username' value='".htmlspecialchars($user['username'], ENT_QUOTES)."' required> 
                    <input type='email' name='edit_email' value='".htmlspecialchars($user['email'], ENT_QUOTES)."' required> 
                    <input type='password' name='edit_password' placeholder='Nouveau mot de passe (laisser vide pour ne pas changer)'> 
                    <label><input type='checkbox' name='edit_admin' ".($user['admin'] ? 'checked' : '')."> Admin</label> 
                    <button type='submit'>Enregistrer</button>
                </form>
                <form method='post' style='display:inline; margin-left:5px;'>
                    <button type='submit' name='cancel_edit' value='1'>Annuler</button>
                </form>
            </td></tr>";
        } else {
            echo "<tr>
                <td>{$user['id']}</td>
                <td>".htmlspecialchars($user['username'])."</td>
                <td>".htmlspecialchars($user['email'])."</td>
                <td>".($user['admin'] ? 'Oui' : 'Non')."</td>
                <td>
                    <form method='post' style='display:inline;'>
                        <input type='hidden' name='edit_mode' value='{$user['id']}'>
                        <button type='submit'>Modifier</button>
                    </form>
                    <form method='post' style='display:inline; margin-left:5px;'>
                        <input type='hidden' name='user_action' value='toggle_admin'>
                        <input type='hidden' name='toggle_id' value='{$user['id']}'>
                        <input type='hidden' name='current_admin' value='{$user['admin']}'>
                        <button type='submit'>".($user['admin'] ? 'Retirer admin' : 'Passer admin')."</button>
                    </form>
                    <form method='post' style='display:inline; margin-left:5px;'>
                        <input type='hidden' name='user_action' value='delete'>
                        <input type='hidden' name='delete_id' value='{$user['id']}'>
                        <button type='submit' onclick=\"return confirm('Supprimer cet utilisateur ?');\">Supprimer</button>
                    </form>
                </td>
            </tr>";
        }
    }
    echo "</table>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - MY SHOP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="admin-center">
        <h1>Administration MY SHOP</h1>
        <nav>
            <ul>
                <li><a href="admin.php?page=users">Utilisateurs</a></li>
                <li><a href="admin.php?page=products">Produits</a></li>
                <li><a href="admin.php?page=categories">Catégories</a></li>
                <li><a href="index.php">Retour à l'accueil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
        <hr>
        <div>
            <?php
            if (isset($_GET['page']) && $_GET['page'] === 'categories') {
                echo "<h2>Gestion des catégories</h2>";
            } else if (isset($_GET['page']) && $_GET['page'] === 'products') {
                echo "<h2>Gestion des produits</h2>";
            } else if (isset($_GET['page']) && $_GET['page'] === 'users') {
                echo "<h2>Gestion des utilisateurs</h2>";
            }
            ?>
        </div>
    </div>
</body>
</html>