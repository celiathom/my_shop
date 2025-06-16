my_shop
Description

my_shop est une application web complète de e-commerce développée en PHP, SQL, HTML et CSS.
Ce projet met en pratique les fondamentaux du web et la programmation orientée objet pour créer un site de vente en ligne avec un front office (pour les clients) et un back office (pour les administrateurs).

Objectifs

    Développer un projet e-commerce from scratch.

    Utiliser les outils de base du développement web.

    Appliquer les bonnes pratiques de sécurité et de design.

    Améliorer l’ergonomie, l’accessibilité et le référencement (SEO).

Fonctionnalités principales
Pages publiques

    index.php : page d’accueil affichant tous les produits de la boutique.

    Formulaire d’inscription signup.php :

        Création de compte avec pseudo, email et mot de passe.

        Gestion des erreurs : email invalide, mot de passe vide, doublon de pseudo/email, etc.

        Mots de passe sécurisés (hashage) et protection contre les injections SQL.

    Formulaire de connexion signin.php :

        Connexion sécurisée avec vérification des identifiants.

        Redirection selon le rôle (client ou admin).

    Déconnexion avec suppression des cookies/session.

✅ Interface Admin (admin.php)

Accessible uniquement aux administrateurs après authentification :

    Gestion des utilisateurs : CRUD complet.

    Gestion des produits : CRUD complet (nom, description, prix, image, catégories multiples).

    Gestion des catégories :

        Hiérarchie illimitée (ex. : meubles → chaises → chaises en bois).

        CRUD complet.

    Sécurité renforcée : les pages admin sont protégées, impossibles à manipuler par un client.

Front office (clients)

    Affichage des produits avec un design responsive.

    Navigation claire et rapide.

    Filtres :

        Barre de recherche (nom, description, catégorie).

        Filtrage par plage de prix.

        Tri par ordre alphabétique ou par prix croissant/décroissant.

SEO & Performance

    Code valide W3C.

    Optimisation des images et des temps de chargement.

    Structure HTML sémantique.

