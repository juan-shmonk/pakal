# PAKAL — Configuración manual de base de datos

Ejecuta estos pasos en orden desde la terminal (CMD o PowerShell como administrador).

---

## Paso 1 — Detener MySQL

Desde el Panel de Control de XAMPP: haz clic en **Stop** junto a MySQL.

O desde terminal:
```
net stop mysql
```

---

## Paso 2 — Borrar el directorio de la base de datos

```
rmdir /s /q "C:\xampp\mysql\data\pakal_tienda"
```

Si el directorio no existe, ignora el error y continúa.

---

## Paso 3 — Iniciar MySQL

Desde el Panel de Control de XAMPP: haz clic en **Start** junto a MySQL.

O desde terminal:
```
net start mysql
```

---

## Paso 4 — Crear la base de datos y las tablas

```
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE pakal_tienda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

```
"C:\xampp\mysql\bin\mysql.exe" -u root pakal_tienda < "C:\xampp\htdocs\proyecto\database\pakal.sql"
```

---

## Paso 5 — Crear el usuario administrador

```
"C:\xampp\mysql\bin\mysql.exe" -u root pakal_tienda -e "INSERT INTO usuarios (nombre, apellido, email, password_hash, rol) VALUES ('Admin', 'PAKAL', 'admin@pakal.mx', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');"
```

> La contraseña de ese hash es: **Pakal2026!**
> (hash bcrypt generado con cost 12)

---

## Paso 6 — Verificar

```
"C:\xampp\mysql\bin\mysql.exe" -u root pakal_tienda -e "SHOW TABLES; SELECT id, email, rol FROM usuarios;"
```

Deberías ver 6 tablas y el usuario admin listado.

---

## Acceso a la tienda

| URL | Descripción |
|-----|-------------|
| http://localhost/proyecto/index.php | Tienda |
| http://localhost/proyecto/auth.php | Login / Registro |
| http://localhost/proyecto/admin.php | Panel admin |

**Credenciales admin:** `admin@pakal.mx` / `Pakal2026!`
