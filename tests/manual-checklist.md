# 🧪 Checklist de Pruebas y Aseguramiento de Calidad (QA) — Devioz Classic

Este documento describe la suite de pruebas funcionales, de seguridad y de experiencia de usuario (UX) para validar que el sistema cumple al 100% con los requerimientos académicos antes de su puesta en producción.

---

## 🗄️ 1. Base de Datos y Persistencia

- [ ] **Importación DDL:** Ejecutar `database/schema.sql` en MySQL 8.0+ / MariaDB 10.4+. Verificar que las 10 tablas se crean sin errores de sintaxis y con el charset `utf8mb4_unicode_ci`.
- [ ] **Semilla de Datos (Seed):** Ejecutar `database/seed.sql`. Verificar que se insertan los 2 administradores, los 34 practicantes reales repartidos en las sedes (Cercado, Lima Norte, Villa) y los 5 proyectos iniciales de muestra.
- [ ] **Integridad Referencial (Foreign Keys):**
  - Al borrar un practicante que posee proyecto en cascada, verificar que las tablas dependientes (`check_ins`, `comments`, `deliveries`, `milestones`) respondan conforme a las restricciones `ON DELETE CASCADE`.
  - Al borrar un usuario evaluador, verificar que el proyecto permanezca intacto y la columna `reviewed_by` se actualice a `NULL` (`ON DELETE SET NULL`).

---

## 🔐 2. Autenticación y Seguridad de Sesión

- [ ] **Login con Administrador / Docente:**
  - Email: `elena.rostova@devioz.io` | Clave: `Devioz2026!`
  - Resultado: Inicio de sesión exitoso, redirección automática a `/dashboard` con vista de métricas globales de jefatura.
- [ ] **Login con Practicante:**
  - Email: `jhon.julca@devioz.io` | Clave: `Devioz2026!`
  - Resultado: Redirección a `/dashboard` con vista personalizada de su proyecto activo (`DEV-2026-001`).
- [ ] **Credenciales Incorrectas:**
  - Ingresar clave errónea.
  - Resultado: Mensaje de error genérico (*"Credenciales incorrectas. Verifica tu correo y contraseña."*) sin revelar si el correo existe o no (prevención de enumeración de usuarios). El campo de correo debe conservar su valor previo mediante `old('email')`.
- [ ] **Registro de Practicante:**
  - Navegar a `/register` y completar los datos seleccionando una de las 3 sedes académicas permitidas.
  - Verificar que si las contraseñas no coinciden, se bloquee el registro con alerta descriptiva.
  - Probar registrar un correo ya existente (`users.email` unique); debe rechazar el registro indicando que el correo ya se encuentra en uso.
- [ ] **Cierre de Sesión (Logout):**
  - Enviar petición POST con token CSRF a `/logout`.
  - Resultado: Sesión invalidada, cookie expirada y redirección a `/login` con mensaje informativo.
- [ ] **Protección CSRF:**
  - Intentar enviar un formulario POST alterando o eliminando el campo hidden `_csrf_token`.
  - Resultado: Código HTTP 419 y rechazo inmediato de la mutación.
- [ ] **Session Fixation:**
  - Verificar que al autenticarse se invoque `session_regenerate_id(true)`, generando un ID de sesión nuevo e invalidando el anterior.

---

## 🛡️ 3. Control de Acceso Basado en Roles (RBAC)

- [ ] **Aislamiento del Practicante:**
  - Estando autenticado como practicante (`developer`), intentar ingresar por URL a `/admin/review`, `/admin/assignments`, `/admin/evaluations` o `/projects/create`.
  - Resultado: Pantalla de error HTTP 403 (*"Acceso denegado. Se requieren privilegios de Administrador"*).
- [ ] **Redirección de Proyectos:**
  - Un practicante que intente acceder a la lista general `/projects` debe ser redirigido de forma transparente a su espacio de trabajo `/my-project`.
- [ ] **Visitantes Anónimos:**
  - Intentar acceder a `/dashboard` o `/my-project` sin haber iniciado sesión.
  - Resultado: Redirección a `/login` con mensaje de advertencia.

---

## 💼 4. Reglas de Negocio Estrictas

- [ ] **1 Practicante = 1 Proyecto Activo:**
  - Ir a `/projects/create` como docente.
  - En el selector de desarrolladores, verificar que únicamente aparezcan los practicantes que no tienen ningún proyecto activo en curso.
  - Si un usuario ya tiene un proyecto con estado `assigned`, `in_progress` o `pending_review`, el sistema debe rechazar cualquier intento de asignación duplicada.
- [ ] **Liberación tras Culminación:**
  - Cuando un proyecto alcanza el estado `completed`, el practicante queda libre y vuelve a figurar como disponible en el panel de asignaciones (`/admin/assignments`).

---

## 🚀 5. Espacio de Trabajo del Practicante (`/my-project`)

- [ ] **Cronograma de Hitos:**
  - Verificar la visualización secuencial de hitos con sus fechas límites programadas.
  - Si una fecha límite es menor a la fecha actual y el hito no está completado, debe mostrarse el badge rojo *"Atrasado"*.
- [ ] **Subida de Entregas:**
  - Hacer clic en *"Subir Entrega"* para abrir el modal interactivo.
  - Enviar evidencia con título, tipo (parcial o final), descripción, enlace de video (Loom/YouTube) y adjuntos múltiples (ZIP o PDF).
  - Verificar que se cree el registro en la tabla `deliveries` con estado `pending` y que el hito cambie a `in_progress`.
