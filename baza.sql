-- baza.sql

-- Utworzenie bazy danych (jeśli nie istnieje)
CREATE DATABASE IF NOT EXISTS cms_database DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE cms_database;

-- Tabela użytkowników
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  account_group INT NOT NULL DEFAULT 1, -- 1 = standard, 3 = administrator
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabela ustawień strony
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_name VARCHAR(255) NOT NULL DEFAULT 'CMS - HP Domy i Garaże',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert domyślnego ustawienia, jeżeli nie istnieje
INSERT INTO settings (site_name) VALUES ('CMS - HP Domy i Garaże') 
  ON DUPLICATE KEY UPDATE site_name = site_name;

-- Tabela dla zdjęć do galerii
CREATE TABLE IF NOT EXISTS gallery_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_path VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabela do przechowywania dynamicznej treści stron (opcjonalnie)
CREATE TABLE IF NOT EXISTS pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_name VARCHAR(100) NOT NULL UNIQUE,
  content TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
