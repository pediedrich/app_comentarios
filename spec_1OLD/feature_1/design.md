# DESIGN.md - Estructura Técnica y Arquitectura de Datos

## Arquitectura de Directorios (Monolito Limpio)
```text
├── config/
│   └── database.php       # Conexión PDO en modo estricto
├── src/
│   ├── Actions/           # Lógica de negocio (Comentarios, Reacciones, Reportes)
│   ├── Services/          # Integración externa (Servicio de Moderación de IA)
│   └── Middleware/        # AuthMiddleware (Verificación de sesión y baneos)
├── public/
│   ├── assets/
│   │   ├── js/
│   │   │   └── app.js     # Lógica AJAX con Fetch API y manipulación de DOM
│   │   └── css/           # Estilos personalizados adicionales sobre Bootstrap 5
│   ├── index.php          # Punto de entrada principal / Renderizado del tablero
│   └── api.php            # Router API para peticiones asíncronas de JavaScript
```

## Invariantes de Arquitectura (Reglas de Oro del Sistema)
1. **Respuestas API unificadas:** Toda comunicación del archivo `api.php` hacia JavaScript Vanilla se debe realizar devolviendo cabeceras `header('Content-Type: application/json')` acompañadas de un código de estado HTTP semántico (200 OK, 400 Bad Request, 401 Unauthorized, 403 Forbidden).

2. **Validación de Tiempo Inmutable:** El límite de borrado de 15 minutos se calcula estrictamente en el backend comparando `NOW()` de la base de datos con el campo `created_at` del registro, mitigando alteraciones de reloj en el cliente local.

3. **Control Jerárquico:** Si un comentario padre posee un campo `parent_id IS NOT NULL`, el backend rechazará cualquier intento de asignarlo como `parent_id` de otro registro, garantizando la restricción estricta de 1 nivel de anidación.

## Especificación del Esquema de Datos (SQL de Referencia)

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_banned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    is_locked TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL DEFAULT NULL, -- NULL si es raíz, contiene el id del padre si es respuesta
    content TEXT NOT NULL,
    status ENUM('approved', 'flagged_by_ai', 'hidden') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
);

CREATE TABLE reactions (
    user_id INT NOT NULL,
    comment_id INT NOT NULL,
    type ENUM('like', 'unlike') NOT NULL,
    PRIMARY KEY (user_id, comment_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    target_user_id INT NULL DEFAULT NULL,
    target_comment_id INT NULL DEFAULT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_comment_id) REFERENCES comments(id) ON DELETE CASCADE
);
```

## Tokens de UI (Bootstrap 5 Contextual)
*   **Hilos Bloqueos:** Contenedor con clase `.bg-light.text-muted` y un badge `<span class="badge bg-danger">Hilo Cerrado</span>`. Los botones de respuesta internos se ocultan mediante la utilidad `.d-none` de Bootstrap.
*   **Respuestas Anidadas (Nivel 1):** Clase `.ms-5.border-start.ps-3` para generar una sangría visual limpia respecto al comentario raíz.
*   **Contador de Tiempo Restante:** Atributo de datos HTML5 `data-timestamp` embebido en el elemento del comentario para que un script global de JS actualice o remueva dinámicamente el botón de borrar en el DOM del cliente en cuenta regresiva.
