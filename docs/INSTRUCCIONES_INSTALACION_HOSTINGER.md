# GUÍA DE INSTALACIÓN Y DESPLIEGUE EN HOSTINGER
## DEVIOZ 24 — PORTAFOLIO DE PROYECTOS DE PRÁCTICAS

---

### 1. REQUISITOS DEL SERVIDOR (HOSTINGER)

* **Versión de PHP:** PHP 8.2 o superior (seleccionable desde *hPanel > Sitios Web > Administrar > Configuración de PHP*).
* **Base de datos:** MySQL 8.0+ o MariaDB 10.4+.
* **Extensiones PHP requeridas (activadas por defecto en Hostinger):**
  - `pdo_mysql`
  - `fileinfo`
  - `mbstring`
  - `json`
  - `curl`
* **Módulo Apache:** `mod_rewrite` habilitado (estándar en Hostinger).
* **Permisos de escritura:** En la carpeta del proyecto para generar el archivo `.env` y el bloqueo `installed.lock`.

---

### 2. CLAVE PRIVADA DE INSTALACIÓN

Para proteger la instalación y evitar que usuarios no autorizados ejecuten el asistente en el servidor de producción:

> **CLAVE PRIVADA:** `DEVIOZ_HOSTINGER_2026_INSTALL`

*(Esta clave se solicita en la pantalla inicial de `instalar.php` antes de permitir cualquier operación).*

---

### 3. CONTENIDO DEL PAQUETE DE ENTREGA

1. **ZIP Completo (`devioz_hostinger_completo.zip`):**
   Contiene el código fuente completo del sistema, librerías, vistas, base de datos y el instalador automático listo para descomprimir en Hostinger.
2. **ZIP Diferencial (`devioz_hostinger_diferencial.zip`):**
   Contiene únicamente los archivos creados o actualizados para la integración con Hostinger:
   - `instalar.php` (Instalador automático web con interfaz profesional)
   - `.htaccess` (Reglas de Apache adaptadas para subcarpetas y bloqueo de archivos confidenciales)
   - `database/devioz_hostinger_portafolio.sql` (Script SQL unificado sin `CREATE DATABASE` ni `USE`)
   - `docs/INSTRUCCIONES_INSTALACION_HOSTINGER.md` (Este documento de guía)
   - `backend/app/Controllers/TeamController.php` (CRUD completo de equipo y roles)
   - `backend/app/Models/User.php`
   - `frontend/views/team/index.php`
   - `backend/bootstrap/app.php`
3. **Archivo SQL (`database/devioz_hostinger_portafolio.sql`):**
   Estructura completa de 12 tablas con llaves foráneas y datos iniciales de proyectos, usuarios y auditorías. Totalmente sanitizado para bases de datos con prefijo en Hostinger (ejemplo: `u123456789_devioz`).

---

### 4. PASO A PASO PARA LA INSTALACIÓN EN HOSTINGER

#### Paso 1: Subir los Archivos al Hosting
1. Iniciar sesión en el panel de **Hostinger (hPanel)**.
2. Ingresar a **Archivos > Administrador de Archivos (File Manager)**.
3. Navegar a la carpeta pública del dominio:
   - Ruta: `public_html/nombre_proyecto/`  
   *(Ejemplo: `public_html/devioz-portafolio/`)*.
4. Subir el archivo `devioz_hostinger_completo.zip` y descomprimirlo (Extract) dentro de esa carpeta.
5. Verificar que el archivo `.htaccess` y el archivo `instalar.php` se encuentren en la raíz de `public_html/nombre_proyecto/`.

#### Paso 2: Crear la Base de Datos en Hostinger
1. En hPanel, ir a **Bases de Datos > Gestión de Bases de Datos MySQL**.
2. Crear una nueva base de datos y usuario:
   - **Nombre de la base de datos:** ej. `u123456789_devioz`
   - **Nombre de usuario MySQL:** ej. `u123456789_admin`
   - **Contraseña:** Elegir una contraseña segura y guardarla.
3. Hostinger asignará el host de la base de datos (habitualmente `localhost`).

#### Paso 3: Ejecutar el Instalador Web Automático
1. Abrir el navegador web e ingresar a la URL del instalador:
   ```
   https://devioz.com/nombre_proyecto/instalar.php
   ```
   *(Si estás probando localmente en XAMPP: `http://localhost/Devioz-Portafolio-de-Proyectos/instalar.php`)*.

