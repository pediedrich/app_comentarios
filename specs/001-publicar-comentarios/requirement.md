# Requisitos del Sistema - App de Comentarios Básica (MVP)

## 1. Descripción del Proyecto
Plataforma de comentarios que permite la interacción entre usuarios.

## 2. Features
* **F-001:** Crear Comentarios
* **F-002:** Eliminar Comentarios

## 3. Criterios de Aceptación
* **CA-001:** El sistema debe permitir a un usuario escribir y publicar un comentario asociado a un comentario anterior (F-001).
* **CA-002:** El comentario no puede publicarse si el campo de texto está vacío ni exceder los 500 caracteres (F-001, RN-003).
* **CA-003:** El sistema debe permitir responder a un comentario existente con un nivel de anidación, y toda respuesta debe pertenecer siempre a un comentario (F-001, RN-004, RN-006).
* **CA-004:** La eliminación de comentarios corresponde a una decisión de moderación (F-002).
* **CA-005:** Un comentario puede eliminarse sin una ventana de tiempo limitada después de su publicación (F-002, RN-005).
* **CA-006:** Una vez eliminado, el comentario ya no debe mostrarse en la lista de comentarios (F-002).

## 4. Reglas de Negocio (RN)
* **RN-001:** Los comentarios deben mostrarse ordenados cronológicamente (del más reciente al más antiguo) debajo del comentario anterior.
* **RN-003:** El campo de texto del comentario no puede enviarse vacío y tendrá un límite máximo de 500 caracteres (F-001).
* **RN-004:** Los usuarios pueden responder directamente a un comentario existente (un nivel de anidación) (F-001).
* **RN-005:** La eliminación es una decisión de moderación y no corresponde al borrado propio del autor ni está limitada a una ventana de cinco minutos (F-002).
* **RN-006:** Una respuesta siempre pertenece a un comentario (F-001).
* **RN-007:** Un comentario no puede ser eliminado si tiene respuestas.- (F-002).

