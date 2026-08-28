-- ACTUALIZACIÓN para instalaciones de la versión anterior.
-- Haga respaldo antes de ejecutar este archivo.
SET NAMES utf8mb4;
SET time_zone = '-06:00';

-- Asegurar credenciales solicitadas.
UPDATE usuarios SET password_hash='$2y$12$rdx36KcWFsK2zQwfiXed8.P4vuWNwFWvnuEwO95uzvkBvdSxIS7K2',estado=1,rol='Admin' WHERE nombre_usuario='admin';
UPDATE usuarios SET password_hash='$2y$12$lSVXsbfM5zB3Vkx19N1az.Gt2mt6GplKgtZvf2pgem5GKuELmgU4i',estado=1,rol='Guardia' WHERE nombre_usuario='guardia';

-- Los QR pasan a ser reutilizables. Si la columna activo no existe, agréguela.
SET @db=DATABASE();
SET @sql=(SELECT IF(COUNT(*)=0,'ALTER TABLE codigos_qr ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER estado','SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='codigos_qr' AND COLUMN_NAME='activo');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Quitar la relación única entre visita y QR para permitir reutilización histórica.
SET @fk=(SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND COLUMN_NAME='id_codigo_qr' AND REFERENCED_TABLE_NAME='codigos_qr' LIMIT 1);
SET @sql=IF(@fk IS NULL,'SELECT 1',CONCAT('ALTER TABLE visitas DROP FOREIGN KEY `',@fk,'`'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx=(SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND COLUMN_NAME='id_codigo_qr' AND NON_UNIQUE=0 AND INDEX_NAME<>'PRIMARY' LIMIT 1);
SET @sql=IF(@idx IS NULL,'SELECT 1',CONCAT('ALTER TABLE visitas DROP INDEX `',@idx,'`'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx2=(SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND COLUMN_NAME='codigo_qr' AND NON_UNIQUE=0 AND INDEX_NAME<>'PRIMARY' LIMIT 1);
SET @sql=IF(@idx2 IS NULL,'SELECT 1',CONCAT('ALTER TABLE visitas DROP INDEX `',@idx2,'`'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

ALTER TABLE visitas ADD INDEX idx_visita_qr (id_codigo_qr);
ALTER TABLE visitas ADD INDEX idx_visitas_codigo_qr (codigo_qr);
ALTER TABLE visitas ADD CONSTRAINT fk_visita_qr FOREIGN KEY (id_codigo_qr) REFERENCES codigos_qr(id_codigo_qr);

-- Cambiar la lógica de Consumido: los QR finalizados vuelven a estar disponibles.
UPDATE codigos_qr SET estado='Disponible',asignado_en=NULL WHERE estado='Consumido';
ALTER TABLE codigos_qr MODIFY estado ENUM('Disponible','Asignado','Inactivo') NOT NULL DEFAULT 'Disponible';
ALTER TABLE codigos_qr DROP COLUMN consumido_en;
UPDATE codigos_qr SET estado=IF(activo=1,'Disponible','Inactivo'),asignado_en=NULL WHERE estado<>'Asignado';

-- Corregir QR que realmente están ocupados por visitas pendientes/activas.
UPDATE codigos_qr c JOIN visitas v ON v.id_codigo_qr=c.id_codigo_qr AND v.estado_visita IN ('Pendiente','Activa') SET c.estado='Asignado',c.activo=1;

-- Asegurar los 10 QR iniciales reutilizables.
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
