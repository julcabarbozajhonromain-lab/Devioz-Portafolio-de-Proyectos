# 🚀 DEVIOZ CLASSIC — Sistema de Gestión de Prácticas Profesionales

**Devioz Classic** es una plataforma académica de nivel empresarial diseñada para la gestión, seguimiento, revisión y acreditación de prácticas profesionales universitarias e institucionales. 

El sistema implementa una arquitectura **MVC (Model-View-Controller)** estricta desarrollada en **PHP 8.2 puro**, **MySQL**, **Bootstrap 5**, **CSS3 personalizado** y **JavaScript Vanilla**, prescindiendo intencionalmente de frameworks pesados o pipelines de compilación en Node.js para garantizar un desempeño ultraligero y máxima portabilidad en servidores compartidos (cPanel) y entornos locales (XAMPP).

---

## 🎯 Justificación Arquitectónica: PHP Puro vs Frameworks

El proyecto original concebido en Laravel + Inertia/Vue presentaba fricciones severas en entornos de producción tradicionales: consumo excesivo de memoria RAM (40 a 80 MB por petición), roturas en las cadenas de compilación de NPM en servidores sin Node, carpetas `vendor` y `node_modules` de más de 150 MB y lentitud en hostings compartidos económicos.

**Devioz Classic resuelve estas limitaciones de raíz:**
- **Consumo Mínimo de Recursos:** Huella de memoria inferior a **4 MB de RAM** por request y tiempos de respuesta de **10 a 25 milisegundos**.
- **Despliegue Cero-Compilación (Zero Build Steps):** No requiere `npm run build`, Vite ni Webpack. Todo el CSS y JS nativo se sirve de forma directa con cache-busting automático.
- **Portabilidad Universal:** Compatible de inmediato con cualquier hosting cPanel estándar con PHP 8.2+ y Apache, así como entornos locales XAMPP, WampServer o contenedores Docker básicos.
- **Auditoría y Control Absoluto:** Sin abstracciones mágicas; cada query SQL pasa por sentencias preparadas de PDO, cada sesión es manipulada bajo directivas de seguridad estrictas y cada ruta está gobernada por un enrutador ligero con soporte de middlewares.

---

## 🛠️ Stack Tecnológico

| Capa | Tecnología | Características Clave |
| :--- | :--- | :--- |
| **Backend** | PHP 8.2+ Puro | Tipado estricto (`declare(strict_types=1)`), PSR-4 Autoloading nativo, PDO Singleton. |
| **Base de Datos** | MySQL 8.0+ / MariaDB 10.4+ | Motor InnoDB, Charset `utf8mb4_unicode_ci`, llaves foráneas con reglas ON DELETE, índices B-Tree. |
| **Frontend** | Bootstrap 5.3.3 + CSS3 Custom | Paleta Esmeralda (`#10b981`), tema Dark/Light nativo con `[data-theme]`, diseño responsivo mobile-first. |
| **Lógica de Cliente** | JavaScript Vanilla | SortableJS para Drag & Drop en Kanban, Chart.js para métricas, auto-dismiss de mensajes flash. |
| **Servidor Web** | Apache 2.4+ con `mod_rewrite` | Usa `public/index.php` como entrada compatible; las vistas y los assets están organizados dentro de `frontend/`. |

---

## 📋 Reglas de Negocio del Sistema

1. **1 Practicante = 1 Proyecto Activo:** Un estudiante no puede tener múltiples proyectos en curso simultáneamente. Al ser asignado un proyecto con estado `assigned`, `in_progress` o `pending_review`, queda bloqueado para nuevas asignaciones hasta concluirlo (`completed`).
2. **Ciclo de Hitos Secuenciales:** Cada proyecto cuenta con un cronograma de 1 a 12 hitos académicos. El avance porcentual se calcula en función de la ponderación de los hitos completados.
3. **Entregas con Evidencia:** El alumno envía código (ZIP), documentación técnica (PDF) o videos demostrativos (YouTube/Loom).
4. **Dictamen Docente:** El administrador califica en escala académica (0.0 a 10.0), aprueba entregas, observa con motivos obligatorios o aprueba el proyecto para su publicación en el portafolio público.
5. **Standup Diario (Check-ins):** Los practicantes registran su avance en 3 ventanas laborales (Mañana, Mediodía y Tarde), reportando horas dedicadas, blockers y patrones de diseño aplicados.

---

## 🔑 Cuentas de demostración

La semilla `database/seed.sql` contiene cuentas de muestra para desarrollo local. No uses estos datos de prueba en producción; crea cuentas y contraseñas nuevas en cada entorno antes de desplegar.

## 💻 Guía de Instalación en XAMPP (Local - Windows)

### Paso 1: Ubicación del Proyecto
Clonar o copiar la carpeta del proyecto directamente dentro del directorio de publicación de Apache:
```text
C:\xampp\htdocs\Devioz-Portafolio-de-Proyectos
```

### Paso 2: Iniciar Servicios
Abre el **XAMPP Control Panel** e inicia los módulos:
- **Apache**
- **MySQL**

### Paso 3: Importar Base de Datos
Desde la consola o mediante **phpMyAdmin** (`http://localhost/phpmyadmin`):
1. Importa el archivo de estructura:
   ```bash
   mysql -u root < C:\xampp\htdocs\Devioz-Portafolio-de-Proyectos\database\schema.sql
   ```
2. Importa la semilla de datos iniciales:
   ```bash
   mysql -u root devioz_classic < C:\xampp\htdocs\Devioz-Portafolio-de-Proyectos\database\seed.sql
   ```

