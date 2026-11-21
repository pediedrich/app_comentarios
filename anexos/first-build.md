# First Build — App de Comentarios MVP

## Estado general

- Ejecucion iniciada y completada para el MVP.
- Stack aplicado: PHP 8.2, MySQL/MariaDB y frontend HTML/CSS/JavaScript.
- Persistencia real validada con XAMPP.
- Pruebas de integracion locales: aprobadas.
- Servidor local activo en `http://127.0.0.1:8081`.

## Archivos creados

### Configuracion y persistencia

- `schema.sql`: crea la base `app_comentarios` y la tabla `comments`.
- `config.php`: configura host, puerto, base, usuario y password desde variables de entorno.
- `src/Database.php`: crea la conexion PDO con MySQL.

### Reglas de negocio

- `src/CommentService.php`: valida texto, crea comentarios y respuestas y controla la moderacion.
- `src/CommentRepository.php`: persiste comentarios, consulta el orden y aplica la baja moderada.

### API

- `api.php`: expone las acciones:
  - `GET api.php?action=list`
  - `POST api.php?action=comments`
  - `POST api.php?action=replies`
  - `POST api.php?action=moderation-delete`
- `index.php`: sirve la API y los archivos del frontend.

### Frontend

- `public/index.html`: interfaz minima de comentarios y respuestas.
- `public/styles.css`: estilos basicos.
- `public/app.js`: carga comentarios, publica comentarios y muestra respuestas.

### Pruebas

- `tests/run.php`: ejecuta pruebas de integracion contra MySQL.

## Modelo implementado

Tabla `comments`:

- `id`: identificador unico.
- `text`: texto Unicode de hasta 500 caracteres.
- `parent_id`: nulo para comentario principal y referencia al principal para respuestas.
- `created_at`: fecha de creacion.
- `deleted_at`: baja logica opcional.

## Reglas implementadas

### F-001 — Crear comentarios

- Se aceptan comentarios principales.
- El texto se recorta al inicio y al final.
- Se rechazan textos vacios.
- Se rechaza texto mayor a 500 caracteres Unicode.
- Se soporta exactamente un nivel de respuesta.
- La respuesta debe existir como comentario principal.
- Se rechaza responder a otra respuesta.
- El alta usa una transaccion y confirma antes de informar exito.

### F-002 — Eliminar comentarios

- La baja se expone como una accion de moderacion.
- No existe borrado propio basado en autor.
- No existe ventana de cinco minutos.
- Un comentario con respuestas no puede eliminarse.
- El comentario eliminado usa baja logica y deja de aparecer en consultas.

### RN-001 — Orden

- Los comentarios se consultan por `created_at DESC`.
- Se usa `id DESC` como desempate estable.

### D-004 — Anidacion

- Los comentarios principales tienen `parent_id = NULL`.
- Las respuestas tienen `parent_id` apuntando al comentario principal.
- No se permite `parent_id` que apunte a otra respuesta.

## Pruebas ejecutadas

Comando utilizado:

```text
php C:\xampp\htdocs\app_comentarios\tests\run.php
```

Resultado:

```text
OK: 11 pruebas de integracion completadas.
```

Cobertura incluida:

- Creacion de comentario principal.
- Creacion de respuesta.
- Pertenencia de respuesta al principal.
- Consulta de dos comentarios principales.
- Orden del mas reciente al mas antiguo.
- Inclusion de respuesta bajo su comentario principal.
- Rechazo de texto vacio.
- Rechazo de texto mayor a 500 caracteres.
- Rechazo de respuesta a otra respuesta.
- Bloqueo de eliminacion con respuestas.
- Eliminacion moderada sin respuestas.
- Ausencia del eliminado en la consulta posterior.

## Validacion HTTP

Validaciones realizadas:

- `GET api.php?action=list`: devuelve una respuesta JSON correcta.
- `/`: devuelve HTTP 200 y sirve el frontend.
- Creacion HTTP de comentario: aprobada.
- Creacion HTTP de respuesta: aprobada.
- El intento de moderacion sobre un comentario con respuestas fue rechazado por la regla `RN-007`.

## Persistencia

Se inicio el servidor MySQL local de XAMPP y se cargo `schema.sql`.

Configuracion predeterminada:

- Host: `127.0.0.1`
- Puerto: `3306`
- Base: `app_comentarios`
- Usuario: `root`
- Password: vacio

Las variables compatibles son:

- `APP_DB_HOST`
- `APP_DB_PORT`
- `APP_DB_NAME`
- `APP_DB_USER`
- `APP_DB_PASSWORD`

## Inicio local

El servidor PHP fue iniciado con:

```text
php -S 127.0.0.1:8081
```

Desde el directorio `C:\xampp\htdocs\app_comentarios`.

## Fuera de alcance

No se implementaron:

- Autenticacion de usuarios.
- Gestion de roles.
- Gestion de moderadores.
- Edicion propia.
- Borrado propio por autor.
- Ventana de eliminacion de cinco minutos.
- Notificaciones en tiempo real.
- Votos.
- Reacciones.
- Likes.

## Pendientes tecnicos

- Confirmar si la baja logica es preferida permanentemente o si debe reemplazarse por eliminacion fisica.
- Definir el mecanismo externo real de invocacion moderada.
- Definir formato canonico de respuestas API para todos los clientes.
- Definir si el frontend web debera mostrar una interfaz separada de moderacion.
- Ejecutar pruebas desde un servidor web Apache real si se desea validar el comportamiento XAMPP con rutas adicionales.

## Resultado

La primera construccion entrega el MVP funcional:

- Backend PHP.
- Persistencia MySQL.
- Frontend HTML/CSS/JavaScript.
- API de comentarios principales.
- API de respuestas de un nivel.
- API de baja moderada.
- Consulta ordenada.
- Validacion centralizada.
- Persistencia atomica.
- Pruebas de integracion.

## Correccion posterior — recursos estaticos

### Problema

El servidor PHP con router no resolvia `/styles.css` y `/app.js`, porque esos recursos estaban dentro de `public/` mientras el HTML los solicita desde la raiz.

### Correccion

- Se agregaron rutas explicitas para `/styles.css` y `/app.js` en `index.php`.
- Se mantuvo `/` como carga de `public/index.html`.
- El servidor se reinicio usando `index.php` como router:

```text
php -S 127.0.0.1:8081 index.php
```

### Verificacion

- `/`: HTTP 200.
- `/styles.css`: HTTP 200.
- `/app.js`: HTTP 200.
- `api.php?action=list`: respuesta JSON HTTP 200.
