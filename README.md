# Vite & Gourmand

Application web développée dans le cadre de l'ECF GRADUATE Développeur Web et Web Mobile.

## Objectif

Créer une application permettant à un traiteur de présenter ses menus, gérer les commandes, les utilisateurs, les employés, les avis clients et le contact.

## Technologies prévues

- Symfony
- Twig
- MySQL
- MongoDB
- HTML
- CSS
- Bootstrap
- JavaScript
- GitHub

## Installation

### Prérequis

- PHP 8.2+
- Composer
- Symfony CLI
- MySQL

### Installation du projet

Cloner le dépôt :

git clone URL_DU_PROJET

Installer les dépendances :

composer install

Configurer la base de données dans le fichier .env

Créer la base :

php bin/console doctrine:database:create

Lancer le serveur :

symfony server:start


## Comptes de démonstration

### Utilisateur

Email : yasmina@example.com

Mot de passe : ********

### Employé

Email : employe@vite-gourmand.fr

Mot de passe : ********

### Administrateur

Email : admin@vite-gourmand.fr

Mot de passe : ********


## Fonctionnalités réalisées

### Utilisateur

- Consultation des menus
- Consultation du détail des menus
- Inscription
- Connexion
- Formulaire de contact
- Commande de prestation

### Employé

- Consultation des commandes
- Filtre par statut
- Recherche client
- Modification du statut des commandes
- Annulation avec motif
- Validation des avis clients

### Administrateur

- Consultation du rapport
- Statistiques des commandes
- Chiffre d'affaires

## Structure du projet

src/
 ├── Controller/
templates/
 ├── home/
public/
sql/
 └── database.sql

 Projet ECF Vite & Gourmand