# Salaire Anonyme — projet PHP/MySQL

Application web de démonstration permettant de déclarer un salaire sans publier de nom, puis de consulter des statistiques agrégées.

## Technologies
- PHP 8+
- MySQL / MariaDB
- HTML, CSS, JavaScript
- XAMPP (développement local)

## Installation avec XAMPP
1. Installer et lancer XAMPP.
2. Démarrer **Apache** et **MySQL**.
3. Copier le dossier du projet dans `C:\xampp\htdocs\` et le renommer `salaire_anonyme`.
4. Ouvrir `http://localhost/phpmyadmin`.
5. Importer le fichier `database.sql` (onglet **Importer**). Attention : il supprime et recrée la table.
6. (Démo) Importer ensuite `seed.sql` pour charger des déclarations fictives.
7. Vérifier les paramètres de connexion dans `config.php` (configuration XAMPP habituelle : utilisateur `root`, mot de passe vide).
8. Ouvrir `http://localhost/salaire_anonyme/`.

## Fonctionnalités
- Formulaire de déclaration anonyme d'un salaire.
- Choix du métier, du niveau d'expérience et du secteur ; salaire mensuel net en FCFA.
- Tableau de bord affichant, par profil, le salaire médian et la fourchette courante (1er–3e quartile).
- Les groupes contenant moins de 5 déclarations ne sont pas affichés.
- Page **Comparer** (`comparer.php`) : on choisit un profil et on saisit son salaire ; le site indique la médiane du profil et la position (en dessous, dans ou au-dessus de la fourchette courante). Rien n'est enregistré. La position n'est donnée qu'en 3 niveaux, jamais en rang exact, pour ne pas révéler de salaires individuels.
- **Jeton secret** : après l'envoi, un jeton est affiché une seule fois (`merci.php`). Sur la page **Ma déclaration** (`ma-declaration.php`), il permet de modifier ou de supprimer sa déclaration, sans compte. Seule l'empreinte SHA-256 du jeton est stockée en base ; un jeton perdu ne peut pas être retrouvé. Les données de démo (`seed.sql`) n'ont pas de jeton.
- Validation côté serveur, requêtes préparées PDO et jeton CSRF.

## Important
Ce projet est une base pédagogique, pas une plateforme prête pour une mise en production. Avant publication sur Internet, ajoutez notamment HTTPS, une politique de confidentialité, une protection anti-spam, une limitation de débit, une gestion des sauvegardes et une revue de sécurité. L'anonymat absolu ne peut pas être garanti : évitez de collecter des informations identifiantes et ne publiez pas de groupes trop petits.
