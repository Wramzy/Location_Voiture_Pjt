-- Script pour corriger l'utilisateur MySQL
-- Exécutez ce script en tant qu'utilisateur root MySQL

-- Supprimer l'ancien utilisateur s'il existe
DROP USER IF EXISTS 'car_rental'@'localhost';
DROP USER IF EXISTS 'car_rental'@'127.0.0.1';
DROP USER IF EXISTS 'car_rental'@'%';

-- Créer l'utilisateur avec caching_sha2_password (plugin par défaut pour MySQL 8+)
CREATE USER 'car_rental'@'localhost' IDENTIFIED BY 'Pass1234!';
CREATE USER 'car_rental'@'127.0.0.1' IDENTIFIED BY 'Pass1234!';

-- Accorder tous les privilèges sur la base de données
GRANT ALL PRIVILEGES ON location_voiture.* TO 'car_rental'@'localhost';
GRANT ALL PRIVILEGES ON location_voiture.* TO 'car_rental'@'127.0.0.1';

-- Appliquer les changements
FLUSH PRIVILEGES;

-- Vérifier que l'utilisateur est créé
SELECT user, host, plugin FROM mysql.user WHERE user = 'car_rental';