- [ ] **Completar Hito:**
  - Hacer clic en *"Marcar Completado"* en un hito.
  - Confirmar el diálogo nativo `data-confirm`.
  - Verificar que el porcentaje de avance ponderado se recalcule automáticamente.
  - Si se completa el último hito, el proyecto debe transicionar automáticamente a `pending_review`.
- [ ] **Reporte Diario Standup (Check-in):**
  - Seleccionar ventana (Mañana, Mediodía o Tarde).
  - Ingresar horas trabajadas, descripción de actividades y posibles bloqueos.
  - Enviar y verificar que el reporte se refleje en el historial del día.
  - Intentar enviar nuevamente la misma ventana en el mismo día; debe actualizar el registro existente sin duplicar filas (llave única `unique_user_window_date`).
- [ ] **Hilo de Comentarios:**
  - Publicar un mensaje para el docente y verificar que aparezca en el feed cronológico con fecha, hora y rol.

---

## 📋 6. Panel y Tablero Kanban de Revisión Docente (`/admin/review`)

- [ ] **Visualización en 4 Columnas:**
  - Columna 1: Pendiente de Revisión (`pending_review`, `assigned`).
  - Columna 2: En Revisión (`in_review`).
  - Columna 3: Aprobado (`approved`, `completed`).
  - Columna 4: Observado / Rechazado (`rejected`).
- [ ] **Indicadores Visuales en Tarjetas:**
  - Las tarjetas deben exhibir iconos para video (📹), documentación (📄), demo en vivo (🌐) y repositorio (📦) solo si el proyecto cuenta con dichos recursos.
- [ ] **Drag & Drop Interactivo:**
  - Arrastrar una tarjeta de una columna a otra con el ratón.
  - Verificar que SortableJS emita una petición asíncrona (Fetch API) a `/admin/review/{id}/status` con cabecera `X-CSRF-TOKEN`.
  - Verificar en base de datos que la columna `status` del proyecto se actualice al instante sin requerir recargar la página.
- [ ] **Aprobación Formal y Publicación:**
  - Ingresar al detalle de un proyecto en revisión, asignar calificación final (escala 0 a 10) y hacer clic en *"Aprobar y Publicar"*.
  - Verificar que el estado cambie a `approved`, que `is_public_in_gallery` pase a `1` y que el practicante reciba un mensaje interno de confirmación.
- [ ] **Observación / Rechazo:**
  - Hacer clic en *"Observar / Rechazar"*, ingresar el motivo de observación obligatorio (mínimo 10 caracteres) y confirmar.
  - Verificar que el estado pase a `rejected`, que el motivo quede persistido en `rejection_reason` y que se genere un mensaje urgente para el alumno.

---

## 📊 7. Calificaciones y Evaluaciones Académicas (`/admin/evaluations`)

- [ ] **Bandeja de Entregas Pendientes:**
  - Comprobar que liste las evidencias enviadas por los alumnos pendientes de dictamen.
- [ ] **Modal de Calificación:**
  - Abrir el modal, registrar una nota numérica (ej: `9.5`) y retroalimentación técnica cualitativa.
  - Al guardar, la entrega debe cambiar a `approved`, el hito vinculado marcarse como `completed` y crearse el registro formal en la tabla `evaluations`.

---

## 📁 8. Gestión Segura de Archivos (Uploads)

- [ ] **Validación MIME Estricta:**
  - Intentar subir un script ejecutable `.php`, `.exe` o `.bat` renombrado con extensión `.pdf`.
  - El validador basado en `finfo` debe inspeccionar los bytes del archivo y rechazar la carga con una excepción de seguridad.
- [ ] **Límite de Tamaño:**
  - Probar subir un archivo que supere los 500 MB configurados en `.env`. Debe ser rechazado limpiamente sin agotar la memoria del servidor.
- [ ] **Integridad SHA-256:**
  - Verificar en la tabla `project_files` que cada archivo almacenado cuente con su hash SHA-256 generado para auditoría.

---

## 🎨 9. Interfaz, Tema y Experiencia de Usuario (UI/UX)

- [ ] **Conmutador de Tema (Dark / Light):**
  - Hacer clic en el icono de sol/luna en la barra superior.
  - Verificar que la paleta cambie entre fondo oscuro (`#09090b`) y fondo claro (`#fafafa`).
  - Recargar la página (F5) y comprobar que el tema elegido se mantenga gracias a `localStorage.getItem('devioz_theme')`.
- [ ] **Auto-Dismiss de Mensajes Flash:**
  - Provocar un mensaje de éxito o advertencia.
  - Verificar que a los 4 segundos la alerta inicie su transición de desvanecimiento (`fade-out`) y se remueva del DOM.
- [ ] **Diseño Responsivo:**
  - Redimensionar el navegador a tamaño móvil (< 768px).
  - Verificar que la barra lateral se oculte y aparezca únicamente al presionar el botón hamburguesa, desplegándose como drawer con fondo oscurecido (`backdrop-filter`).
- [ ] **Galería Pública:**
  - Navegar a `/gallery` sin iniciar sesión.
  - Comprobar que los proyectos aprobados se muestren con su portada, stack tecnológico y enlace de detalle público `/gallery/{slug}`.
  - Al abrir un proyecto público, verificar que el contador `views_count` incremente en 1 unidad.
