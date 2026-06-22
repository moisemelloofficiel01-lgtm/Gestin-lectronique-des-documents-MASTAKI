# GED DSTN sur XAMPP

## Prérequis
- XAMPP installé
- Apache démarré
- MySQL démarré

## Lancement
1. Placer ce projet dans `c:\xampp\htdocs\team37`
2. Démarrer `Apache` et `MySQL` depuis le panneau XAMPP
3. Ouvrir l'application : [http://localhost/team37](http://localhost/team37)
4. PhpMyAdmin : [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

## Base de données
- Hôte : `localhost`
- Base : `my_database_ged`
- Utilisateur par défaut : `root`
- Mot de passe par défaut : vide

L'application crée automatiquement la base `my_database_ged` si elle n'existe pas encore, puis initialise les tables nécessaires au premier chargement.

## Configuration optionnelle
Si votre environnement MySQL n'utilise pas les identifiants par défaut de XAMPP, vous pouvez définir ces variables :
- `MYSQL_HOST`
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`

## Point d'entrée
- `index.php` à la racine redirige vers `src/`
- l'application principale se trouve dans `src/`
