SET NAMES utf8mb4;
SET time_zone = '-06:00';

CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre_usuario VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  nombre_completo VARCHAR(150) NOT NULL,
  rol ENUM('Guardia','Admin') NOT NULL DEFAULT 'Guardia',
  estado TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitantes (
  id_visitante INT AUTO_INCREMENT PRIMARY KEY,
  numero_identidad VARCHAR(40) NOT NULL UNIQUE,
  nombre_completo VARCHAR(180) NOT NULL,
  telefono VARCHAR(40) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS codigos_qr (
  id_codigo_qr INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(80) NOT NULL UNIQUE,
  estado ENUM('Disponible','Asignado','Inactivo') NOT NULL DEFAULT 'Disponible',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_por INT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  asignado_en DATETIME NULL,
  CONSTRAINT fk_qr_creador FOREIGN KEY (creado_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
  INDEX idx_qr_estado (estado),
  INDEX idx_qr_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitas (
  id_visita INT AUTO_INCREMENT PRIMARY KEY,
  id_visitante INT NOT NULL,
  id_usuario_registro INT NOT NULL,
  id_codigo_qr INT NOT NULL,
  codigo_qr VARCHAR(80) NOT NULL,
  motivo VARCHAR(500) NOT NULL,
  persona_a_visitar VARCHAR(180) NOT NULL,
  area_destino VARCHAR(180) NOT NULL,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  hora_entrada DATETIME NULL,
  hora_salida DATETIME NULL,
  estado_visita ENUM('Pendiente','Activa','Finalizada','Cancelada') NOT NULL DEFAULT 'Pendiente',
  CONSTRAINT fk_visita_visitante FOREIGN KEY (id_visitante) REFERENCES visitantes(id_visitante),
  CONSTRAINT fk_visita_usuario FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario),
  CONSTRAINT fk_visita_qr FOREIGN KEY (id_codigo_qr) REFERENCES codigos_qr(id_codigo_qr),
  INDEX idx_visita_qr (id_codigo_qr),
  INDEX idx_visitas_codigo_qr (codigo_qr),
  INDEX idx_visitas_estado (estado_visita),
  INDEX idx_visitas_fecha (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin: admin / admin123
INSERT INTO usuarios(nombre_usuario,password_hash,nombre_completo,rol,estado)
VALUES ('admin','$2y$12$rdx36KcWFsK2zQwfiXed8.P4vuWNwFWvnuEwO95uzvkBvdSxIS7K2','Administrador General','Admin',1)
ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),nombre_completo=VALUES(nombre_completo),rol='Admin',estado=1;

-- Guardia inicial: guardia / guardia1
INSERT INTO usuarios(nombre_usuario,password_hash,nombre_completo,rol,estado)
VALUES ('guardia','$2y$12$lSVXsbfM5zB3Vkx19N1az.Gt2mt6GplKgtZvf2pgem5GKuELmgU4i','Guardia de Turno','Guardia',1)
ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),nombre_completo=VALUES(nombre_completo),rol='Guardia',estado=1;

-- 10 gafetes QR reutilizables iniciales.
INSERT INTO codigos_qr(codigo,estado,activo,creado_por) VALUES
('JFD-QR-0001','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0002','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0003','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0004','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0005','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0006','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0007','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0008','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0009','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1)),
('JFD-QR-0010','Disponible',1,(SELECT id_usuario FROM usuarios WHERE nombre_usuario='admin' LIMIT 1))
ON DUPLICATE KEY UPDATE activo=1;