### Paso 4: Configuración de Entorno (.env)
Verifica que el archivo `.env` en la raíz contenga los parámetros de conexión:
```env
APP_NAME="Devioz Classic"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/Devioz-Portafolio-de-Proyectos/public
APP_TIMEZONE=America/Lima

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devioz_classic
DB_USERNAME=root
DB_PASSWORD=
```

### Paso 5: Acceder a la Aplicación
Ingresa en tu navegador web a:
👉 **`http://localhost/Devioz-Portafolio-de-Proyectos/`**
*(El sistema te redirigirá automáticamente a la galería pública o a `/login`).*

---

## 🌐 Guía de Despliegue en Servidores cPanel / Hosting Compartido

1. **Subida de Archivos:**
   - Comprime el proyecto en un archivo `.zip` (excluyendo carpetas innecesarias).
   - Súbelo a través del **Administrador de Archivos de cPanel** o por **SFTP/FTP** a la carpeta `public_html` (o a un subdirectorio si es un subdominio).
   - Extrae el contenido.
2. **Configuración de Apache / DocumentRoot:**
   - Apache envía las peticiones al front controller en `frontend/public/index.php`; los assets siguen disponibles bajo la URL `/public/...`.
3. **Creación de Base de Datos en cPanel:**
   - Ve a **Bases de datos MySQL®** en cPanel.
   - Crea una nueva base de datos (ej: `usuario_devioz`).
   - Crea un usuario de base de datos con contraseña segura y asígnalo con **TODOS LOS PRIVILEGIOS**.
4. **Importación con phpMyAdmin:**
   - Abre **phpMyAdmin** en cPanel.
   - Selecciona la base de datos creada.
   - En la pestaña **Importar**, sube primero `database/schema.sql` y luego `database/seed.sql`.
5. **Configuración de Variables en `.env`:**
   - Edita el archivo `.env` en el servidor con los datos de producción:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://tudominio.com

     DB_HOST=localhost
     DB_DATABASE=usuario_devioz
     DB_USERNAME=usuario_dbuser
     DB_PASSWORD=TuPasswordSeguro
     ```
6. **Permisos de Escritura:**
   - Asegura permisos de escritura para `frontend/public/uploads` y `backend/storage/private/uploads`. Las entregas se guardan fuera del directorio web y se descargan desde la aplicación con autorización.

---

## Estructura del Proyecto

El código está agrupado en tres áreas principales para identificar con claridad cada responsabilidad:

```text
Devioz-Portafolio-de-Proyectos/
├── backend/                 # Aplicación PHP, configuración, comandos y almacenamiento privado
│   ├── app/                 # Controladores, núcleo, modelos, políticas y servicios
│   ├── bootstrap/           # Arranque, autoload y registro de rutas
│   ├── config/              # Configuración de aplicación y base de datos
│   ├── bin/                 # Herramientas CLI y scripts de mantenimiento
│   └── storage/             # Logs, caché y archivos privados
├── frontend/                # Interfaz renderizada y recursos servidos al navegador
│   ├── views/               # Vistas PHP, layouts y componentes
│   └── public/              # CSS, JavaScript, imágenes y archivos públicos
├── database/                # Esquema, semillas y cambios SQL versionados
├── docs/                    # Documentación técnica y de arquitectura
├── tests/                   # Guías y pruebas del proyecto
├── artisan                  # Consola de mantenimiento
├── composer.json            # Autoload PSR-4
└── .htaccess                # Enrutamiento compatible con la URL local actual de XAMPP
```

`public/index.php` es un adaptador pequeño que conserva la URL de XAMPP y delega en `backend/bootstrap/app.php`; el contenido de interfaz vive en `frontend/views/` y `frontend/public/`. Los directorios `backend/`, `database/` y `frontend/views/` no deben exponerse directamente por HTTP.
## 🔒 Mecanismos de Seguridad Implementados

1. **Inyección SQL (SQLi):** 100% de las consultas a base de datos utilizan **Sentencias Preparadas de PDO** con paso estricto de parámetros enlazados (`bind parameters`). La emulación de consultas preparadas está desactivada a nivel de driver.
2. **Cross-Site Scripting (XSS):** Todo dato renderizado en vistas pasa por la función de escape `e()`, configurada con `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
3. **Cross-Site Request Forgery (CSRF):** Todos los formularios y llamadas asíncronas con métodos `POST`, `PUT` o `DELETE` validan tokens criptográficos generados con `random_bytes(32)` y comparados mediante `hash_equals()`.
4. **Fijación de Sesión (Session Fixation):** Al completar un inicio de sesión exitoso, el sistema ejecuta `session_regenerate_id(true)`, destruyendo la sesión previa e invalidando el identificador anterior.
5. **Cookies de Sesión Seguras:** Cookies configuradas con directivas `HttpOnly`, `SameSite=Lax` y `Secure` (cuando se detecta conexión HTTPS).
6. **Subida Segura de Archivos:** Las extensiones son verificadas contra una lista blanca y el tipo MIME es inspeccionado en el contenido binario real del archivo mediante `finfo_file` (evitando archivos maliciosos renombrados con extensiones falsas).
7. **Cabeceras HTTP de Seguridad:** Cabeceras `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff` y `X-XSS-Protection: 1; mode=block` inyectadas en cada petición.

---

## 📄 Licencia y Créditos Académicos

Desarrollado como proyecto académico de prácticas profesionales para el ecosistema **Devioz**. Código abierto bajo licencia MIT.
