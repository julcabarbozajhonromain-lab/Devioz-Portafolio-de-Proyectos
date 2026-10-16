# Flujo de calidad y auditoría QA

## Alcance

Devioz registra dictámenes internos y evidencias por entrega. ISO/IEC 25010:2023 proporciona el modelo de calidad del producto y OWASP Top 10:2025 ayuda a organizar la revisión de riesgos web. Ninguno de estos registros representa una certificación emitida por ISO u OWASP.

El score Devioz (0–100) es el promedio de las nueve valoraciones manuales (0–4) de ISO/IEC 25010, convertido a escala porcentual. Cada valor necesita evidencia escrita. El umbral interno inicial de 80/100 es una política del producto; no procede de ISO. El score por sí solo no aprueba la compuerta.

## Compuerta de aprobación

Una auditoría pasa únicamente cuando se cumplen todas estas condiciones:

- Las nueve características ISO tienen valoración y evidencia.
- Las diez categorías OWASP están revisadas, justificadas y tienen evidencia.
- Pruebas automatizadas y análisis de seguridad muestran `passed` con una referencia de evidencia.
- SonarQube devuelve `OK` desde su Quality Gate configurado.
- El score calculado alcanza 80/100.
- No hay hallazgos críticos o altos ni categorías OWASP con hallazgos abiertos.

Los hallazgos críticos/altos bloquean; un fallo de herramienta, score inferior o hallazgo OWASP deja el dictamen en cambios requeridos. La falta de información deja la compuerta pendiente y mantiene la entrega pendiente para completar la revisión. Aprobar el hito actualiza su estado; aprobar el proyecto completo requiere además un dictamen aprobado.

## Configurar SonarQube

1. En SonarQube, configura un Quality Gate adecuado para el proyecto y ejecuta el análisis del commit que se revisará.
2. Crea un token de usuario con permisos mínimos de lectura para consultar el estado del proyecto.
3. Define `SONARQUBE_URL` (por ejemplo, la URL base de tu instancia) y `SONARQUBE_TOKEN` en el entorno privado de PHP. No guardes el token en el repositorio ni lo envíes al navegador.
4. Habilita la extensión cURL de PHP.
5. En la auditoría QA, escribe el `project key` exacto de SonarQube. Devioz consulta `/api/qualitygates/project_status` por HTTPS cuando está disponible y registra estado, condiciones, fecha, commit y enlace al panel. Sin configuración, análisis o respuesta válida queda `not_run`/`error`; nunca se asume PASS.

Un Quality Gate de SonarQube solo expresa el resultado de las condiciones configuradas y del análisis recuperado. Verifica que el análisis corresponda al SHA declarado en el formulario; el conector registra ese SHA, pero no puede probar que la instancia haya analizado el mismo commit si SonarQube no expone esa relación en la respuesta consultada.

## Evidencias recomendadas

- Pruebas: URL de CI, suite ejecutada, entorno, fecha, commit, totales y fallos.
- Seguridad: SAST/DAST/dependencias, alcance, herramienta y versión, fecha, reporte y severidad de hallazgos.
- Rendimiento: objetivo acordado, carga, concurrencia, latencia percentil 95 y condiciones del entorno.
- Funcionalidad: criterios de aceptación y casos de prueba trazables al requisito.
- Usabilidad y accesibilidad: tareas evaluadas, dispositivos, criterios WCAG aplicables y problemas observados.
- Despliegue/fiabilidad: evidencia de recuperación, backup/restore, monitoreo y estrategia de rollback cuando aplique.

Las pruebas automatizadas y el escaneo de seguridad aún se registran por el auditor con evidencia del pipeline/reporte; el formulario no afirma ejecutarlas. SonarQube sí se consulta mediante API cuando se configura.

## CoPilot Devioz

CoPilot recibe la última auditoría almacenada y puede resumir lo observado, identificar datos faltantes y proponer correcciones o pruebas de retest. Su respuesta no cambia el score, no cierra hallazgos, no ejecuta herramientas ni aprueba el Quality Gate. Para validar una corrección, se debe volver a ejecutar el pipeline/escáner y registrar una nueva auditoría asociada al commit corregido.

## Criterios de mantenimiento

- Revisa periódicamente el umbral, el Quality Gate Sonar y las reglas de hallazgos con el responsable de ingeniería.
- Mantén el historial inmutable: una nueva revisión crea otro registro; no sobrescribas auditorías previas.
- Ajusta categorías OWASP y políticas cuando se adopten nuevas versiones.
- Protege los reportes y enlaces de evidencia según la clasificación del proyecto y los acuerdos del cliente.
