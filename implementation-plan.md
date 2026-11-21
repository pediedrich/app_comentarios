# Plan de implementacion — App de Comentarios MVP

## Objetivo

Implementar las funcionalidades `F-001` y `F-002` respetando:

- `D-001` a `D-005`.
- `RN-001`, `RN-003`, `RN-004`, `RN-006` y `RN-007`.
- `CA-001` a `CA-006`.
- Ausencia de autenticacion, roles, edicion propia y borrado propio.
- Presencia exclusiva de comentarios principales, respuestas, consulta y moderacion.

## 1. Stack tecnologico

No se definira inicialmente un stack especifico, siguiendo el diseno general y evitando tecnologias no aprobadas.

El proyecto debera incluir posteriormente:

- Backend con persistencia.
- API HTTP.
- Capa de validacion.
- Capa de acceso a datos.
- Pruebas automatizadas.
- Migraciones o creacion del esquema.

Antes de codificar se debe confirmar con el usuario:

- Lenguaje y framework del backend.
- Motor de base de datos.
- Framework o tecnologia del frontend, si existe.
- Convencion exacta de rutas de API.
- Representacion del proceso de moderacion.

## 2. Modelo de datos

Crear una entidad unica de comentario con:

| Campo | Tipo conceptual | Restriccion |
|---|---|---|
| `id` | Identificador unico | Obligatorio y estable |
| `text` | Texto Unicode | Obligatorio, despues de recortar espacios |
| `parent_id` | Referencia opcional | `null` para comentario principal |
| `created_at` | Fecha y hora | Obligatorio |
| `deleted_at` | Fecha opcional | Representa baja logica |
| `moderation_status` | Estado opcional | Solo si el diseno de moderacion lo requiere |

### Reglas del modelo

- `parent_id = null`: comentario principal.
- `parent_id = id principal`: respuesta permitida.
- `parent_id = id de respuesta`: publicacion rechazada (`D-004`).
- Los registros eliminados no deben aparecer en consultas normales (`CA-006`).
- Un comentario no puede eliminarse si existen respuestas asociadas (`RN-007`).

## 3. Modulos y responsabilidades

### Modulo de comentarios principales

Responsable de:

- Recibir el contenido.
- Recortar espacios iniciales y finales.
- Rechazar texto vacio.
- Limitar el contenido a 500 caracteres Unicode.
- Persistir el comentario sin `parent_id`.

Cubre `D-003`, `D-005`, `F-001`, `CA-001` y `CA-002`.

### Modulo de respuestas

Responsable de:

- Recibir `parent_id`.
- Confirmar que el destino existe.
- Confirmar que el destino es comentario principal.
- Rechazar respuestas a respuestas.
- Persistir la respuesta con una sola referencia padre.

Cubre `D-004`, `RN-004`, `RN-006`, `F-001`, `CA-003` y `D-005`.

### Modulo de moderacion

Responsable de:

- Resolver la eliminacion solicitada.
- Confirmar que el comentario no tiene respuestas.
- Aplicar baja logica o eliminacion segun la implementacion adoptada.
- Hacer que el comentario desaparezca de la consulta.

No debe implementar:

- Borrado propio del autor.
- Ventana de cinco minutos.
- Autenticacion de usuarios.
- Gestion de roles.
- Gestion de moderadores.

Cubre `D-002`, `RN-007`, `F-002`, `CA-004`, `CA-005` y `CA-006`.

### Modulo de consulta y orden

Responsable de:

- Consultar comentarios publicados y no eliminados.
- Mostrar primero los mas recientes.
- Usar el identificador descendente como desempate.
- Excluir respuestas anidadas recursivamente.

Cubre `D-001` y `RN-001`.

## 4. Endpoints propuestos

Las rutas concretas se deben aprobar antes de la implementacion.

### Crear comentario principal

- Operacion: alta de comentario.
- Entrada: texto.
- Resultado:
  - Publicacion creada.
  - Error por texto vacio.
  - Error por texto mayor a 500 caracteres Unicode.

### Crear respuesta

- Operacion: alta de respuesta.
- Entrada: texto y `parent_id`.
- Resultado:
  - Respuesta creada.
  - Error si el padre no existe.
  - Error si el padre ya es una respuesta.

### Consultar comentarios

- Operacion: lectura.
- Resultado:
  - Comentarios principales y sus respuestas de un nivel.
  - Orden cronologico descendente.
  - Desempate estable por identificador.

### Eliminar comentario

- Operacion: baja bajo decision de moderacion.
- Resultado:
  - Eliminacion confirmada.
  - Error si el comentario tiene respuestas.
  - Comentario eliminado ausente de consultas posteriores.

No se implementara edicion propia ni autorizacion por autor.

## 5. Persistencia atomica

Para cada alta:

1. Validar el texto en el backend.
2. Validar el padre cuando corresponda.
3. Iniciar una operacion de escritura.
4. Persistir un unico registro completo.
5. Confirmar el resultado de la transaccion.
6. Responder exito solo despues de confirmar la persistencia.

Ante cualquier excepcion o fallo:

- No informar publicacion exitosa.
- No dejar registros parciales.
- Aplicar rollback o equivalente.

Cubre `D-005`.

## 6. Andamiaje

Crear inicialmente:

1. Configuracion del proyecto.
2. Esquema de persistencia.
3. Entidad o modelo de comentario.
4. Repositorio de comentarios.
5. Modulo de validacion.
6. Modulo de comentarios principales.
7. Modulo de respuestas.
8. Modulo de moderacion.
9. Modulo de consulta y orden.
10. Definicion de rutas o endpoints.
11. Pruebas automatizadas.
12. Configuracion de ejecucion local.

No comenzar por frontend ni interfaces adicionales fuera de las funciones requeridas.

## 7. Casos de prueba

### Creacion

- Publicar comentario valido.
- Rechazar texto vacio.
- Rechazar espacios unicamente.
- Rechazar texto mayor a 500 caracteres.
- Confirmar recorte de espacios.
- Confirmar conteo Unicode.
- Confirmar persistencia completa o rechazo completo.

### Respuestas

- Responder a comentario principal.
- Rechazar respuesta a comentario inexistente.
- Rechazar respuesta a otra respuesta.
- Confirmar que una respuesta queda bajo el principal correspondiente.

### Consulta

- Primero el comentario mas reciente.
- Segundo el anterior.
- Desempate estable por identificador.
- No mostrar comentarios eliminados.

### Eliminacion moderada

- Eliminar comentario sin respuestas.
- Rechazar eliminacion si tiene respuestas.
- Confirmar que el eliminado desaparece.
- Confirmar que no existe borrado propio basado en autor.
- Confirmar que no existe limite temporal.

## 8. Criterios de finalizacion

La implementacion estara terminada cuando:

- `[F-001]` permite crear comentarios y respuestas validas.
- `[F-002]` permite la baja moderada conforme a `D-002`.
- `[RN-007]` impide eliminar comentarios con respuestas.
- `[D-001]` produce un orden determinista.
- `[D-003]` protege el texto desde el backend.
- `[D-004]` impide anidacion superior a un nivel.
- `[D-005]` garantiza persistencia atomica.
- Los casos fuera de alcance no han sido implementados.
- Todas las pruebas relevantes pasan.

## 9. Dudas que deben resolverse antes de ejecutar

- Lenguaje y framework seleccionados.
- Motor de base de datos.
- Convencion de identificadores.
- Baja logica versus eliminacion fisica.
- Forma externa de invocar la decision de moderacion.
- Estructura exacta de las respuestas API.
- Ubicacion y formato del frontend, si existe.

Estas dudas no deben resolverse por suposicion.
