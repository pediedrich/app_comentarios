# Requisitos del Sistema - App de Comentarios Básica (MVP)

## 1. Descripción del Proyecto
Plataforma de comentarios que permite la interacción entre usuarios registrados mediante respuestas y reacciones. Cuenta con un sistema robusto de moderación automatizada por IA y herramientas administrativas de control comunitario.

## 2. Reglas de Negocio (RN)
* **RN-001:** Un usuario debe estar logueado, para escribir un comentario con su nombre y el contenido del mensaje.
* **RN-002:** Los comentarios deben mostrarse ordenados cronológicamente (del más reciente al más antiguo) debajo del artículo.
* **RN-003:** El campo de texto del comentario no puede enviarse vacío y tendrá un límite máximo de 500 caracteres.
* **RN-004:** El administrador del sitio debe poder eliminar comentarios inapropiados desde un panel de control simple.
* **RN-005:** Los usuarios pueden responder directamente a un comentario existente (un nivel de anidación).
* **RN-006:** Un usuario puede editar o eliminar su propio comentario dentro de los primeros 15 minutos posteriores a su publicación.
* **RN-007:** El usuario debe iniciar sesión (vía Email o Google) para poder utilzar el sistema.
* **RN-008:** Los comentarios anónimos no están permitidos.
* **RN-009:** Una respuesta siempre pertenece a un comentario.
* **RN-010:** El administrador puede banear a un usuario desde un panel de administración simple, por el mal uso de la plataforma o comportamientos inadecuados.

## 3. Features
* **F-001:** Comentarios y Respuestas Anónimas (Contraste con RN-008)

Permite a los usuarios publicar comentarios sin necesidad de iniciar sesión, seleccionando la opción "Publicar como Anónimo" y asignando un identificador temporal generado por el sistema (ej. Usuario Anónimo #4821).

* **F-002:** Ordenamiento Dinámico y Popularidad (Contraste con RN-002)

Añade un selector de ordenamiento para que los usuarios puedan alternar entre vista cronológica, comentarios más votados (Top / Trending) y discusiones más activas, en lugar de mostrar exclusivamente los más recientes primero.

* **F-003:** Edición y Eliminación Sin Límite de Tiempo (Contraste con RN-006)

Otorga libertad total a los usuarios para editar o eliminar sus propios comentarios en cualquier momento posterior a su publicación, manteniendo un historial de versiones visibles públicamente si el comentario ha sido editado.

* **F-004:** Anidación Múltiple / Hilos Profundos (Contraste con RN-005)

Habilita múltiples niveles de respuestas encadenadas (sub-respuestas ilimitadas o con un límite configurable de hasta 5 niveles) para permitir debates en árbol estructurados, en lugar de un único nivel de respuesta.

* **F-005:** Formato Enriquecido y Contenido Multimedia (Contraste con RN-003 / MOD-001)

Permite el uso de formato Markdown básico en el campo de texto (negritas, cursivas, listas) y la inserción de imágenes o GIFs embebidos, ampliando el límite de caracteres más allá de los 500 iniciales.

* **F-006:** Auto-gestión y Suspensión Temporal (Contraste con RN-010 y MOD-004)

Introduce un sistema de sanciones progresivas automatizadas (advertencias previas y bloqueos temporales automáticos por 24 o 48 horas) gestionadas por la IA antes de aplicar un baneo permanente manual por parte del administrador.
## 3. Moderación y Seguridad (MOD)
* **MOD-001:** Antes de insertar un comentario o respuesta en la base de datos, el backend debe enviar el texto a un microservicio/API de lenguaje de IA para analizar palabras prohibidas y enlaces sospechosos (Phishing/Malware).
* **MOD-002:** Los usuarios pueden reportar comentarios específicos o perfiles de otros usuarios por mala conducta que infrinjan las normas comunitarias.
* **MOD-003:** Herramientas para banear usuarios, bloquear hilos de discusión y revisar comentarios reportados.
* **MOD-004:** Si un usuario tiene esta baneado, el sistema debe destruir su sesión automáticamente y bloquearle el acceso de escritura (RN-010).
* **MOD-005:** Si la IA detecta contenido inapropiado, el comentario se guardará automáticamente en estado `flagged_by_ai` y no se mostrará públicamente hasta que un administrador lo apruebe (RN-004, MOD-001).
* **MOD-006:** Los administradores pueden bloquear un hilo de discusión completo. Al bloquearse, se congela el hilo: se pueden seguir leyendo los comentarios pero se deshabilitan las acciones de comentar y responder.
* **MOD-007:** El sistema debe verificar que el usuario está logueado antes de permitir: comentar, responder, dar like/unlike, reaccionar, reportar o eliminar (RN-001, RN-005, RN-008).ahhh 

## 4. Requisitos No Funcionales (RNF)
* **RNF-001: Interfaz Limpia:** El diseño debe adaptarse a dispositivos móviles (Responsivo).
* **RNF-002: Rendimiento:** Los comentarios deben cargar en menos de 1 segundo al abrir la página.
* **RNF-003: Almacenamiento:** Los datos se guardarán en una base de datos relacional (SQLite/PostgreSQL).