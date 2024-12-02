# Starvel - Site de Réservation de Trains

Starvel est un site de réservation de trajets en train, permettant aux utilisateurs de réserver, consulter et gérer leurs trajets, ainsi qu'aux administrateurs de gérer les trains, gares et utilisateurs.

## Fonctionnalités

### Utilisateur
- Inscription et gestion de profil
- Réservation de trajets en train
- Consultation et gestion des réservations passées
- Modification des informations personnelles

### Administrateur
- Gestion des utilisateurs : création, modification, suppression
- Gestion des trains : ajout, modification, suppression
- Gestion des gares et des adresses

### Super-administrateur
- Gestion des administrateurs et super-administrateurs : ajout, suppression

## Installation
1. Clonez ce repository
2. Décompressez le dossier dans le répertoire de votre serveur web (par exemple, www).
3. Importez la base de données :
  - Ouvrez train.sql dans PHPMyAdmin
  - Créez une base de données appelée train et importez le fichier
  - Modifiez le fichier header.php si nécessaire, pour adapter la configuration de votre serveur.

Les comptes suivants sont déjà créés avec des insertions :
  - Utilisateur :
    Email : bli@gmail.com
    Mot de passe : Blibli22 !
  - Super-administrateur :
    Email : blabla@gmail.com
    Mot de passe : Blibli22 !
  - Administrateur :
    Email : emailathene@truc.com
    Mot de passe : pasdur

Vous pouvez également créer votre propre compte via la page d'inscription.

Je vous conseille de lancer la page "connexion.php" pour lancer le site.
