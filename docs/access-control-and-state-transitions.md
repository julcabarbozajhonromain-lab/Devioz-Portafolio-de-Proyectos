# Acceso y estados de Devioz

**Estado:** baseline verificado en código el 16 de octubre de 2026. Esta matriz describe los roles actuales (`admin`, `developer`) y separa lo existente de lo propuesto. No hay rol de cliente ni modelo `organization_id` todavía.

## Matriz de permisos actual y objetivo

| Recurso / acción | Visitante | Desarrollador asignado | Otro desarrollador | Administrador | Estado / regla |
| --- | --- | --- | --- | --- | --- |
| Galería pública | Leer | Leer | Leer | Leer | Público; debe exponer solo los campos publicados. |
| Ficha interna del proyecto | No | Leer su proyecto | No | Leer todos | La ficha comprueba asignación al usuario. |
| Crear, editar, asignar, archivar proyecto | No | No | No | Sí | Rutas administrativas. |
| Enviar proyecto a revisión | No | Sí, su proyecto | No | Sí | La ruta actual permite admin o el developer asignado. |
| Completar hito / enviar entrega | No | Sí, de su proyecto | No | No | Debe validar que hito y proyecto pertenecen al usuario. |
| Dictaminar QA | No | No | No | Sí | Admin; compuerta basada en evidencia. |
| Aprobar / cambiar estado del proyecto | No | No | No | Sí | Admin y solo con auditoría aprobada para `approved`. Las transiciones aún requieren restringirse. |
| Descargar evidencia de entrega | No | Sí, de su proyecto | No | Sí | Controlador valida propiedad; acepta compatibilidad con archivos antiguos públicos. |
| Comentar en proyecto | No | Sí, su proyecto | No | Sí | Verificación de pertenencia en el controlador. |
| Previsualizar / usar CoPilot | No | Sí, su proyecto | No | Sí | Preview valida propiedad. CoPilot debe usar la misma política; verificarla antes de devolver datos del proyecto. |
| Configuración y usuarios | No | No | No | Sí | Solo administrador. |
| Portal y aceptación de cliente | No | No | No | No | No implementado: no hay usuario/rol de cliente ni organización. |

### Regla de autorización propuesta

Centralizar en `ProjectPolicy` las acciones `view`, `submit`, `comment`, `download`, `manage`, `audit` y `approve`. Denegar por defecto. Para recursos ligados a un proyecto, exigir administrador o desarrollador asignado; nunca confiar solo en que la ruta requiera login ni en IDs enviados por el navegador. Cuando se agreguen clientes, sumar `organization_id` y membresía al modelo; no simular tenancy con `client_name`.

## Estados que existen hoy

| Entidad | Estados registrados | Comportamiento comprobado |
| --- | --- | --- |
| Proyecto | `draft`, `assigned`, `in_progress`, `pending_review`, `in_review`, `approved`, `rejected`, `completed`, `archived` | La entrega a revisión o completar todos los hitos establece `pending_review`. El endpoint Kanban admin acepta cualquier estado de la lista; no aplica una tabla de transiciones. |
| Hito | `pending`, `in_progress`, `completed` | El desarrollador asignado puede marcarlo completo; completar todos los hitos mueve el proyecto a revisión. |
| Entrega | `pending`, `approved`, `rejected` | Se crea pendiente; el dictamen QA puede aprobar o rechazar y completar el hito. |
| Auditoría QA | `in_review`, `needs_changes`, `passed`, `blocked` | Se mantiene separada de la aprobación del proyecto. La compuerta además conserva `not_run`, `pending`, `passed`, `failed`, `blocked`. |
| Aceptación del cliente | — | No implementada. `approved` no debe interpretarse como aceptación contractual del cliente. |
| Release / despliegue | — | No implementado. `completed` no prueba que exista un despliegue. |

## Transiciones recomendadas para el MVP

### Proyecto

| Desde | Hacia | Quién | Condición |
| --- | --- | --- | --- |
| `draft` | `assigned` | Admin | Proyecto con responsable asignado. |
| `assigned` | `in_progress` | Admin / developer asignado | Inicio confirmado. |
| `in_progress` | `pending_review` | Developer asignado | Entrega final presentada o hitos requeridos completos. |
| `pending_review` | `in_review` | Admin | Revisión iniciada. |
| `in_review` | `rejected` | Admin | Observaciones documentadas; el proyecto vuelve a trabajo activo según política. |
| `in_review` | `approved` | Admin | Quality Gate aprobado y evidencia de entrega final. No significa aceptación del cliente ni despliegue. |
| `approved` | `completed` | Admin | Cierre administrativo explícito; no usar como sinónimo de deploy. |
| estados terminales | `archived` | Admin | Retención y auditoría preservadas. |

Las transiciones de regreso a `in_progress`, reapertura y cancelación deben decidirse con negocio antes de habilitarlas. Cualquier transición no listada se rechaza.

### QA, aceptación y despliegue

1. QA: `in_review` → `passed`, `needs_changes` o `blocked`.
2. Aceptación del cliente (futura): solo después de QA `passed`; `awaiting_client` → `accepted`, `changes_requested` o `declined`.
3. Release (futura): solo después de aceptación o una excepción aprobada; registrar versión y commit antes de `ready` → `deploying` → `deployed` o `failed`; rollback se registra como evento propio.

No implementar todavía la máquina de aceptación ni la de despliegue mientras no exista el portal/operación que las ejecuta. Mantenerlas separadas en el modelo cuando ese alcance sea aprobado.

## Hallazgos para el siguiente cambio

- Alinear las comprobaciones por dueño del recurso con `ProjectPolicy`, incluyendo CoPilot y cualquier endpoint nuevo.
- Restringir el endpoint Kanban a transiciones válidas, con un `UPDATE ... WHERE status = :expected_status` para evitar carreras.
- Mantener evidencia de QA privada; revisar la ruta de compatibilidad de archivos legados antes de retirarla.
- El esquema inicial contiene `DROP TABLE`; usar únicamente migraciones aditivas/versionadas para cambios en una base con datos.
- La matriz no concede permisos de cliente porque hoy no existe ese rol ni aislamiento multiempresa.
