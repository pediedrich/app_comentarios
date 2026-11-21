# Registro de configuración y despliegue — App de Comentarios

<!-- Alumno: Diedrich Pablo Enrique -->
<!-- Herramienta usada: se completa en la sección 1. -->

## 1. Herramienta y modo de trabajo

- Herramienta: OpenCode (model:Big Pickle)
- Modo para planificar: Build
- Modo para ejecutar: Build
- En qué momento cambiaste de modo y por qué: Hasta el momento cambie de modo, todo fue construido con el mismo modo.

## 2. AGENTS.md destilado   

### Reglas
- Los comentarios se muestran ordenados cronológicamente, del más reciente al más antiguo (origen: RN-001)
- El campo de texto del comentario no puede enviarse vacío y tiene un límite máximo de 500 caracteres (origen: RN-003)
- Los usuarios pueden responder directamente a un comentario existente con un nivel de anidación (origen: RN-004)
- Toda respuesta pertenece siempre a un comentario (origen: RN-006)
- La eliminación es una decisión de moderación y no corresponde al borrado propio del autor ni está limitada a una ventana de cinco minutos (origen: D-002)
- Permitir la eliminación solo si el comentario no tiene respuestas (origen: RN-007)
- El backend es la autoridad de validación del texto: recorta espacios iniciales y finales, rechaza texto vacío y limita el contenido a 500 caracteres Unicode (origen: D-003)
- Una respuesta siempre referencia al comentario principal y no puede responder a otra respuesta (origen: D-004)
- Toda solicitud de alta se persiste como una unidad atómica: solo se informa éxito después de confirmar la persistencia (origen: D-005)

### Regla que acota alcance

- Solo se implementan los features F-001 (Crear Comentarios) y F-002 (Eliminar Comentarios); no se implementan votos, notificaciones en tiempo real, reacciones, likes, (origen: F-001, F-002, sección 4 de design.md)
- Las reglas de creación, respuesta y consulta se aplican a los módulos de comentarios principales, respuestas y consulta/orden. La eliminación se aplica exclusivamente al módulo de moderación (origen: sección 3 de design.md)
- Las reglas técnicas anteriores no deben extenderse a módulos ajenos a comentarios, respuestas, consulta ni moderación.

### Lo que le pediste al agente que NO haga

- No agregar funcionalidades fuera de las features definidas en requirement.md (origen: F-001, F-002)
- No incorporar edición propia, autenticación de usuarios ni borrado propio del autor, ya que quedan fuera del alcance técnico vigente (origen: D-002, sección 4 de design.md)
- No nombrar tecnologías específicas en el plan de implementación si design.md no las define (origen: design.md, sección 1)
- No resolver las dudas técnicas pendientes sin consultar primero (origen: design.md, sección 5)

## 3. Prompt de Plan Mode

<!-- Tu encargo, con referencias explícitas a los documentos y al formato esperado. -->
```
Leer app_comentarios/specs/001-publicar-comentarios/requirement.md y app_comentarios/specs/001-publicar-comentarios/design.md.
Como design.md prevalece sobre requirement.md ante cualquier discrepancia,
con base en los criterios de aceptación vigentes, las reglas de negocio RN-001/007
y las decisiones D-001/005,
generar un plan de implementación con: stack tecnológico propuesto, modelo de datos,
endpoints de API, pasos de andamiaje y criterios de prueba. Entregar el plan en markdown
con referencias explícitas a cada ID de requisito y decisión.
```

## 4. Revisión del plan

<!-- Tus correcciones. Para cada una: qué pediste cambiar, por qué y qué cambió. -->
### Corrección 1
- Pedí cambiar: eliminar del alcance operativo cualquier edición propia o borrado propio del autor, y dejar la eliminación bajo decisión de moderación.
- Motivo: atento al alcance del contrato de la primer etapa.-
- Resultado: se adoptó D-002 como decisión vigente y se dejó RN-007 como única restricción de borrado asociada a respuestas.

### Corrección 2
- Pedí cambiar: idem a la corrección 1.
- Motivo: idem.
- Resultado: sin cambios pendientes por ahora.

### Corrección 3
- Pedí cambiar: idem a la corrección 1.
- Motivo: idem.
- Resultado: sin cambios pendientes por ahora.

## 5. Ejecución del andamiaje

<!-- Qué archivos o módulos quedaron tras ejecutar el plan revisado. Estructura, no lógica. -->
- requirement.md — requisitos, features y criterios de aceptación.
- design.md — arquitectura general, ADR, módulos, fuera de alcance y dudas pendientes.
- agents.md — este registro de configuración y despliegue.
- Módulo de comentarios principales — previsto en el diseño para recibir, validar y persistir comentarios sin padre.
- Módulo de respuestas — previsto en el diseño para mantener un único nivel de anidación.
- Módulo de moderación — previsto en el diseño para resolver la baja de comentarios con RN-007.
- Módulo de consulta y orden — previsto en el diseño para consultar primero los registros más recientes.

### Desvíos entre el plan revisado y lo que se generó

- No se generó código ni andamiaje de software ejecutable porque esta sesión se limitó a documentos de requisitos y diseño.
- Sin desvíos entre el plan revisado y lo generado: no existió un plan de implementación formal ni una ejecución posterior a revisar.

## 6. Contraste con el sprint de la Semana 1

<!-- Volvé a tu experiencia de la Semana 1 y nombrá problemas concretos. -->
- Problema que pasó en la Semana 1: Codigo creado por conocimiento propio y lenguaje a eleccion del modelo
- Regla que lo habría evitado: No alucinar. Siempre preguntarme ante alguna duda o falta de contexto.
- Evidencia de que hoy funciona: Contexto amplio y limitaciones/reglas.

## 7. Conclusión

<!-- Dos o tres frases. ¿Qué cambia concretamente en tu próxima tarea con el agente? -->
- En la próxima tarea, partiré de los documentos ya definidos (requirement.md y design.md) para pedir al agente un plan de implementación con stack, modelo de datos y endpoints, en lugar de empezar desde cero.
- Cualquier duda o ajuste se consultará juntos y se irá completando punto a punto según avancemos.