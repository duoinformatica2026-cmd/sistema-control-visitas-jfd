# Sistema de Control de Visitas JFD — PHP + MySQL

Sistema web para el C.E.M.G. Técnico “Dr. Jorge Fidel Durón”, preparado para XAMPP y despliegue en Railway mediante GitHub.

## Credenciales iniciales
- Administrador: `admin` / `admin123`
- Guardia: `guardia` / `guardia1`

## Flujo definitivo
1. El administrador administra guardias, visitantes, visitas, reportes y códigos QR.
2. Hay 10 QR iniciales reutilizables (`JFD-QR-0001` a `JFD-QR-0010`) y el administrador puede crear más.
3. El guardia registra una visita y selecciona un QR disponible.
4. El escáner QR **solo registra la entrada**. No registra salidas.
5. La salida se registra desde **Personas en el instituto → Ya salió**.
6. Al marcar la salida, el QR vuelve automáticamente a `Disponible` y puede reutilizarse.
7. Al escribir un DNI ya registrado, solo se recuperan nombre y teléfono. Motivo, persona a visitar y área quedan vacíos.

## CRUD administrativo
- Guardias: crear, consultar, editar usuario/contraseña, activar/desactivar y eliminar si no tienen historial.
- Visitantes: crear, buscar, editar y eliminar si no tienen historial.
- Visitas: consultar, buscar, editar, cancelar y eliminar.
- QR: generar, crear manualmente, editar, activar/desactivar, eliminar si nunca se usó e imprimir en tarjetas de 7 × 9 cm.
- Reportes: diario y general, ambos imprimibles.

## Base de datos
- Instalación nueva: importe `sql/control_visitas.sql`.
- Si ya tenía la versión anterior: haga un respaldo e importe `sql/ACTUALIZAR_DESDE_VERSION_ANTERIOR.sql`.

## Railway
La aplicación lee `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD` y `MYSQLDATABASE`. El `Dockerfile` y `railway.json` ya están incluidos.


## Derechos de autor
Todas las pantallas visibles usan el pie de página compartido con: “Jonathan Isaac Garcia Aguilar” y “Duodecimo Grado BTP Informatica 2026💖”.
