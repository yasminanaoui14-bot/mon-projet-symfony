# 🍽️ Vite & Gourmand

Projet réalisé dans le cadre de l'Évaluation en Cours de Formation (ECF) du titre professionnel **Développeur Web et Web Mobile**.

## 📖 Présentation

**Vite & Gourmand** est une application web développée avec **Symfony** permettant à une entreprise de traiteur de présenter ses menus, gérer les commandes de ses clients et administrer son activité grâce à un espace Employé et Administrateur.

L'application propose différents espaces selon le rôle de l'utilisateur afin de répondre aux besoins de gestion de l'entreprise.

---

# ✨ Fonctionnalités

## 👤 Espace Utilisateur

- Création de compte
- Connexion / Déconnexion
- Réinitialisation du mot de passe par e-mail
- Consultation des menus
- Recherche et filtres des menus
- Consultation du détail des menus
- Création d'une commande
- Consultation et annulation des commandes
- Modification du profil
- Dépôt d'avis

---

## 👨‍🍳 Espace Employé

- Gestion des commandes
- Modification du statut des commandes
- Validation ou refus des avis clients
- Recherche des commandes par client ou statut

---

## 👑 Espace Administrateur

- Création de comptes employés
- Désactivation d'un employé
- Tableau de bord administratif
- Statistiques des commandes
- Calcul du chiffre d'affaires
- Rapport imprimable (PDF)
- Graphiques interactifs avec Chart.js

---

# 📊 Rapport Administrateur

Le tableau de bord permet de consulter :

- Nombre total de commandes
- Chiffre d'affaires
- Panier moyen
- Menu le plus commandé
- Répartition des commandes
- Filtres par menu et période
- Impression du rapport

---

# 🛠️ Technologies utilisées

Backend :

- PHP 8
- Symfony

Frontend :

- Twig
- Bootstrap 5
- JavaScript
- HTML5
- CSS3

Base de données :

- MySQL

Déploiement :

- Docker
- Render
- Aiven (MySQL Cloud)

Outils : 

- VS Code
- Composer
- Chart.js
- PDO
- Mailtrap
- Git
- GitHub

---

# ⚙️ Installation

Cloner le projet :

```bash
git clone https://github.com/yasminanaoui14-bot/mon-projet-symfony.git
```

Installer les dépendances :

```bash
composer install
```

Configurer le fichier `.env`.

Créer la base de données puis importer le fichier SQL fourni.

Lancer le serveur Symfony :

```bash
symfony serve
```

---

# 📁 Structure du projet

- `/src` : Contrôleurs et logique métier
- `/templates` : Vues Twig
- `/public` : Ressources publiques
- `/config` : Configuration Symfony
- `/sql` : Base de données

---

# 🔒 Sécurité

- Authentification sécurisée
- Gestion des rôles (Utilisateur, Employé, Administrateur)
- Hachage des mots de passe
- Validation des formulaires
- Protection des espaces selon les rôles


---

# 📷 Captures d'écran

Des captures d'écran sont disponibles dans le rapport du projet.

---

## Comptes de démonstration

### Utilisateur
Email : yasmina3@test.com
Mot de passe : Client2026.

### Employé
Email : Maya@test.com
Mot de passe : Employe2026.

### Administrateur
Email : admin@exemple.com
Mot de passe : motdepasse

---
## Déploiement

L'application est déployée sur Render.

Le projet est déployé sur Render avec :

- Docker
- Base de données MySQL hébergée sur Aiven
- Variables d'environnement configurées sur Render
- Apache configuré avec `mod_rewrite`
- Fichier `.htaccess` pour le routage Symfony

### Technologies utilisées

- Symfony 7
- PHP 8.2
- MySQL
- Aiven (base de données cloud)
- Render (hébergement)
- Apache
- Mailtrap (tests des e-mails)

## Gestion de projet

Trello :
https://trello.com/invite/b/6a3565114d739610b8d04f50/ATTI7747a835e505e67dcf155a8c704cef7205ECCA8B/ecf-vite-gourmand

Dépôt GitHub

Projet Symfony :
https://github.com/yasminanaoui14-bot/mon-projet-symfony

Liens :

Lien de l'application : 
https://mon-projet-symfony-oqh0.onrender.com 

---

# 👩‍💻 Réalisé par

Yasmina Naoui

Projet réalisé dans le cadre de l'ECF du titre professionnel **Développeur Web et Web Mobile**.
2026