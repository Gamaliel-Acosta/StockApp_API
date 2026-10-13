-- Ejecutar una sola vez si la base inventario_computadoras ya existe.
-- No modifica correos, contrasenas, roles ni usuarios existentes.
USE inventario_computadoras;

ALTER TABLE users
  ADD COLUMN username VARCHAR(50) DEFAULT NULL AFTER name,
  ADD COLUMN phone VARCHAR(30) DEFAULT NULL AFTER email,
  ADD COLUMN last_login_at TIMESTAMP NULL DEFAULT NULL AFTER created_at,
  ADD UNIQUE KEY uq_users_username (username);

-- Los nombres de usuario son datos editables del perfil. El telefono queda
-- NULL hasta que se capture un valor real desde la aplicacion.
UPDATE users SET username = CASE id
  WHEN 1 THEN 'admin'
  WHEN 2 THEN 'maria'
  WHEN 3 THEN 'inactivo'
  ELSE NULL
END
WHERE username IS NULL;
