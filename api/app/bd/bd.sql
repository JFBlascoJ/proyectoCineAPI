-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS logrofilm;
-- Usar la base de datos
USE logrofilm;
-- Crear la tabla PELICULAS
CREATE TABLE
    IF NOT EXISTS PELICULAS (
        id_peli INT AUTO_INCREMENT PRIMARY KEY,
        tit_original VARCHAR(255),
        tit_espanol VARCHAR(255),
        genero VARCHAR(255),
        ano INT,
        duracion INT,
        sinopsis TEXT,
        reparto TEXT,
        poster TEXT,
        director VARCHAR(255)
    );
-- Crear la tabla USUARIOS
CREATE TABLE
    IF NOT EXISTS USUARIOS (
        id_usr INT AUTO_INCREMENT PRIMARY KEY,
        correo VARCHAR(255) UNIQUE,
        username VARCHAR(255) UNIQUE,
        clave VARCHAR(255),
        nombre VARCHAR(255),
        apellidos VARCHAR(255),
        foto VARCHAR(255),
        es_admin BOOLEAN
    );
-- Crear la tabla COMENTARIOS
CREATE TABLE
    IF NOT EXISTS COMENTARIOS (
        id_comentario INT AUTO_INCREMENT PRIMARY KEY,
        id_peli INT,
        id_usr INT,
        comentario TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_peli) REFERENCES PELICULAS(id_peli) ON DELETE CASCADE,
        FOREIGN KEY (id_usr) REFERENCES USUARIOS(id_usr) ON DELETE CASCADE
    );
-- Crear la tabla VALORACIONES
CREATE TABLE
    IF NOT EXISTS VALORACIONES (
        id_valoracion INT AUTO_INCREMENT PRIMARY KEY,
        id_peli INT,
        id_usr INT,
        valoracion INT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_peli) REFERENCES PELICULAS(id_peli) ON DELETE CASCADE,
        FOREIGN KEY (id_usr) REFERENCES USUARIOS(id_usr) ON DELETE CASCADE
    );