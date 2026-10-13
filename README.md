# StockApp_API

## Instalacion en XAMPP

1. Copia esta carpeta como `C:\xampp\htdocs\stockapp-api`.
2. Importa `inventario_computadoras.sql` en phpMyAdmin si es una instalacion nueva.
3. Si ya tienes la base creada, ejecuta una sola vez `usuarios_perfil_migracion.sql`.
4. Verifica que Apache y MySQL esten iniciados. La aplicacion movil debe apuntar a la IP de tu PC y al puerto de Apache, por ejemplo `192.168.1.6:80`.

La API de perfil queda disponible en:

- `GET /stockapp-api/api/perfil.php?user_id=1`: consulta el perfil desde `users`.
- `PATCH /stockapp-api/api/perfil.php`: actualiza `name`, `username`, `email`, `phone` y `role`. El cuerpo debe incluir `id`.

Ejemplo de cuerpo para actualizar:

```json
{
  "id": 1,
  "name": "Administrador",
  "username": "admin",
  "email": "admin@example.com",
  "phone": "6180000000",
  "role": "admin"
}
```

El endpoint nunca recibe ni modifica `password`; los correos y contrasenas existentes se conservan. Al iniciar sesion, `login.php` actualiza `last_login_at` y devuelve los datos del perfil.