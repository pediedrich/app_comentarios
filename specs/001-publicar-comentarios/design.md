# design.md — App de Comentarios

<!-- Alumno: Diedrich Pablo -->
<!-- requirements.md de referencia: specs/001-publicar-comentarios/requirements.md -->

## 1. Arquitectura general

El sistema se organiza en módulos para crear comentarios principales, responder a ellos, moderar su eliminación y consultar los resultados. El backend aplica las reglas de negocio antes de persistir y devolver los comentarios. La consulta presenta primero los más recientes y usa el identificador como desempate estable.

## 2. Decisiones técnicas (ADR)

### D-001 — Orden determinista de los comentarios
- Contexto: Los comentarios deben mostrarse cronológicamente, del más reciente al más antiguo (RN-001).
- Decisión: Ordenar por fecha de creación descendente y, cuando dos registros tengan la misma fecha, por identificador descendente.
- Alternativas consideradas: ordenar únicamente por fecha o únicamente por identificador.
- Justificación: conserva el orden cronológico requerido y garantiza un resultado estable cuando las fechas coinciden.
- Requisito relacionado: RN-001, F-001, CA-001

### D-002 — Eliminación bajo decisión de moderación
- Contexto: La eliminación corresponde a la moderación y no debe convertirse en una acción de autoservicio del autor.
- Decisión: No incluir borrado propio ni una ventana de cinco minutos. La baja de un comentario queda a consideración del moderador; se conserva la regla RN-007 de no eliminar un comentario que tenga respuestas.
- Alternativas consideradas: permitir que el autor elimine su comentario dentro de un plazo fijo.
- Justificación: evita introducir autenticación de usuarios y una política de borrado por tiempo que no forman parte del alcance de v1.
- Requisito relacionado: F-002, RN-007, CA-006

### D-003 — Validación del texto
- Contexto: El texto no puede estar vacío y tiene un máximo de 500 caracteres (RN-003, CA-002).
- Decisión: El backend es la autoridad de validación. Recorta espacios al inicio y al final, rechaza el texto si queda vacío y limita el contenido a 500 caracteres Unicode. La validación del frontend, si existe, es solo una ayuda de interfaz.
- Alternativas consideradas: validar únicamente en el frontend o contar bytes en lugar de caracteres.
- Justificación: el backend protege la regla ante cualquier cliente y el conteo de caracteres evita que textos con caracteres multibyte tengan un límite distinto.
- Requisito relacionado: RN-003, F-001, CA-002

### D-004 — Modelo de respuestas y un nivel de anidación
- Contexto: Una respuesta debe pertenecer a un comentario y solo se admite un nivel de anidación (RN-004, RN-006, CA-003).
- Decisión: Un comentario principal no tiene comentario padre (`parent_id` nulo); una respuesta referencia al comentario principal. Al crear una respuesta, el backend verifica que el comentario referenciado sea principal y rechaza responder a otra respuesta.
- Alternativas consideradas: permitir respuestas sin padre o habilitar anidación recursiva.
- Justificación: la referencia al padre representa la pertenencia y la comprobación al crear cada respuesta impide superar un nivel.
- Requisito relacionado: RN-004, RN-006, F-001, CA-003

### D-005 — Alta atómica
- Contexto: Una publicación debe persistirse completa o no persistirse.
- Decisión: Cada solicitud de alta se trata como una unidad atómica: el sistema informa éxito solo después de confirmar la persistencia; ante un fallo no debe quedar una publicación parcial.
- Alternativas consideradas: informar éxito antes de confirmar la persistencia o conservar escrituras parciales.
- Justificación: evita que la respuesta del sistema indique una publicación inexistente o incompleta.
- Requisito relacionado: F-001, CA-001, CA-003

## 3. Estructura de módulos

- Módulo de comentarios principales — recibe, valida y persiste comentarios que no están asociados a otro comentario; aplica RN-003.
- Módulo de respuestas — permite responder a un comentario principal existente y valida que el destino no sea ya una respuesta; así garantiza un único nivel de anidación (RN-004, RN-006).
- Módulo de moderación — resuelve la baja de comentarios según decisión del moderador y respeta la restricción sobre comentarios con respuestas (F-002, RN-007).
- Módulo de consulta y orden — devuelve los comentarios del más reciente al más antiguo, con desempate por identificador (RN-001).

## 4. Fuera de alcance técnico

- Autenticación de usuarios y borrado propio — no forman parte de la v1 definida para este diseño.
- Gestión de roles de usuario — crear, asignar o administrar roles, incluido el rol de moderador, no forma parte del alcance técnico de la v1.
- Notificaciones en tiempo real — no se requieren para publicar, responder o consultar comentarios.
- Votos, "me gusta" u otras reacciones — no forman parte de las funciones de comentarios y moderación.

## 5. Supuestos adoptados

- La eliminación es una decisión de moderación, no una acción del autor, y no está limitada a los primeros cinco minutos.
- No se define aquí un mecanismo de autenticación ni la implementación de permisos de moderación; se asume que la decisión de moderación proviene del proceso de moderación de la plataforma.
- La validación autoritativa del contenido ocurre en el backend; el frontend no puede sustituirla.
