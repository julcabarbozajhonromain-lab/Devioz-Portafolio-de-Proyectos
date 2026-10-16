# Acceso y estados de Devioz

**Estado:** verificado en codigo el 16 de octubre de 2026. El sistema tiene dos roles (`admin`, `developer`); todavia no existe un rol de cliente ni aislamiento por organizacion.

## Matriz de acceso actual

| Recurso / accion | Visitante | Desarrollador asignado | Otro desarrollador | Administrador |
| --- | --- | --- | --- | --- |
| Galeria publica | Leer proyectos publicados | Leer | Leer | Leer |
| Ficha interna de proyecto | No | Solo su proyecto | No | Leer todos |
| Crear, editar, asignar, archivar | No | No | No | Si |
| Enviar proyecto a revision | No | Su proyecto | No | Si |
| Completar hitos / enviar entregas | No | Su proyecto | No | No |
| Dictaminar QA | No | No | No | Si, con evidencia |
| Cambiar estado / aprobar | No | No | No | Si, transicion valida y QA aprobado para `approved` |
| Descargar evidencia de entrega | No | Su proyecto | No | Si |
| Comentar / previsualizar / CoPilot | No | Su proyecto | No | Si |
| Configuracion y usuarios | No | No | No | Si |
| Portal y aceptacion de cliente | No | No | No | No implementado |

`ProjectPolicy` concentra permisos sobre recursos de proyecto y deniega por defecto. `ProjectStatusTransition` valida transiciones de estado y aplica el cambio con una condicion sobre el estado anterior para detectar cambios concurrentes. Las rutas siguen siendo responsables de autenticar y autorizar al actor. Cuando se agregue el portal de clientes, se necesitara `organization_id` y membresia real; `client_name` no proporciona aislamiento.

## Entidades y estados existentes

| Entidad | Estados registrados | Funcion comprobada |
| --- | --- | --- |
| Proyecto | `draft`, `assigned`, `in_progress`, `pending_review`, `in_review`, `approved`, `rejected`, `completed`, `archived` | Envio a revision y finalizacion de hitos pueden mover el proyecto a `pending_review`. |
| Hito | `pending`, `in_progress`, `completed` | El desarrollador asignado completa hitos. |
| Entrega | `pending`, `approved`, `rejected` | QA dictamina entregas con base en evaluacion y evidencia. |
| Auditoria QA | `in_review`, `needs_changes`, `passed`, `blocked` | El Quality Gate mantiene tambien `not_run`, `pending`, `passed`, `failed`, `blocked`. |
| Aceptacion de cliente | No existe | `approved` no representa aceptacion contractual. |
| Release / despliegue | No existe | `completed` no demuestra que haya ocurrido un despliegue. |

## Transiciones de proyecto implementadas

| Desde | Hacia | Actor / condicion |
| --- | --- | --- |
| `draft` | `assigned` | Admin asigna un responsable. |
| `assigned` | `in_progress` | Desarrollador asignado envia la primera entrega. |
| `assigned` | `pending_review` | Se conserva para el flujo actual de envio aunque no haya evidencia de entrega final; requiere definicion de negocio. |
| `in_progress` | `pending_review` | Desarrollador asignado envia el proyecto o completa todos los hitos. |
| `pending_review` | `in_review` | Admin inicia revision desde Kanban. |
| `pending_review` / `in_review` | `approved` | Admin; exige auditoria QA aprobada con score. No equivale a aceptacion del cliente ni a despliegue. |
| `pending_review` / `in_review` | `rejected` | Admin registra observacion. |
| `rejected` | `in_progress` / `pending_review` | Desarrollador asignado reanuda trabajo o reenvia. |
| `assigned` / `in_progress` | `draft` | Admin desasigna el proyecto y lo devuelve al pool. |
| `pending_review` / `in_review` / `rejected` / `approved` / `completed` | `archived` | Admin archiva preservando el registro. |
| `archived` | `assigned` | Admin reasigna el proyecto. |

El mapa de transiciones vive en `app/Services/ProjectStatusTransition.php`. El tablero QA solo permite como destinos `pending_review`, `in_review`, `approved` y `rejected`; archivo y asignacion usan sus acciones administrativas. La aprobacion por tablero o ficha requiere una auditoria `passed` con Quality Gate `passed`. `completed` permanece como estado historico, sin una nueva transicion hacia el porque todavia no hay un proceso definido de cierre o despliegue.

## Maquinas futuras separadas

1. **QA:** evaluacion basada en evidencias, hallazgos, severidad, remediacion y Quality Gate. El dictamen QA no debe confundirse con aceptacion contractual.
2. **Aceptacion de cliente:** futura secuencia `awaiting_client` hacia `accepted`, `changes_requested` o `declined`, despues de QA aprobado.
3. **Release:** futura secuencia `ready` hacia `deploying`, `deployed` o `failed`, con version, commit, aprobador y rollback como evento auditable.

No crear estados para aceptacion o despliegue hasta implementar las operaciones que los ejecuten.

## Pendientes de negocio y arquitectura

- Definir si enviar un proyecto en estado `assigned` exige una entrega final o hitos completos. El comportamiento actual se conserva para evitar cambiar el flujo sin validar datos y pantallas.
- Acordar que significa `completed` y separar cierre, aceptacion del cliente y despliegue.
- Revisar la compatibilidad de archivos de entregas antiguos antes de dejar toda evidencia como privada.
- El esquema inicial contiene sentencias `DROP TABLE`; para bases con informacion se deben usar migraciones aditivas y versionadas.
- No conceder acceso de cliente hasta contar con rol, organizacion, membresia y politicas de aislamiento.
