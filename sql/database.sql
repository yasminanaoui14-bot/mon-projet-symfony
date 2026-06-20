CREATE DATABASE IF NOT EXISTS vite_gourmand;
USE vite_gourmand;

CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL
);

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    theme VARCHAR(100) NOT NULL,
    nombre_personnes_min INT NOT NULL,
    prix DECIMAL(10,2) NOT NULL
);

CREATE TABLE commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    menu_id INT,
    nom_client VARCHAR(100) NOT NULL,
    email_client VARCHAR(180) NOT NULL,
    adresse TEXT NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    date_prestation DATE NOT NULL,
    heure_prestation TIME NOT NULL,
    nombre_personnes INT NOT NULL,
    statut VARCHAR(100) NOT NULL,
    prix_total DECIMAL(10,2),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);

CREATE TABLE contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO menu (titre, description, theme, nombre_personnes_min, prix, image) VALUES
('Menu Classique', 'Menu traditionnel français', 'Classique', 4, 80.00, 'menu-classique.jpg'),
('Menu Festif', 'Idéal pour les fêtes et événements', 'Festif', 10, 150.00, 'menu-festif.jpg'),
('Menu Végétarien', 'Menu végétarien équilibré et gourmand', 'Végétarien', 2, 60.00, 'menu-vegetarien.jpg');

INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES
('Naoui', 'Yasmina', 'yasmina@example.com', 'motdepasse', 'ROLE_USER'),
('Employe', 'Test', 'employe@example.com', 'motdepasse', 'ROLE_EMPLOYE'),
('Admin', 'Test', 'admin@example.com', 'motdepasse', 'ROLE_ADMIN');

INSERT INTO commande (
    utilisateur_id,
    menu_id,
    nom_client,
    email_client,
    adresse,
    telephone,
    date_prestation,
    heure_prestation,
    nombre_personnes,
    statut,
    prix_total
) VALUES
(1, 1, 'Yasmina Nao', 'yasmina@example.com', '18 rue de la Gastronomie, Bordeaux', '0558123400', '2026-07-10', '12:00:00', 4, 'Acceptée', 320.00),
(1, 2, 'Florian Clau', 'florian@example.com', '25 avenue des Fêtes, Bordeaux', '0558123401', '2026-07-15', '19:30:00', 10, 'En préparation', 1500.00),
(1, 1, 'Riad Inao', 'riad@example.com', '8 rue du Centre, Bordeaux', '0558123402', '2026-07-20', '13:00:00', 5, 'Livrée', 360.00);