2. **Fase 1 — Verificación y Diagnóstico:**
   - Ingresar la **Clave Privada de Instalación**: `DEVIOZ_HOSTINGER_2026_INSTALL`
   - El sistema validará la clave y comprobará automáticamente los requisitos del servidor (PHP 8.2+, extensiones PDO, permisos de escritura).
   - Presionar **"Continuar a Base de Datos"**.

3. **Fase 2 — Credenciales de Base de Datos:**
   - **Host MySQL:** `localhost` (o el host asignado por Hostinger).
   - **Puerto:** `3306`.
   - **Nombre de Base de Datos:** El nombre creado en el Paso 2 (ej. `u123456789_devioz`).
   - **Usuario MySQL:** El usuario creado (ej. `u123456789_admin`).
   - **Contraseña MySQL:** La contraseña definida.
   - Presionar **"Probar Conexión"** para verificar que las credenciales son válidas antes de continuar.

4. **Fase 3 — URL del Sistema y Administrador:**
   - **URL del Proyecto:** El instalador detectará automáticamente la URL (ej. `https://devioz.com/nombre_proyecto`).
   - **Nombre del Administrador:** Nombre del tutor o encargado (por defecto: `Elena Rostova`).
   - **Correo Electrónico:** `admin@devioz.com` (o el correo de preferencia).
   - **Contraseña del Administrador:** Definir la contraseña de acceso (por defecto sugerida: `Devioz2026*`).

5. **Fase 4 — Ejecución de la Instalación:**
   - Hacer clic en **"Instalar Sistema DEVIOZ"**.
   - El instalador:
     1. Importará automáticamente las 12 tablas y registros semilla desde `database/devioz_hostinger_portafolio.sql`.
     2. Configurará al usuario administrador con la contraseña encriptada con Bcrypt.
     3. Creará el archivo de configuración `.env` en producción.
     4. Creará el candado de seguridad `installed.lock`.
   - Aparecerá la pantalla de confirmación con el botón directo para iniciar sesión.

---

### 5. ACCESO Y CREDENCIALES DEL SISTEMA

Una vez finalizada la instalación:

* **URL del Sistema:** `https://devioz.com/nombre_proyecto/`
* **URL de Login Directo:** `https://devioz.com/nombre_proyecto/login`
* **Credenciales de Administrador:**
  - **Correo:** `admin@devioz.com` (o el definido en el instalador)
  - **Contraseña:** `Devioz2026*` (o la definida en el instalador)
  - **Rol:** Administrador (`admin`)

* **Credencial de Desarrollador / Practicante (Demo):**
  - **Correo:** `jjulca@devioz.com`
  - **Contraseña:** `Devioz2026*`
  - **Rol:** Desarrollador (`developer`)

---

### 6. SEGURIDAD Y BLOQUEO POST-INSTALACIÓN

1. **Bloqueo Automático:**
   Al finalizar, el instalador genera el archivo `installed.lock` en la raíz. Si alguien intenta abrir nuevamente `instalar.php`, el sistema mostrará una advertencia de bloqueo y no permitirá ninguna modificación.
2. **Protección `.htaccess`:**
   El archivo `.htaccess` prohíbe el acceso web directo a:
   - Archivo `.env` (credenciales de BD y API Keys).
   - Archivo `installed.lock`.
   - Archivos de base de datos `.sql`.
   - Carpeta del framework `backend/`.
3. **Deshabilitación total opcional:**
   Si se requiere una capa extra de seguridad tras validar el funcionamiento, se puede eliminar o renombrar el archivo `instalar.php` directamente desde el Administrador de Archivos de Hostinger.

---

### 7. VERIFICACIÓN FINAL DE MÓDULOS

Tras la instalación, verificar el funcionamiento de las siguientes secciones:
1. **Inicio de Sesión:** Acceder con las credenciales de administrador.
2. **Portafolio y Galería de Proyectos:** Carga de tarjetas y métricas de proyectos.
3. **Gestión de Programadores y Roles (`/team`):** Visualizar practicantes, editar roles (Admin, Desarrollador, Líder) y registrar nuevos talentos.
4. **Messenger Interno (`/chat`):** Envío y recepción de mensajes, notas de voz, adjuntos y vista de contactos.
5. **Reels y Demostraciones (`/reels`):** Visualización de videos de sustentación y catálogo técnico interactivo.
6. **Auditorías QA y Evaluación:** Control de calidad y estados de entrega.

