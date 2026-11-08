# Sistema de Gestión - Escuela de Béisbol Menor Maporal

**Versión:** 1.2.0
**Última actualización:** Junio 2026
**Autor:** Henry Velazquez

---

## 📋 Tabla de Contenidos

1. [Información General](#información-general)
2. [Características del Sistema](#características-del-sistema)
3. [Requisitos del Sistema](#requisitos-del-sistema)
4. [Instalación](#instalación)
5. [Estructura del Proyecto](#estructura-del-proyecto)
6. [Configuración](#configuración)
7. [Módulos del Sistema](#módulos-del-sistema)
8. [Roles y Permisos](#roles-y-permisos)
9. [Base de Datos](#base-de-datos)
10. [Guía de Uso](#guía-de-uso)
    - [10.1 Acceso al Sistema](#acceso-al-sistema)
    - [10.2 Gestión de Atletas](#gestión-de Atletas)
    - [10.3 Gestión de Pagos](#gestión-de-pagos)
    - [10.4 Generar Reportes](#generar-reportes)
    - [10.5 Formato de Teléfonos](#formato-de-teléfonos)
    - [10.6 Gestión de Fotografías](#gestión-de-fotografías)
    - [10.7 Gestión de Administradores](#gestión-de-administradores)
    - [10.8 Gestión de Sesión](#gestión-de-sesión)
    - [10.9 Dashboard](#dashboard)
11. [Arquitectura Técnica](#arquitectura-técnica)
12. [Documentación de API](#documentación-de-api)
13. [Seguridad](#seguridad)
14. [Tecnologías Utilizadas](#tecnologías-utilizadas)
15. [Registro de Cambios](#registro-de-cambios)
16. [Solución de Problemas](#solución-de-problemas)
17. [Puesta en producción](#puesta-en-producción)

---

# 1. Información General

Sistema Automatizado para la Gestión Administrativa de Escuela de Beisbol Menor Maporal (Pdvsa) de Barinas, Municipio Barinas.

Este sistema proporciona una solución completa para la gestión de Atletas, pagos, reportes y administración de la escuela de béisbol.

---

# 2. Características del Sistema

## 🎯 Gestión de Atletas

- ✅ Registro completo de Atletas con información personal, médica y de contacto
- ✅ Carga de fotografías de Atletas (JPG, PNG, máx. 2MB)
- ✅ Búsqueda inteligente por nombre, C.I. del atleta o del representante
- ✅ Autocompletado en tiempo real
- ✅ Validación de edad según categoría (Infantil, Pre-juvenil, Juvenil, Junior)
- ✅ Gestión de números de jersey únicos
- ✅ Formato de teléfono venezolano (código de área + 7 dígitos)
- ✅ Estados: Activo, Inactivo, Suspendido
- ✅ **Detalle del estatus:** al dejar a un atleta Inactivo o Suspendido se registra el **motivo**, **quién** lo hizo y **cuándo**; administrador y secretario lo ven completo, el entrenador y el representante solo ven el estatus y desde cuándo

## 💰 Gestión de Pagos

- ✅ **Flujo de verificación por estados:** Pendiente → En Revisión → Aprobado (y Rechazado, con motivo)
- ✅ **Separación de funciones:** quien reporta o registra un pago no puede aprobarlo ni rechazarlo, sea secretario o administrador: siempre hace falta una **segunda persona** que concilie
- ✅ El **representante** consulta el estado de cuenta de sus atletas y **reporta sus pagos** (referencia, fecha, método y comprobante)
- ✅ **Catálogo de conceptos de cobro** (mensualidad, inscripción, uniforme…) con tarifa, administrado por el administrador
- ✅ **Datos bancarios de la escuela** (transferencia y pago móvil) que carga el administrador en Conceptos y que el representante ve al reportar su pago; cada cambio queda registrado con quién, cuándo y el valor anterior
- ✅ **Historial inmutable** de cada pago: quién hizo qué, cuándo y por qué
- ✅ **Pago aprobado = cerrado.** Solo el administrador puede reabrirlo, con motivo obligatorio
- ✅ Comunicación automática: recibo por correo y WhatsApp **solo al aprobar**, aviso por correo con el motivo al rechazar, y **aviso por correo a quienes pueden conciliar** (secretarios y administradores) cuando un pago queda por revisar. Además, el secretario y el administrador ven un **contador rojo** junto a "Por revisar" en el menú
- ✅ Comprobantes de pago (JPG/PNG/WEBP, máx. 5 MB) servidos **solo a quien puede ver ese pago**

## 📊 Sistema de Reportes

- ✅ Reporte de Atletas (PDF)
- ✅ Ficha individual del atleta con fotografía (PDF)
- ✅ Reporte de pagos (PDF)
- ✅ Historial de pagos por atleta (PDF)
- ✅ Reporte financiero (PDF)
- ✅ **Código QR de verificación de autenticidad** en todos los PDFs generados
- ✅ **Página pública de validación** — cualquier persona puede escanear el QR para comprobar que el documento no fue alterado

## 📦 Inventario

- ✅ Registro de equipamiento y materiales deportivos
- ✅ Categorías: Equipamiento, Uniformes, Herramientas, Material Deportivo, Consumibles, Otros
- ✅ Control de stock con alertas de stock mínimo
- ✅ Registro de movimientos: Entradas, Salidas y Ajustes con trazabilidad completa
- ✅ Historial de movimientos por ítem (quién, cuándo, motivo)
- ✅ Condición del ítem: Nuevo, Bueno, Regular, Deteriorado, Baja
- ✅ Filtros por categoría, condición, estado y stock bajo
- ✅ Disponible para Administrador, Secretario y Entrenador (no para Representante)

## 🏆 Cronograma de Torneos

- ✅ Registro de torneos con nombre, categoría, lugar y fechas
- ✅ Estatus automático según fechas (Programado → En Curso → Finalizado)
- ✅ Inscripción y retiro de atletas participantes en tiempo real
- ✅ Filtros por nombre, estatus, categoría y año
- ✅ Estadísticas: total, programados, en curso, finalizados
- ✅ Eliminación protegida con clave de operaciones (solo Administrador)

## 👥 Administración

- ✅ Gestión de administradores con diferentes roles (5 roles)
- ✅ Sistema de autenticación seguro
- ✅ Recuperación de contraseña por email
- ✅ Cierre de sesión automático por inactividad (2 minutos)
- ✅ Dashboard con estadísticas en tiempo real

---

# 3. Requisitos del Sistema

## 💻 Requisitos Mínimos

- PHP 7.4 o superior
- MySQL 5.7 o superior / MariaDB 10.4 o superior
- Apache con mod_rewrite habilitado
- Composer (para dependencias)
- Extensiones PHP requeridas:
  - PDO
  - PDO_MySQL
  - mbstring
  - GD o Imagick (para procesamiento de imágenes)
  - fileinfo (para validación de archivos)

## 🖥️ Entorno de Desarrollo Recomendado

- **XAMPP:** Versión 3.3.0 o superior
- **PHP:** Versión 8.2.12 o superior
- **Extensión GD:** bundled (2.1.0 compatible) - Requerida para visualización de fotos en PDFs
- **Sistema Operativo:** Windows 10 x64 o superior
- **Navegador:** Chrome, Firefox, Edge (versiones recientes)

> **Nota importante:** La extensión GD de PHP es esencial para la correcta visualización de fotografías en los reportes PDF. Asegúrate de que esté habilitada en tu configuración de PHP (`php.ini`).

---

# 4. Instalación

## Paso 1: Clonar o descargar el proyecto

```bash
git clone [URL_DEL_REPOSITORIO]
cd project
```

## Paso 2: Instalar dependencias

```bash
composer install
```

## Paso 3: Configurar la base de datos

Crear la base de datos y ejecutar los scripts SQL:

```sql
-- Crear base de datos
CREATE DATABASE escuela_beisbol_maporal CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Importar estructura y datos iniciales
SOURCE database/escuela_beisbol_maporal.sql;
```

## Paso 4: Configurar el entorno y la base de datos

El sistema se configura con **tres archivos que NO se versionan** (están en `.gitignore` porque contienen secretos). Cada uno tiene una plantilla `.example.php` en la misma carpeta; se copia y se completa:

| Archivo | Para qué sirve | Plantilla |
|---|---|---|
| `config/app.php` | **Entorno**: URL, modo (local / producción), HTTPS y clave de firma de los QR | `config/app.example.php` |
| `config/database.php` | Conexión a la base de datos | `config/database.example.php` |
| `config/mail.php` | Correo (Gmail/SMTP) | `config/mail.example.php` |
| `config/whatsapp.php` | WhatsApp (Meta Cloud API) | `config/whatsapp.example.php` |

```bash
copy config\app.example.php config\app.php
copy config\database.example.php config\database.php
```

Ver la sección **6. Configuración** para el significado de cada valor y **17. Puesta en producción** para el procedimiento completo y el checklist.

## Paso 5: Configurar permisos

```bash
chmod 755 img/
chmod 644 config/*.php
```

## Paso 6: Configurar el servidor web

Asegúrate de que el DocumentRoot apunte al directorio del proyecto y que mod_rewrite esté habilitado.

## Paso 7: Acceder al sistema

Abrir la URL definida en `base_url` de `config/app.php` (en desarrollo: `http://localhost/project`) e iniciar sesión con la cuenta de administrador.

🔒 **No hay contraseña por defecto.** `admin` / `admin123` (que figuraba en versiones anteriores de este documento) **ya no funciona**. Si importas el SQL de referencia en un servidor nuevo, **cambia la contraseña de la cuenta `admin` antes de exponer el sistema**.

⚠️ Cambiar también la **clave de operaciones** de cada administrador (Administradores → Editar): protege las acciones destructivas.

---

# 5. Estructura del Proyecto

```
project/
├── config/                      # Configuración y credenciales
│   ├── config.php              # Configuración general y autoload
│   ├── database.php            # Configuración de base de datos
│   ├── mail.php                # SMTP (credenciales; en .gitignore)
│   ├── mail.example.php        # Plantilla de mail.php sin secretos
│   ├── whatsapp.php            # WhatsApp Cloud API (token; en .gitignore)
│   └── whatsapp.example.php    # Plantilla de whatsapp.php sin secretos
├── controllers/                 # Controladores MVC (+ núcleo: Controller, View, Router)
│   ├── AdminsController.php       # Gestión de administradores
│   ├── AthletesController.php     # Gestión de Atletas
│   ├── AuthController.php         # Autenticación y sesiones
│   ├── Controller.php             # Controlador base
│   ├── DashboardController.php    # Dashboard principal
│   ├── InventoryController.php    # Gestión de inventario
│   ├── PasswordResetController.php # Recuperación de contraseña
│   ├── PaymentsController.php     # Gestión de pagos
│   ├── ReportsController.php      # Generación de reportes
│   ├── Router.php                 # Enrutador de la aplicación
│   ├── TournamentsController.php  # Cronograma de torneos
│   ├── TrainingController.php     # Cronogramas de entrenamiento
│   ├── VerifyController.php       # Verificación pública de PDFs
│   └── View.php                   # Motor de vistas
├── models/                        # Modelos de datos (acceden a la base de datos)
│   ├── Model.php                  # Modelo base (CRUD genérico)
│   ├── Admin.php                  # Modelo de administradores
│   ├── Athlete.php                # Modelo de Atletas
│   ├── Inventory.php              # Modelo de inventario
│   ├── Payment.php                # Modelo de pagos
│   ├── PdfVerification.php        # Modelo de verificaciones PDF
│   ├── Tournament.php             # Modelo de torneos
│   └── TrainingSchedule.php       # Modelo de cronogramas
├── services/                      # Lógica de negocio y utilidades (ni modelo ni controlador)
│   ├── Mailer.php                 # Envío de correos (PHPMailer)
│   ├── PdfGenerator.php           # Trait: generación de PDFs con QR
│   ├── AthleteEnrollmentService.php # Planilla de inscripción (PDF, correo, WhatsApp)
│   ├── AthleteClearanceService.php # Solvencia de retiro (elegibilidad, emisión, PDF)
│   ├── AthleteCertificateService.php # Constancia de pertenencia (emisión, PDF, correo)
│   ├── EmailReceiptService.php    # Recibo de pago por correo
│   ├── WhatsAppReceiptService.php # Recibo de pago por WhatsApp
│   ├── WhatsAppClient.php         # Cliente de la API de Meta
│   └── WhatsAppApiException.php   # Excepción de la API de Meta
├── views/                         # Vistas de la aplicación
│   ├── admins/                    # Vistas de administradores
│   ├── athletes/                  # Vistas de Atletas
│   ├── auth/                      # Vistas de autenticación
│   ├── dashboard/                 # Vista del dashboard
│   ├── inventory/                 # Vistas de inventario
│   ├── layouts/                   # Plantillas principales
│   ├── payments/                  # Vistas de pagos
│   ├── reports/                   # Vistas de reportes
│   ├── training/                  # Vistas de cronogramas
│   ├── verify/                    # Vista pública de verificación
│   └── assets/                    # Recursos estáticos
│       ├── css/                # Estilos CSS
│       └── fonts/              # Fuentes
├── database/                    # Scripts SQL
│   └── escuela_beisbol_maporal.sql  # Estructura inicial
├── img/                         # Fotografías de Atletas
├── index.php                    # Punto de entrada
├── .htaccess                    # Configuración Apache
└── composer.json                # Dependencias PHP
```

---

# 6. Configuración

## Entorno (`config/app.php`)

**Es el único archivo que hay que tocar al mover el sistema a otro servidor.** Se copia desde `config/app.example.php`. No se versiona.

```php
return [
    'env'       => 'production',                    // 'production' (errores ocultos) o 'local' (errores visibles)
    'base_url'  => 'https://escuela.ejemplo.com/',  // URL completa, TERMINADA EN "/"
    'base_path' => '/',                             // '/' si va en la raiz del dominio; '/sistema/' si va en una subcarpeta
    'https'     => true,                            // OBLIGATORIO en produccion: activa la cookie de sesion "secure"
    'hmac_key'  => '<64 caracteres aleatorios>',    // firma los QR de los documentos NUEVOS
    'hmac_previous_keys' => [],                     // claves anteriores: solo para VERIFICAR documentos ya emitidos
    'daily_send_limit_email'    => 150,             // tope diario de correos automaticos
    'daily_send_limit_whatsapp' => 200,             // tope diario de WhatsApp automaticos
];
```

| Valor | Qué controla | Cuidado |
|---|---|---|
| `env` | `local` muestra los errores de PHP en pantalla; cualquier otro valor los **oculta** y solo los registra en el log del servidor | En producción debe ser `production`. Mostrar errores revelaría rutas, consultas y datos internos |
| `base_url` | Dirección que aparece en el **QR de los PDF** y en los enlaces de los correos | **Defínela bien antes de emitir documentos reales.** Un QR ya impreso apunta a la URL que había al generarlo: si luego cambias el dominio, esos QR dejan de funcionar |
| `base_path` | Ruta de la cookie de sesión | Debe coincidir con la ruta de `base_url`. Así la cookie no viaja a otras aplicaciones del mismo dominio |
| `https` | Cookie de sesión solo por conexiones seguras | Con HTTPS en `false` la cookie viaja en claro. Activarlo **sin** tener HTTPS funcionando impide iniciar sesión |
| `hmac_key` | Clave con la que se firma cada QR nuevo. Generar con `php -r "echo bin2hex(random_bytes(32));"` | **No la cambies** una vez emitidos documentos: sus QR dejarían de validarse. Si hay que rotarla, la anterior pasa a `hmac_previous_keys` |
| `hmac_previous_keys` | Claves antiguas aceptadas **solo para verificar**, nunca para firmar | Aquí está la clave que estaba escrita en el código (`maporal_pdf_secret_2026`): los 29 documentos emitidos antes de sacarla del código se firmaron con ella y siguen validándose |
| `daily_send_limit_*` | Tope diario de envíos automáticos por canal (ver *Límite diario de envíos*) | `-1` lo desactiva **a propósito**. `0`, vacío o texto **no** lo desactivan: se usa el valor por defecto |

**Si `config/app.php` falta o tiene un error**, el sistema **no se cae**: arranca en modo producción seguro (errores ocultos, cookie segura) con la URL por defecto y deja el motivo en el log del servidor. Esto es a propósito: un archivo roto no debe tumbar el sistema ni mostrar errores.

## Límite diario de envíos

Los envíos automáticos (recibos, planillas, constancias, avisos y WhatsApp) **no se pueden retirar una vez enviados**. Para que un error o un bucle no mande cientos de mensajes a las familias, cada canal tiene un **tope diario** (`config/app.php`):

- **Correo: 150 al día. WhatsApp: 200 al día.** El negocio de Meta sin verificar admite 250 conversaciones diarias; el tope deja margen.
- Al llegar al tope, los envíos siguientes se **bloquean** (quedan registrados como `blocked` y en el log del servidor) y se pueden reintentar al día siguiente o subiendo el tope.
- Un envío que **falla también consume cupo**: protege contra bucles de reintentos contra un servidor caído.
- **Falla cerrado:** si el contador no se puede consultar (base de datos caída), el envío se bloquea. Un límite que se salta cuando algo falla no protege.
- **Excepción: la recuperación de contraseña** nunca se limita ni consume cupo. Si una avería agotara el cupo, nadie podría recuperar su acceso.
- El conteo es **atómico**: aun con muchas peticiones simultáneas nunca se pasa del tope (probado con 5 rondas de 10 procesos a la vez).
- Cada intento se registra en `send_log` **sin guardar el correo ni el teléfono** (solo una huella parcial para detectar repeticiones).
- Los contadores por día están en `send_counters`.

Para ver lo enviado hoy: `SELECT channel, status, COUNT(*) FROM send_log WHERE created_at >= CURDATE() GROUP BY channel, status;`

## Eliminación de atletas y pagos (deshabilitada)

La eliminación de **atletas** está deshabilitada y se controla con un interruptor en `config/config.php`:

```php
define('ALLOW_DELETE_ATHLETES', false);
```

Con el valor `false`, el ícono de papelera aparece desactivado y la ruta `athletes/delete` responde `403` aunque se llame directamente. Para reactivarla, cambiar el valor a `true`.

⚠️ **Eliminar un atleta borra también todos sus pagos y el historial de cada uno** (las claves foráneas tienen `ON DELETE CASCADE`). Esto destruye la evidencia financiera. Para apartar a un atleta sin perder su historial, usar el estado `inactive` desde Editar.

🚫 **Los pagos ya no se pueden eliminar desde la aplicación.** La ruta `payments/delete` y su botón fueron retirados: un pago con historial de verificación es un registro financiero. Un error se corrige **rechazando** el pago (si está en revisión) o **reabriéndolo** (administrador, con motivo), nunca borrándolo. La constante `ALLOW_DELETE_PAYMENTS` ya no tiene efecto.

Estos bloqueos son a nivel de aplicación: quien tenga acceso directo a la base de datos (por ejemplo con phpMyAdmin) todavía puede borrar registros.

## Configuración de Email

Copiar `config/mail.example.php` como `config/mail.php` (si no existe) y editarlo para configurar el envío de correos (recuperación de contraseña y recibos). `config/mail.php` está en `.gitignore` porque contiene credenciales:

```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'tu_email@gmail.com';
$mail->Password = 'tu_contraseña_app';
```

## Configuración de PHP

**php.ini recomendado:**

```ini
upload_max_filesize = 2M
post_max_size = 8M
max_execution_time = 30
memory_limit = 128M
date.timezone = America/Caracas

; Extensión GD (requerida para imágenes en PDFs)
extension=gd

; Otras extensiones necesarias
extension=pdo_mysql
extension=mbstring
extension=fileinfo
```

---

# 7. Módulos del Sistema

## 📊 Dashboard

- Estadísticas generales del sistema
- Atletas activos por categoría
- Resumen de pagos
- Atletas y pagos recientes

## 👨‍🎓 Gestión de Atletas

**Funcionalidades:**

- Crear, editar y ver Atletas
- Búsqueda inteligente unificada (nombre, C.I. atleta, C.I. representante)
- Carga de fotografías
- Validación automática de edad vs categoría
- Gestión de números de jersey únicos
- Filtros por categoría, edad, estado
- Planilla de inscripción, ficha técnica y planilla en blanco (descargables; la planilla se envía al representante por correo y WhatsApp al inscribir)
- **Solvencia de retiro**: documento que solo se emite si el atleta no tiene pagos pendientes; al emitirlo el atleta pasa a inactivo (ver "Solvencia de retiro" más abajo)
- **Constancia de pertenencia**: documento que acredita que el atleta forma parte de la escuela; solo se emite a atletas activos, dura 30 días y se descarga o se envía por correo (ver "Constancia de pertenencia" más abajo)

**Campos del atleta:**

- Información personal: nombre, apellido, C.I. (opcional), fecha de nacimiento, edad, categoría, jersey, estado, foto
- Información del representante: nombre, C.I., teléfono, email
- Información de contacto: dirección, contacto de emergencia, teléfono de emergencia
- Información médica: condiciones médicas

## 💳 Gestión de Pagos

**Funcionalidades:**

- Generar cobros por concepto (secretario / administrador)
- Reportar un pago con su comprobante (representante, o el personal en su nombre)
- Conciliar: aprobar o rechazar con comentario (secretario)
- Reabrir un pago aprobado con motivo (administrador)
- Catálogo de conceptos y tarifas (administrador)
- Datos bancarios de la escuela, visibles al representante al reportar un pago (administrador los carga)
- Filtros por estado, mes, año, categoría y atleta

**Ciclo de vida de un pago:**

```
 pending ──reportar──> in_review ──aprobar──> approved ──reabrir (admin, con motivo)──┐
    ▲                      │                                                          │
    │                      └──rechazar (con motivo)──> rejected ──reportar de nuevo──┐ │
    └──────────────────────────────────────────────────────────────────────────────  └─┴──> in_review
```

| Estado | Etiqueta | Significado | ¿Cuenta como cobrado? |
|---|---|---|---|
| `pending` | Pendiente | Cobro generado, aún sin pago reportado | No |
| `in_review` | En Revisión | El pago fue reportado y espera conciliación | No |
| `approved` | Aprobado | Conciliado contra el banco por un secretario | **Sí** |
| `rejected` | Rechazado | No se pudo verificar; lleva el motivo | No |

**Reglas que garantiza el servidor** (no solo la pantalla; ver `models/PaymentWorkflow.php`):

- Un pago solo cambia de estado por una transición válida; cada transición exige un rol concreto.
- **Quien reportó o registró un pago no puede aprobarlo ni rechazarlo**, aunque sea administrador. Aprobar y rechazar lo pueden hacer el **secretario y el administrador**, siempre sobre un pago que otra persona registró.
- Rechazar y reabrir **exigen un motivo** (mínimo 5 caracteres); queda en el historial.
- Un pago aprobado **no se edita**: ni monto, ni fecha, ni estado.
- Nadie puede crear un pago ya aprobado: todo cobro nace `pending`.
- Cada cambio y su registro de historial ocurren en **una sola transacción**, con el pago bloqueado: dos aprobaciones simultáneas no pueden producir dos recibos (probado con 6 peticiones simultáneas: 1 gana, 5 rechazadas).
- **La referencia bancaria es solo numérica (4 a 20 dígitos) y única entre pagos activos, sea cual sea el método.** Una referencia identifica **una** operación del banco: no puede respaldar dos pagos En Revisión o Aprobados distintos (antes solo se comparaba dentro del mismo método, y un mismo depósito podía justificar dos cobros declarando "transferencia" en uno y "pago móvil" en el otro). Un pago **rechazado** libera su referencia: un rechazo no la "quema".
- El servidor exige que la referencia tenga **solo dígitos** aunque el navegador ya lo filtre (se revalida en `PaymentWorkflow`). Solo se recortan espacios y saltos de línea de los **extremos**; no se "corrige" nada más, porque quitar letras o guiones en silencio podría cambiar el número y entonces no coincidiría con el del banco.
- `payment_history` es de solo escritura: la aplicación nunca actualiza ni borra sus filas.

Un cobro **Pendiente o Rechazado** cuya fecha de vencimiento ya pasó se resalta en rojo ("Venció dd/mm/aaaa"), calculado al vuelo con `Payment::isPastDue()`. Un pago **En Revisión** no se considera atrasado mientras se concilia.

**Deuda:** todo cobro que no esté `approved` (pendiente, en revisión o rechazado) cuenta como deuda. La solvencia de retiro solo se emite si el atleta no tiene deuda alguna.

## 📄 Reportes

**Tipos de reportes en PDF:**

- Lista de Atletas
- Ficha individual del atleta (incluye fotografía)
- Reporte de pagos
- Historial de pagos por atleta
- Reporte financiero

**Verificación de autenticidad con QR:**

Cada PDF generado incluye automáticamente un **código QR** en la parte inferior del documento. Este QR permite verificar que el contenido del PDF impreso no ha sido modificado con editores externos.

**Cómo funciona:**

1. Al generar el PDF, el sistema calcula un **HMAC-SHA256** de los datos clave del reporte y los registra en la tabla `pdf_verifications` junto con un token único.
2. El código QR apunta a `BASE_URL/verify?token={token}`.
3. Al escanear el QR (o acceder a la URL), el sistema muestra los **datos originales** del momento de generación y confirma si el documento es auténtico.
4. Si el documento fue alterado por un editor PDF externo, los datos del impreso no coincidirán con los del sistema — la discrepancia es inmediatamente visible.

**Estados de verificación:**

| Estado | Descripción |
|--------|-------------|
| ✅ **Documento Auténtico** | El token existe y el fingerprint coincide — datos íntegros |
| ⚠️ **Documento Alterado** | El token existe pero el fingerprint no coincide — datos manipulados |
| ❌ **Documento No Encontrado** | El token no existe en el sistema — documento falso o QR adulterado |

**Tabla de base de datos:** `pdf_verifications`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `token` | varchar(64) | Identificador único del PDF (hex aleatorio) |
| `report_type` | varchar(50) | Tipo de reporte (athlete_card, athlete_list, etc.) |
| `fingerprint` | varchar(64) | HMAC-SHA256 del payload con clave secreta |
| `payload` | longtext | JSON con los datos clave del reporte en el momento de generación |
| `generated_by` | int | ID del administrador que generó el PDF |
| `generated_at` | datetime | Fecha y hora exacta de generación |
| `verified_count` | int | Cantidad de veces que fue verificado |
| `last_verified_at` | datetime | Última fecha de verificación |

## 👥 Administradores

**Funcionalidades:**

- Crear, editar, ver y eliminar administradores
- Gestión de roles y permisos
- Información de contacto y emergencia

---

# 8. Roles y Permisos

El sistema cuenta con **cinco roles** de usuario, cada uno con permisos diferenciados para garantizar la seguridad y el correcto flujo de trabajo.

## 🔑 Administrador (administrador)

**Control total del sistema, con una sola limitación: no puede borrar pagos ni atletas. Concilia pagos igual que el secretario.**

- ✅ Gestión completa de Atletas (crear, editar, ver, **borrar** si está habilitado)
- ✅ Ve el **detalle completo del estatus** de un atleta inactivo o suspendido (motivo, quién y cuándo) y lo registra al cambiarlo
- ✅ **Conceptos de cobro y tarifas** (crear, editar, activar/desactivar)
- ✅ **Datos bancarios de la escuela** (única cuenta de cobro): solo el administrador los edita, con historial de cambios
- ✅ Generar cobros y editar los que estén Pendientes o Rechazados
- ✅ **Aprobar y rechazar pagos** (con comentario al rechazar), igual que el secretario
- ✅ **Reabrir un pago aprobado**, con motivo obligatorio (esto solo lo hace el administrador)
- ✅ Reportes de ingresos consolidados y dashboard completo
- ✅ Emitir la **solvencia de retiro** y la **constancia de pertenencia**
- ✅ Gestión de administradores (crear, editar, ver)
- ✅ Gestión de cronogramas de entrenamiento (crear, editar, ver, **borrar**)
- ✅ Ve la bandeja **"Por revisar"** y su contador, con los pagos que puede conciliar
- ❌ **NO puede aprobar ni rechazar un pago que él mismo registró** (otra persona debe hacerlo)
- ❌ **NO puede borrar** pagos ni atletas (no existe la opción), ni a otros administradores ni su propia cuenta

> ⚠️ **Clave de Operaciones Especiales:** El rol Administrador requiere configurar una **clave numérica de 4 dígitos** al crear o editar su cuenta. Se solicita en un modal de confirmación cada vez que se intenta eliminar un atleta.

## 📝 Secretario (secretario)

**Operación diaria y conciliación bancaria.**

- ✅ Gestión de Atletas (crear, editar, ver)
- ✅ Ve el **detalle completo del estatus** de un atleta inactivo o suspendido (motivo, quién y cuándo) y lo registra al cambiarlo
- ✅ Generar cobros y editar los Pendientes o Rechazados
- ✅ **Bandeja "Por revisar"** (con contador en el menú): **aprobar** o **rechazar con comentario** los pagos reportados
- ✅ Reportar un pago en nombre de un representante (efectivo o en taquilla)
- ✅ Reenviar el recibo por correo / WhatsApp de un pago aprobado
- ✅ Emitir la **solvencia de retiro** y la **constancia de pertenencia**
- ✅ Ver cronogramas, reportes y dashboard (con "Por cobrar" e ingresos aprobados)
- ❌ **NO puede aprobar ni rechazar un pago que él mismo registró** (lo concilia otro secretario o un administrador)
- ❌ **NO puede reabrir** un pago aprobado ni editar uno En Revisión o Aprobado
- ❌ **NO puede** crear conceptos, cambiar tarifas ni editar los datos bancarios de la escuela
- ❌ **NO puede** gestionar cuentas administradoras

## 🏋️ Entrenador (entrenador)

**Consulta de atletas, inventario y cronogramas propios. Sin acceso a dinero.**

- ✅ Ver la **lista de Atletas con su estatus** (Activo / Inactivo / Suspendido) y filtrar por estatus: así sabe quiénes pueden participar. No hay una pantalla aparte de solvencia
- ✅ Crear, editar y borrar sus **propios** cronogramas de entrenamiento
- ✅ Ver detalles de Atletas e inventario
- ✅ De un atleta inactivo o suspendido ve **solo el estatus y desde cuándo**; ❌ **no ve el motivo** ni quién lo registró
- ✅ Acceso a reportes y dashboard (sin cifras de dinero)
- ❌ **NO ve montos, referencias bancarias ni comprobantes** de ningún pago, y **no tiene acceso al módulo de Pagos**: si intenta abrir Pagos, un pago o un recibo, vuelve al Dashboard
- ❌ **NO puede** crear, editar ni borrar atletas, pagos ni conceptos
- ❌ **NO puede** emitir solvencia de retiro ni constancia
- ❌ **NO puede** ver ni gestionar cronogramas de otros entrenadores

> ℹ️ Por decisión original del sistema, el entrenador puede ver el listado y el detalle del personal (correos, cédulas, teléfonos, contactos de emergencia). No ve contraseñas ni claves. Si la escuela prefiere ocultarlo, es un cambio en `AdminsController::index` y `view`.

## 👨‍👩‍👦 Representante (representante)

**Autogestión: solo ve y reporta lo de **sus** atletas.**

Un representante se vincula a sus atletas por la **cédula**: la cédula de su cuenta debe coincidir con la cédula del representante registrada en el atleta (`athletes.parent_id_card`). Esa coincidencia se consulta en la base de datos en cada petición, así que desactivar la cuenta o cambiar su cédula surte efecto de inmediato.

- ✅ Ver **solo sus atletas** y el estado de cuenta de cada uno
- ✅ Si su atleta está inactivo o suspendido ve **el estatus y desde cuándo**; ❌ **no ve el motivo** ni quién lo registró
- ✅ **Reportar un pago:** referencia, fecha, método y comprobante (captura)
- ✅ Volver a reportar un pago que fue rechazado
- ✅ Descargar el **recibo** de sus pagos aprobados
- ✅ Ver cronogramas de entrenamiento **activos** y su dashboard (solo sus atletas y pagos)
- ❌ **NO ve** atletas, pagos ni comprobantes de otras familias
- ❌ **NO puede** aprobar, rechazar, editar cobros, ni generar reportes
- ❌ **NO puede** descargar la ficha técnica (contiene datos médicos), ni siquiera la de su atleta
- ❌ **NO ve** cifras globales de la escuela

---

# 9. Base de Datos

## 🗄️ Tablas Principales

### athletes

Almacena información de los Atletas.

**Campos principales:**

- `id` (PK): Identificador único
- `first_name`: Nombre
- `last_name`: Apellido
- `id_card`: Cédula del atleta (opcional)
- `date_of_birth`: Fecha de nacimiento
- `age`: Edad calculada
- `parent_name`: Nombre del representante
- `parent_id_card`: Cédula del representante
- `parent_phone`: Teléfono (formato: 0414-1234567)
- `parent_email`: Email del representante
- `address`: Dirección
- `emergency_contact`: Contacto de emergencia
- `emergency_phone`: Teléfono de emergencia
- `medical_conditions`: Condiciones médicas
- `registration_date`: Fecha de inscripción
- `category`: Categoría (Infantil, Pre-juvenil, Juvenil, Junior)
- `status`: Estado (active, inactive, suspended)
- `status_reason`: Motivo del último cambio a inactivo/suspendido (máx. 500). `NULL` = no registrado (atletas anteriores a esta función) o atleta activo
- `status_changed_at`: Fecha y hora de ese cambio
- `status_changed_by` (FK → `admins`, `ON DELETE SET NULL`): quién lo hizo
- `status_changed_name`, `status_changed_role`: nombre y rol de quien lo hizo, **copiados** para que sigan legibles aunque la cuenta cambie. Para una solvencia de retiro el rol es `retiro`
- `withdrawn_at`: Fecha y hora del retiro formal (solvencia emitida); `NULL` si no está retirado
- `clearance_number`: Número de la solvencia de retiro, por ejemplo `SOL-2026-0001` (único)
- `clearance_issued_by`: Nombre de quien emitió la solvencia
- `jersey_number`: Número de jersey (único)
- `photo`: Ruta de la fotografía
- `enrollment_email_status` / `enrollment_email_sent_at` / `enrollment_email_error`: Estado del envío de la planilla de inscripción por correo
- `enrollment_whatsapp_status` / `enrollment_whatsapp_sent_at` / `enrollment_whatsapp_error`: Estado del envío de la planilla por WhatsApp
- `created_at`: Fecha de creación
- `updated_at`: Fecha de actualización

### clearance_counters

Lleva la cuenta de los números de solvencia de retiro. Una fila por año.

- `year` (PK): Año
- `last_number`: Último número de solvencia emitido ese año

**Nunca disminuye**, aunque se reactive al atleta y se anule su solvencia: así un número ya impreso no se vuelve a asignar a otro documento. El incremento es atómico, por lo que dos emisiones simultáneas nunca obtienen el mismo número.

### membership_certificates

Constancias de pertenencia emitidas (una fila por constancia).

- `id` (PK): Identificador único
- `certificate_number`: Número correlativo, por ejemplo `CON-2026-0001` (único)
- `athlete_id` (FK): Referencia al atleta (`ON DELETE CASCADE`)
- `issued_at`: Fecha y hora de emisión
- `valid_until`: Último día de vigencia (emisión + 30 días)
- `issued_by`: Nombre de quien la emitió (se guarda como texto)
- `email_status` / `email_sent_at` / `email_sent_to` / `email_error`: Resultado del envío por correo

### certificate_counters

Lleva la cuenta de los números de constancia de pertenencia. Una fila por año (`year` PK, `last_number`). **Nunca disminuye** y el incremento es atómico, igual que `clearance_counters`.

### payments

Almacena los cobros y pagos de los atletas.

**Campos principales:**

- `id` (PK), `athlete_id` (FK), `athlete_name`
- `concept_id` (FK → `payment_concepts`): qué se cobra (mensualidad, inscripción…)
- `amount`: monto de **este** cobro (se copia al crearse; cambiar la tarifa del concepto no lo altera)
- `reference_number`: **número de referencia bancaria completo, solo dígitos (4 a 20)**, guardado como **texto** para conservar los ceros del inicio (`004812377`). Tiene el índice `idx_payments_reference`: la búsqueda exacta y por prefijo no recorre la tabla
- `payment_method`: método reportado (`transferencia`, `pago_movil`, `efectivo`, `otro`)
- `due_date`, `payment_date`, `month`, `year`, `notes`
- `status`: `pending`, `in_review`, `approved` o `rejected` (`NOT NULL`, por defecto `pending`)
- `reported_by`, `reported_at`: quién reportó el pago y cuándo
- `reviewed_by`, `reviewed_at`, `review_comment`: quién lo aprobó/rechazó, cuándo y el motivo
- `receipt_image`: ruta del comprobante (solo accesible por `payments/receiptImage`, no por URL)
- `whatsapp_status`, `whatsapp_sent_at`, `whatsapp_error`, `email_status`, `email_sent_at`, `email_error`: estado de los envíos del recibo
- `created_at`, `updated_at`

### payment_concepts

Catálogo de lo que se cobra. `name` (único), `description`, `default_amount` (tarifa sugerida), `is_monthly`, `is_active`. **Nunca se borra** (los pagos lo referencian): se desactiva. Cambiar `default_amount` solo afecta a los cobros **nuevos**.

### school_bank_account

Datos bancarios de la escuela: **una sola fila** (`id = 1`, forzado con una restricción). Campos: `holder_name`, `holder_id` (cédula/RIF), `bank_name`, `account_type` (`corriente`/`ahorro`), `account_number` (20 dígitos), `mobile_bank`, `mobile_phone`, `mobile_id`, `updated_by` (FK → `admins`, `ON DELETE SET NULL`), `updated_by_name` (copia del nombre) y `updated_at`. **Todo es opcional** y la fila nace **vacía**: el sistema no inventa ningún dato bancario. Los datos reales **no viajan** en `database/escuela_beisbol_maporal.sql`.

### school_bank_account_history

Bitácora de cada cambio de los datos bancarios: `changed_by`, `changed_by_name`, `changed_by_role`, `changed_fields` (qué campos), `old_values` y `new_values` (JSON con el valor anterior y el nuevo **solo de los campos modificados**) y `created_at`. Permite detectar y revertir un desvío de cuenta. Solo se inserta; nunca se actualiza ni se borra desde la aplicación.

### payment_history

Bitácora **inmutable** de cada pago: `payment_id`, `from_status`, `to_status`, `action` (`create`, `report`, `approve`, `reject`, `reopen`, `migrate`), `actor_id`, `actor_name`, `actor_role`, `comment`, `amount` (vigente en ese momento) y `created_at`. El nombre y rol del actor se copian al registro para que el historial siga siendo legible aunque la cuenta cambie. La aplicación solo inserta; nunca actualiza ni borra.

Los pagos cobrados antes de existir este flujo quedaron registrados con la acción `migrate` y el actor "Migración del sistema": **no se inventó un aprobador** que nunca existió.

### admins

Almacena información de los administradores.

**Campos principales:**

- `id` (PK): Identificador único
- `username`: Nombre de usuario (único)
- `password`: Contraseña hasheada (bcrypt)
- `email`: Email (único)
- `full_name`: Nombre completo
- `role`: Rol (`administrador`, `secretario`, `entrenador`, `representante`)
- `operation_key`: Clave de operaciones especiales hasheada (bcrypt) — solo Administrador
- `status`: Estado (`active`, `inactive`)
- `reset_token`: Token de recuperación de contraseña
- `reset_token_expires_at`: Expiración del token
- Otros campos de información personal y contacto

### inventory_items

Almacena el inventario de equipamiento y materiales deportivos.

**Campos principales:**

- `id` (PK): Identificador único
- `name`: Nombre del ítem
- `quantity`: Cantidad actual en stock (se modifica solo mediante movimientos)
- `min_quantity`: Cantidad mínima de alerta (0 = sin alerta)
- `created_by` / `created_by_name`: Quién registró el ítem
- `created_at` / `updated_at`: Fechas de registro y de última modificación

> Los campos `code`, `category`, `description`, `unit`, `condition`, `location`, `notes` y `status` se eliminaron para simplificar el módulo.

### inventory_movements

Registra todos los movimientos de stock de cada ítem (trazabilidad completa).

**Campos principales:**

- `id` (PK): Identificador único
- `item_id` (FK → inventory_items): Ítem afectado
- `type`: Tipo de movimiento (`entrada`, `salida`, `ajuste`)
- `quantity`: Cantidad del movimiento
- `quantity_before`: Stock antes del movimiento
- `quantity_after`: Stock después del movimiento
- `reason`: Motivo del movimiento
- `registered_by` / `registered_by_name`: Quién registró el movimiento
- `created_at`: Fecha y hora del movimiento

## 📊 Vistas de Base de Datos

### dashboard_stats

Totales para consulta externa: atletas, atletas activos, pagos **aprobados** e ingresos **aprobados**.

### payment_reports

Une pagos con atletas, con el `status` del pago (`pending`, `in_review`, `approved`, `rejected`).

> ℹ️ Ninguna pantalla de la aplicación consulta estas vistas (los reportes usan el modelo `Payment`); existen para consultas directas. La migración al flujo de estados las dejó devolviendo **0 ingresos** porque filtraban por el antiguo estado `paid`; se corrigieron. El archivo `database/escuela_beisbol_maporal.sql` las define sin prefijo de base de datos y con `SQL SECURITY INVOKER` para que se importen en cualquier servidor.

## 📋 Categorías de Atletas

| Categoría   | Rango de Edad |
| ----------- | ------------- |
| Infantil    | 8-10 años     |
| Pre-juvenil | 11-12 años    |
| Juvenil     | 13-15 años    |
| Junior      | 16-18 años    |

---

# 10. Guía de Uso

## 🎯 Acceso al Sistema

### Primer Acceso

1. Abrir navegador web (Chrome, Firefox, Edge)
2. Ingresar la URL del sistema (la de `base_url` en `config/app.php`)
3. Iniciar sesión con la cuenta de administrador que te entregó quien instaló el sistema
4. ⚠️ **Cambiar la contraseña y la clave de operaciones en el primer acceso** (Administradores → Editar)

> 🔒 **Ya no existe una contraseña "por defecto".** Las versiones anteriores de este documento indicaban `admin` / `admin123`: **esa contraseña ya no funciona** en el sistema actual y no debe usarse nunca. Si instalas el sistema desde cero con el SQL de referencia, cambia la contraseña de la cuenta `admin` **antes** de exponer el sistema.

### Interfaz Principal

Una vez autenticado, verá:

- **Barra de navegación superior** - Acceso a módulos principales
- **Menú lateral** - Opciones del módulo actual
- **Dashboard** - Estadísticas y resumen general
- **Botón de usuario** - Perfil y cierre de sesión

---

## 👨‍🎓 Gestión de Atletas

### Crear Nuevo Atleta

**Paso 1: Acceder al formulario**

1. Hacer clic en **Atleta** en el menú principal
2. Hacer clic en botón **+ Nuevo Atleta** (esquina superior derecha)
3. Se abrirá el formulario de registro

**Paso 2: Completar información personal**

- **Nombre** (\*): Nombre del atleta
- **Apellido** (\*): Apellido del atleta
- **Cédula** (opcional): Número de cédula del atleta
- **Fecha de Nacimiento** (\*): Seleccionar del calendario
- **Categoría** (\*): Seleccionar según edad:
  - Infantil (8-10 años)
  - Pre-juvenil (11-12 años)
  - Juvenil (13-15 años)
  - Junior (16-18 años)
- **Número de Jersey** (\*): Número de Jersey
- **Estado** (\*): Seleccionar "Activo" para nuevos atletas

**Paso 3: Información del representante**

- **Nombre del Representante** (\*): Nombre completo
- **Cédula del Representante** (\*): Número de cédula
- **Teléfono** (\*):
  - Seleccionar código de área (0412, 0414, 0416, 0424, 0426, 0422)
  - Ingresar 7 dígitos numéricos
  - Formato final: 0414-1234567
- **Email** (\*): Correo electrónico válido

**Paso 4: Información de contacto**

- **Dirección**: Dirección completa
- **Contacto de Emergencia**: Nombre de persona a contactar
- **Teléfono de Emergencia**: Teléfono de emergencia

**Paso 5: Información médica**

- **Condiciones Médicas**: Alergias, enfermedades crónicas, etc.

**Paso 6: Fotografía (opcional)**

1. Hacer clic en **Seleccionar Fotografía**
2. Elegir archivo JPG, JPEG o PNG (máx. 2MB)
3. Se mostrará vista previa
4. El sistema valida automáticamente formato y tamaño

**Paso 7: Guardar**

1. Revisar todos los datos
2. Hacer clic en **Guardar Atleta**
3. Se mostrará mensaje de confirmación
4. Se redirigirá a la Lista de Atletas

### Buscar Atletas

**Búsqueda Rápida:**

1. En la Lista de Atletas, localizar campo de búsqueda en la parte superior
2. Escribir mínimo 2 caracteres
3. El sistema buscará en:
   - Nombre completo del atleta
   - Cédula del atleta
   - Cédula del representante
4. Se mostrarán resultados en tiempo real con autocompletado
5. Hacer clic en resultado para seleccionar

**Filtros Avanzados:**

1. Usar los filtros disponibles:
   - **Categoría**: Filtrar por categoría de edad
   - **Estado**: Activo, Inactivo, Suspendido
   - **Edad**: Filtrar por edad específica
2. Combinar múltiples filtros
3. Hacer clic en **Aplicar Filtros**

### Ver Detalles del atleta

1. En la lista, hacer clic en el nombre del atleta
2. Se abrirá página con todos los detalles
3. Opciones disponibles:
   - **Editar** - Modificar información
   - **Eliminar** - Borrar registro (solo administradores)
   - **Generar Ficha PDF** - Descargar ficha individual

### Editar atleta

1. En la lista, hacer clic en ícono de **lápiz** (editar)
2. O desde detalles, hacer clic en **Editar**
3. Modificar los campos necesarios
4. Para cambiar fotografía:
   - Hacer clic en **Cambiar Fotografía**
   - Seleccionar nueva imagen
   - La foto anterior se eliminará automáticamente
5. Si cambia el **Estatus** a Inactivo o Suspendido, aparece el campo **Motivo del cambio** y es obligatorio (ver *Detalle del estatus de un atleta*)
6. Hacer clic en **Guardar Cambios**
7. Se mostrará confirmación

### Solvencia de retiro

La solvencia es el documento que se entrega cuando un atleta se retira de la escuela. Sustituye a la eliminación de atletas, que está deshabilitada, y **conserva todo su historial**.

**Quién puede emitirla:** administrador y secretario. El entrenador puede descargar una solvencia ya emitida, pero no emitirla.

**Regla:** solo se emite si el atleta **no tiene ningún pago pendiente**. Un pago pendiente bloquea la solvencia aunque todavía no haya vencido.

**Pasos para emitirla:**

1. Abrir el detalle del atleta y, en el bloque **Estado y retiro**, hacer clic en **Solvencia de retiro**
2. Si el atleta tiene pagos pendientes, el sistema muestra cuáles son (período, vencimiento y monto) y el total adeudado, con un enlace a cada pago. No se puede continuar hasta saldarlos
3. Si no debe nada, aparece la confirmación con un aviso: **al emitirla, el atleta quedará inactivo**
4. Si el atleta no tiene **ningún pago registrado**, se muestra además un aviso: el sistema solo conoce los pagos que se registraron, así que conviene confirmar que no adeuda mensualidades
5. Hacer clic en **Emitir solvencia y retirar al atleta**. Se descarga el PDF

**Qué ocurre al emitirla:**

- El atleta pasa a **Inactivo**, y deja de aparecer en el selector de atletas de torneos y en las estadísticas de atletas activos
- Se guarda la fecha del retiro, el número de solvencia (`SOL-AAAA-NNNN`, correlativo por año) y el nombre de quien la emitió
- En el detalle y en la lista aparece la insignia **Retirado el dd/mm/aaaa**
- Todo ocurre en una sola operación: si algo falla, el atleta no cambia. Además, el sistema **vuelve a comprobar los pagos justo al emitir**, de modo que un pago registrado en el último momento impide la emisión

**Reimprimir:** en el detalle de un atleta retirado, el botón **Descargar solvencia** genera el mismo documento, con el mismo número y la misma fecha de emisión. Cada impresión tiene su propio código QR de verificación.

**Reactivar a un atleta retirado:** al editar su ficha y cambiar el estado a **Activo**, el sistema **anula la solvencia** (borra la fecha, el número y el emisor) para no dejar a un atleta activo con una solvencia vigente. El formulario lo avisa antes de guardar. Si luego se vuelve a retirar, recibe un **número nuevo**: el anterior no se reutiliza. Editar a un atleta retirado sin cambiar su estado no anula nada.

**Verificación pública:** el QR del documento abre la página de verificación, que muestra "Solvencia de Retiro" con el número, la fecha de retiro, quién la emitió y los pagos cancelados.

> ⚠️ **Límite del sistema:** los pagos se registran a mano y no se generan mensualidades automáticamente. Un atleta al que nunca se le registró un pago aparece como "sin deuda". Por eso el sistema avisa cuando no hay pagos registrados.
>
> ⚠️ **Texto del documento:** la declaración del PDF es un texto estándar. La escuela debe revisarlo antes de entregarlo a un representante.

### Constancia de pertenencia

La constancia es el documento que se entrega cuando un representante pide una prueba de que su hijo pertenece a la escuela (para un colegio, un seguro, otro club...). A diferencia de la solvencia de retiro, **no cambia el estado del atleta**.

**Quién puede emitirla:** administrador y secretario. El entrenador no ve el botón ni puede descargarla.

**Regla principal: solo se emite a atletas ACTIVOS.** No se emite si el atleta está inactivo, suspendido o tiene una solvencia de retiro. Esa comprobación se hace en el servidor, dentro de una transacción, justo al emitir: aunque alguien fuerce la petición con el botón oculto, o desactive al atleta un instante antes, la emisión se rechaza.

**Cómo se usa:**

1. Abrir el detalle del atleta y, en el bloque **Constancia de pertenencia**, hacer clic en **Constancia**
2. Si el atleta no está activo, la pantalla explica por qué no se puede y no ofrece nada
3. Si está activo, hay dos acciones:
   - **Generar y descargar constancia**: baja el PDF directamente
   - **Enviar constancia por correo**: la manda al correo del representante, que se muestra en pantalla antes de enviar
4. Si no hay un correo válido registrado, la pantalla lo avisa y no ofrece el envío

**Qué contiene:** número de constancia (`CON-AAAA-NNNN`), fecha de emisión, nombre, cédula y categoría del atleta, fecha de inscripción, y nombre y cédula del representante, con una declaración de que el atleta está inscrito y activo. **No incluye** condiciones médicas, foto, teléfono, correo ni dirección.

**Vigencia de 30 días:** el documento declara su fecha de emisión y "válida hasta el dd/mm/aaaa". Pasada esa fecha se solicita una nueva. El último día de vigencia aún es válido.

**Reutilización:** si ya existe una constancia vigente del atleta, se entrega **esa misma** (mismo número y fecha) en lugar de emitir otra. Solo se emite una nueva cuando no hay ninguna o la anterior venció. Las constancias vencidas se conservan como historial. Dos personas que la pidan a la vez para el mismo atleta obtienen la misma constancia.

**Numeración:** `CON-2026-0001`, `CON-2026-0002`... correlativa por año, con un contador que **nunca disminuye** (tabla `certificate_counters`). Una emisión rechazada no consume número.

**Verificación pública:** el QR del documento abre la página de verificación, que muestra el número, la fecha de emisión y "válida hasta", e indica:

- **Constancia vigente** o **Constancia vencida** (un documento puede ser auténtico y estar vencido)
- Un aviso **"el atleta ya no figura como activo"** si el atleta dejó de estar activo después de emitida la constancia. Esa página es pública, así que **no revela el motivo** (retirado, suspendido...) ni ningún dato del atleta. El atleta consultado sale del documento firmado, nunca de la URL.

**Registro:** cada constancia queda guardada en la tabla `membership_certificates` con quién la emitió, cuándo y, si se envió por correo, a qué dirección y con qué resultado.

> ⚠️ **Límite del sistema:** una constancia ya entregada no se puede revocar. Si el atleta se retira dentro de los 30 días, el papel sigue mostrando su fecha. Por eso la verificación por QR avisa cuando el atleta ya no está activo; quien la reciba debe escanear el QR.
>
> ⚠️ **WhatsApp no está incluido.** Requiere una plantilla propia aprobada por Meta (las dos que existen hablan de inscripción y de pagos). Propuesta para cuando se quiera: nombre `constancia_pertenencia_pdf`, categoría Utility, idioma `es`, header Documento, pie "Mensaje automático, no responder", y el cuerpo `Hola {{1}}, adjuntamos la constancia de pertenencia de {{2}} a la Escuela de Beisbol Menor Maporal, emitida el {{3}}. Tiene una vigencia de 30 días.` (`{{1}}` representante, `{{2}}` atleta, `{{3}}` fecha de emisión).
>
> ⚠️ **Texto del documento:** la declaración del PDF es un texto estándar. La escuela debe revisarlo antes de entregarlo a un representante.

### Detalle del estatus de un atleta (por qué está inactivo o suspendido)

Cuando un atleta está **Inactivo** o **Suspendido**, el sistema guarda y muestra **por qué**, **quién** lo decidió y **desde cuándo**.

**Qué se registra.** Cada vez que un atleta pasa a Inactivo o Suspendido se guarda:

- el **motivo**, escrito por quien hace el cambio (obligatorio, entre 5 y 500 caracteres)
- **quién** lo hizo (nombre y rol, copiados al registro: siguen legibles aunque esa cuenta cambie o se elimine)
- **cuándo** (fecha y hora)

Solo se guarda **el último cambio**, no un historial completo: si un atleta pasa de Inactivo a Suspendido, el motivo nuevo reemplaza al anterior. Al **reactivar** a un atleta (Activo) el detalle se borra, porque un atleta activo no tiene motivo de inactividad, y reactivar **no pide motivo**.

**Cómo se registra.**

1. **Editar atleta** → cambiar el Estatus a Inactivo o Suspendido → aparece el campo **Motivo del cambio** (con contador de 500 caracteres) y es obligatorio
2. **Crear atleta** directamente como Inactivo o Suspendido (por ejemplo, un ingreso en lista de espera) también pide motivo
3. **Solvencia de retiro**: no se pide nada. El motivo queda registrado solo: *"Retiro formal de la escuela (solvencia SOL-AAAA-NNNN)"*, con el nombre de quien emitió la solvencia

El motivo solo se pide cuando el estatus **realmente cambia**: editar el teléfono de un atleta que ya está inactivo no obliga a justificarlo de nuevo ni pisa el detalle existente. Si el motivo falta, el formulario vuelve con el error **sin guardar ninguno de los demás cambios** (todo o nada), y esto lo valida el servidor, no solo el navegador.

**Qué ve cada rol.** El motivo puede ser sensible (disciplinario, de deuda…), así que no lo ve todo el mundo. Esta regla se aplica **antes de armar la pantalla**: para los roles sin permiso, el motivo ni siquiera llega al navegador (no está oculto con CSS ni en comentarios del HTML; se comprobó buscándolo en el HTML recibido).

| Dato | Administrador / Secretario | Entrenador | Representante (de su atleta) |
|---|---|---|---|
| Que está Inactivo / Suspendido | ✅ | ✅ | ✅ |
| Desde cuándo | ✅ | ✅ | ✅ |
| **Motivo** | ✅ | ❌ | ❌ |
| **Quién lo registró** | ✅ | ❌ | ❌ |
| "Retirado el …" (si hay solvencia) | ✅ + número y quién la emitió | ✅ solo "Retirado" | ✅ solo "Retirado" |
| Aviso | — | "No figura como activo en la escuela" | "No figura como activo…" / "No puede participar mientras esté suspendido" |

Se ve en dos sitios: en el **detalle del atleta** (bloque *Estado y retiro*, con el motivo completo y quién lo registró) y en la **lista de atletas** (bajo la etiqueta del estatus aparece "desde el dd/mm/aaaa", y para administrador y secretario el motivo sale al pasar el cursor sobre el ícono ⓘ). El representante, como siempre, solo ve a sus propios atletas.

**Atletas inactivos anteriores a este registro.** Los atletas que ya estaban inactivos cuando se agregó esta función (por ejemplo, los que se inactivaron a mano en 2025) **no tienen detalle**, porque nadie lo guardó. Se muestran como *"Motivo no registrado: este cambio se hizo antes de que el sistema guardara el detalle"* y en la lista como *"sin detalle registrado"*. **No se inventa ninguna fecha, motivo ni autor.** Para completarlo, basta con editar al atleta y volver a elegir su estatus (por ejemplo, pasar a Activo y de nuevo a Inactivo con su motivo).

> ⚠️ **Dónde NO aparece el motivo:** ni en la planilla de inscripción, ni en la ficha técnica, ni en los reportes (PDF o web), ni en la verificación pública por QR (que sigue diciendo solo "ya no figura como activo", sin motivo).

### Eliminar atleta

🚫 **Función deshabilitada actualmente** (`ALLOW_DELETE_ATHLETES = false` en `config/config.php`). Los pasos de abajo solo aplican si se reactiva.

⚠️ **Solo disponible para administradores — requiere Clave de Operaciones**

1. En la lista, hacer clic en ícono de **papelera** (eliminar)
2. Se abrirá un **modal de confirmación** solicitando la clave de operaciones especiales
3. Ingresar la **clave numérica de 4 dígitos** configurada en la cuenta del administrador
4. Hacer clic en **Eliminar** para confirmar
5. Si la clave es incorrecta, el sistema muestra un error y permite reintentar
6. Si la clave es correcta, el atleta se eliminará permanentemente
7. Se eliminarán también todos sus pagos asociados (eliminación en cascada)

> 🔒 **Seguridad:** La clave de operaciones se verifica en el servidor mediante bcrypt. El ícono de papelera no aparece para roles distintos al Administrador.

---

## 💳 Gestión de Pagos

### Generar un cobro (secretario / administrador)

1. **Pagos** → **+ Generar Cobro**
2. Buscar al atleta por cédula (del atleta o del representante)
3. Elegir el **concepto**; el monto se rellena con la tarifa, y puede ajustarse para este cobro
4. Indicar mes, año y fecha de vencimiento
5. **Generar cobro**. Nace como **Pendiente**: no existe una opción para crearlo ya pagado.

### Reportar un pago (representante)

1. **Mis pagos** → abrir el cobro pendiente
2. Antes del formulario aparece la tarjeta **Datos para realizar tu pago** (titular, cédula/RIF, banco y número de cuenta, y pago móvil): **deposita ahí** el monto del cobro
3. Completar el **número de referencia completo** (**solo números**, de 4 a 20 dígitos, **con los ceros del inicio** si los tiene, tal como aparece en el comprobante), el **método de pago**, la **fecha** y adjuntar la **captura** (JPG/PNG/WEBP, máx. 5 MB). Solo el pago en efectivo puede ir sin comprobante.
   - El campo **no deja escribir letras, espacios ni guiones**, y si se pega un texto con otros caracteres (por ejemplo copiado de un mensaje del banco) los quita y deja solo los dígitos. **Revisa que el número quede completo y correcto**: el sistema no puede saber cuál era el original.
   - Si ese número **ya fue reportado en otro pago**, el sistema lo rechaza: una referencia corresponde a una sola operación bancaria.
4. **Enviar a revisión**. El pago queda **En Revisión** y aparece en el contador de **Por revisar** del secretario y del administrador, y se **avisa por correo a los secretarios y administradores** activos (menos a quien lo registró).

Si el pago es **rechazado**, el representante recibe un correo con el motivo, lo ve en la ficha del pago y puede volver a reportarlo.

### Conciliar un pago (secretario o administrador)

> 🔔 **Aviso de pagos nuevos:** en el menú, **Por revisar** muestra un **contador rojo** con los pagos que ese usuario puede conciliar, igual para el **secretario y el administrador**. No cuenta los que él mismo registró (no podría aprobarlos); en la bandeja esos llevan el botón **Ver** y la marca "Lo registraste tú". El contador se actualiza al cargar o recargar cualquier pantalla; no hay actualización automática. **Además**, cada vez que se reporta un pago se envía un **correo a los secretarios y administradores activos** (excepto a quien lo registró, que no podría conciliarlo). El correo solo dice que hay un pago por revisar —atleta, concepto, monto y quién lo reportó—, **sin referencia bancaria ni comprobante**: esos datos se ven dentro del sistema. Los destinatarios salen de los roles que el modelo autoriza a aprobar (`PaymentWorkflow::rolesFor('approve')`): si esa regla cambia, el aviso la sigue sin tocar más código. No se envía al entrenador ni al representante. Si el envío falla, el reporte del pago **no** se revierte: el error queda en el log y el contador sigue avisando.

1. **Por revisar** muestra los pagos En Revisión, del más antiguo al más reciente
2. Abrir el pago, comprobar la **referencia** (se puede localizar escribiéndola en el cuadro de búsqueda de Pagos) contra la cuenta de la escuela y revisar la captura
3. **Aprobar** (nota opcional) → se envía el recibo por correo y WhatsApp
4. o **Rechazar** → el motivo es obligatorio y se envía por correo al representante

No se puede aprobar ni rechazar un pago que uno mismo registró; la pantalla lo explica y lo debe conciliar otra persona (otro secretario o un administrador). Por eso conviene que existan **al menos dos personas** con permiso de conciliar.

### Reabrir un pago aprobado (administrador)

Solo para corregir un error de conciliación. **Reabrir pago** → escribir el motivo (obligatorio). El pago vuelve a **En Revisión**, el recibo anterior queda sin efecto y se reinicia el estado de sus envíos. Un secretario debe volver a conciliarlo.

### Historial de un pago

La ficha de cada pago muestra una línea de tiempo con cada cambio: quién, su rol, cuándo, de qué estado a cuál y el comentario.

### Qué ve el entrenador de los atletas

El entrenador no entra a Pagos. Para saber quiénes pueden participar usa **Atletas**: ahí ve a todos con su estatus y puede filtrar por **Activo** o **Inactivo**. Del detalle de un atleta inactivo o suspendido ve el estatus y desde cuándo, sin el motivo ni datos de dinero.

### Datos bancarios de la escuela (administrador)

En **Conceptos**, debajo de la lista, está el bloque **Datos bancarios de la escuela**. Es **uno solo para todos los conceptos** (mensualidad, inscripción, uniforme…): si la cuenta cambia, se cambia una vez.

**Qué se registra** (todo es opcional; se puede cargar solo transferencia, solo pago móvil o ambos):

| Grupo | Campos |
|---|---|
| Titular | Titular de la cuenta · Cédula / RIF del titular (obligatorios si hay cualquier dato de pago) |
| Transferencia bancaria | Banco · Tipo de cuenta (corriente / ahorro) · N.° de cuenta (**20 dígitos exactos**) |
| Pago móvil | Banco · Teléfono (`0414-1234567`; operadoras 0412, 0414, 0416, 0424, 0426, 0422) · Cédula / RIF |

- **Cada grupo debe estar completo si se empieza:** no se guarda "media transferencia", que confundiría a la familia.
- **El sistema acepta formatos distintos y los deja siempre igual:** `0414 123 4567` → `0414-1234567`; `V12345678` o `12345678` → `V-12345678`; `j 12345678 9` → `J-12345678-9`. El representante copia estos datos al banco, y un formato ambiguo causa errores de depósito.
- Mientras **no se carguen**, el representante ve el aviso *"Datos de pago no disponibles: la escuela aún no ha registrado sus datos bancarios"*. **El sistema nunca inventa datos bancarios.**
- Si algo no es válido, el formulario vuelve **conservando lo que escribiste** y explica el error concreto.

**Qué ve el representante.** Al abrir un cobro que puede reportar, justo **encima del formulario** donde escribe la referencia, aparece la tarjeta **"Datos para realizar tu pago"** con el titular, la cédula/RIF, la transferencia y el pago móvil, y le recuerda el monto de **ese** cobro. El mismo bloque lo ve el secretario o el administrador cuando reportan un pago en nombre de un representante.

**Dónde NO aparece** (a propósito, para exponer el dato lo mínimo): ni en un pago ya reportado o aprobado, ni en la lista de pagos, el dashboard, la bandeja de revisión, los recibos ni los PDF. Tampoco el entrenador (que no entra a Pagos) ni nadie sin sesión.

> 🔒 **Por qué se registra cada cambio.** Quien cambiara el número de cuenta haría que las familias depositen en otra cuenta. Por eso **cada cambio queda guardado** (tabla `school_bank_account_history`) con **quién lo hizo, su rol, cuándo y el valor anterior y el nuevo** de cada campo modificado. Guardar sin cambiar nada no genera registro. El bloque muestra los últimos 6 cambios.

**Cómo auditar o revertir un cambio indebido** (desde la base de datos):

```sql
-- Ver quién cambió qué y cuándo (lo más reciente primero)
SELECT created_at, changed_by_name, changed_by_role, changed_fields, old_values, new_values
FROM school_bank_account_history ORDER BY id DESC;
```

`old_values` guarda el valor anterior de cada campo modificado, para restaurarlo desde Conceptos (o con un `UPDATE school_bank_account ...`).

> ⚠️ **Tras una instalación nueva los datos bancarios están vacíos.** Los representantes verán el aviso hasta que un administrador los cargue en Conceptos. Cárgalos **antes** de abrir el sistema a las familias.

### Conceptos de cobro (administrador)

**Conceptos** permite crear conceptos, cambiar su tarifa y desactivarlos. Cambiar una tarifa **no modifica** los cobros ya generados.

### Editar un cobro

Solo mientras esté **Pendiente** o **Rechazado**. Un pago En Revisión o Aprobado está cerrado: para corregirlo hay que rechazarlo o reabrirlo.

### Filtrar la lista de pagos y buscar por referencia

**Filtros:** estado (Pendiente, En Revisión, Aprobado, Rechazado), mes, año, categoría y edad. Todos se **combinan** entre sí y con el cuadro de búsqueda.

**Cuadro de búsqueda.** Lo que se escribe se busca como **texto literal** (`%`, `_` y `\` no actúan como comodines):

| Quién | Qué busca |
|---|---|
| **Administrador y secretario** | **Cédula** del atleta o del representante **y** **número de referencia** del pago |
| **Representante** | Solo cédula, y siempre dentro de **sus** atletas |
| **Entrenador** | No entra a Pagos |

**Buscar por referencia (administrador y secretario):**

- Escribe **al menos 4 dígitos** de la referencia: el número **completo**, o **cualquier parte** (los primeros, los últimos o los del medio). Con menos de 4 dígitos solo se busca por cédula, para no devolver medio sistema.
- Una coincidencia **exacta** de referencia sale **primero**. El número buscado se **resalta** dentro de la referencia que muestra cada fila.
- **Un número puede ser a la vez una cédula y una referencia** (ambas son solo dígitos). Para que no se confundan, cada fila indica **por qué salió**: *Referencia exacta*, *Coincide por referencia*, *Coincide por cédula* o *Referencia y cédula*.
- La referencia se muestra en la fila (`Ref. 0048123770`) y en el detalle del pago (*Referencia reportada*) y en la bandeja *Por revisar*.
- **Los ceros del inicio son parte de la referencia** (`004812377` no es `4812377`). Para buscar basta **cualquier parte contigua** del número (por ejemplo `12377` o `48123`), con o sin los ceros del principio; pero si escribes el número **completo**, debe llevar exactamente los ceros que tiene (`0000048123770` no encuentra `0048123770`). Si no aparece, prueba con solo los últimos 4 a 6 dígitos.

> 🔒 **El representante no puede usar la referencia para ver pagos ajenos:** no tiene búsqueda por referencia, y aunque la tuviera, el alcance por sus atletas se aplica en la propia consulta a la base de datos, antes de devolver nada. Probado: escribir la referencia exacta de un pago de otra familia no muestra ninguna fila ni dato de ese pago.

### Eliminar un pago

🚫 **Ya no existe.** Un pago es un registro financiero con historial. Se corrige rechazándolo o reabriéndolo, nunca borrándolo.

---

## 📊 Generar Reportes

### Acceder a Reportes

1. Hacer clic en **Reportes** en menú principal
2. Se mostrarán opciones de reportes disponibles

### Tipos de Reportes

#### 1. Lista de Atletas

**Descripción:** Reporte en PDF con lista completa de Atletas

**Pasos:**

1. Hacer clic en **Lista de Atletas**
2. Filtros opcionales:
   - **Categoría**: Filtrar por categoría
   - **Estado**: Filtrar por estado
3. Hacer clic en **Generar PDF**
4. El archivo se descargará automáticamente

#### 2. Ficha Individual del atleta

**Descripción:** Reporte PDF con información completa y fotografía del atleta

**Pasos:**

1. Hacer clic en **Ficha Individual**
2. Buscar y seleccionar atleta
3. Hacer clic en **Generar PDF**
4. Se descargará ficha con:
   - Datos personales
   - Información del representante
   - Fotografía
   - Información médica

#### 3. Historial de Pagos por atleta

**Descripción:** Reporte PDF con historial completo de pagos de un atleta

**Pasos:**

1. Hacer clic en **Historial de Pagos**
2. Buscar y seleccionar atleta
3. Hacer clic en **Generar PDF**
4. Se descargará historial con:
   - Todos los pagos registrados
   - Estados y fechas
   - Totales

#### 4. Reporte Financiero

**Descripción:** Reporte PDF con análisis financiero general

**Pasos:**

1. Hacer clic en **Reporte Financiero**
2. Filtros opcionales:
   - **Período**: Día, Mes y Año
   - **Estado**: Pendiente, Pagado
   - **Categoría**: Filtrar por categoría
   - **Mes**: Mes específico
   - **Año**: Año específico
3. Hacer clic en **Generar PDF**
4. Se descargará reporte

---

## 📞 Formato de Teléfonos

### Formato Requerido

El sistema utiliza el formato de teléfonos móviles venezolanos:

**Estructura:** `CÓDIGO-NÚMERO`

**Códigos de área válidos:**

- `0412` - Digitel
- `0414` - Movistar
- `0416` - Movilnet
- `0424` - Movistar
- `0426` - Movilnet
- `0422` - Digitel

**Número:** Exactamente 7 dígitos numéricos

**Ejemplos válidos:**

- 0414-1234567
- 0412-9876543
- 0424-5555555

**Ejemplos inválidos:**

- 0414-123456 (solo 6 dígitos)
- 0414-12345678 (8 dígitos)
- 414-1234567 (falta el 0)

---

## 🖼️ Gestión de Fotografías

### Especificaciones Técnicas

- **Formatos permitidos:** JPG, JPEG, PNG
- **Tamaño máximo:** 2MB
- **Resolución recomendada:** 400x400px o superior
- **Almacenamiento:** Directorio `img/`
- **Nomenclatura:** `athlete_{uniqid}_{timestamp}.{ext}`

### Cómo Cargar Fotografía

**Durante creación de atleta:**

1. En formulario, localizar sección **Fotografía**
2. Hacer clic en **Seleccionar Fotografía**
3. Elegir archivo del equipo
4. Se mostrará vista previa
5. Si es correcta, guardar atleta

**Cambiar fotografía existente:**

1. Editar atleta
2. Hacer clic en **Cambiar Fotografía**
3. Seleccionar nueva imagen
4. La foto anterior se eliminará automáticamente
5. Guardar cambios

### Validaciones

El sistema valida automáticamente:

- ✅ Formato de archivo (JPG, JPEG, PNG)
- ✅ Tamaño máximo (2MB)
- ✅ Tipo MIME correcto
- ✅ Archivo no corrupto

**Si hay error:**

- Verificar formato del archivo
- Verificar tamaño (máx. 2MB)
- Intentar con otra imagen
- Consultar sección de Solución de Problemas

### Visualización

- **Lista de Atletas:** Miniatura circular 40x40px
- **Detalles del atleta:** Imagen 192x192px centrada
- **Reportes PDF:** Imagen incluida en ficha del atleta
- **Calidad:** Se mantiene resolución original

---

## 👥 Gestión de Administradores

### Crear Nuevo Administrador

⚠️ **Solo disponible para administradores**

1. Hacer clic en **Administradores** en menú principal
2. Hacer clic en **+ Nuevo Administrador**
3. Completar formulario:
   - **Nombre de usuario** (\*): Único en el sistema
   - **Contraseña** (\*): Mínimo 8 caracteres
   - **Email** (\*): Correo válido y único
   - **Nombre completo** (\*): Nombre real
   - **Rol** (\*): Seleccionar rol (administrador, secretario, entrenador)
   - **Estado** (\*): Activo o Inactivo
4. Hacer clic en **Guardar Administrador**

### Editar Administrador

⚠️ **Solo disponible para administradores**

1. Hacer clic en ícono de **lápiz** (editar)
2. Modificar campos necesarios
3. Hacer clic en **Guardar Cambios**

### Eliminar Secretario-Entrenador

⚠️ **Solo disponible para administradores**

1. Hacer clic en ícono de **papelera** (eliminar)
2. Confirmar eliminación
3. El usuario se eliminará permanentemente

---

## 🔐 Gestión de Sesión

### Mantener Sesión Activa

- El sistema cierra sesión automáticamente después de **10 minutos de inactividad**
- Se mostrará advertencia **30 segundos antes** del cierre
- Para mantener sesión activa:
  1. Hacer clic en **Mantener Sesión** en el diálogo
  2. O continuar usando el sistema

### Cerrar Sesión

1. Hacer clic en **nombre de usuario** (esquina superior derecha)
2. Hacer clic en **Cerrar Sesión**
3. Se redirigirá a página de login

### Recuperar Contraseña

1. En página de login, hacer clic en **¿Olvidó su contraseña?**
2. Ingresar email registrado
3. Se enviará enlace de recuperación por email
4. Hacer clic en enlace del email
5. Ingresar nueva contraseña
6. Confirmar cambio

---

## 📋 Dashboard

### Información Disponible

El dashboard muestra:

- **Estadísticas generales:**

  - Total de atletas
  - Atletas activos
  - Total de pagos
  - Pagos pendientes

- **Atletas por categoría:**

  - Gráfico con distribución
  - Números por categoría

- **Resumen de pagos:**

  - Pagos realizados
  - Pagos pendientes

- **Actividad reciente:**
  - Últimos atletas registrados
  - Últimos pagos registrados

### Actualización de Datos

- Los datos se actualizan automáticamente
- Hacer clic en **Actualizar al navegador** para forzar actualización
- Los cambios se reflejan en tiempo real

---

# 11. Arquitectura Técnica

## 🏗️ Patrón MVC (Modelo-Vista-Controlador)

El sistema implementa el patrón MVC de forma estricta:

```
┌─────────────┐
│   Cliente   │
└──────┬──────┘
       │ HTTP Request
       ▼
┌─────────────┐
│   Router    │ ◄── Analiza URL y determina ruta
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Controller  │ ◄── Lógica de negocio y flujo
└──────┬──────┘
       │
       ├──────► Model ◄── Interacción con BD
       │
       └──────► View  ◄── Renderizado HTML
```

## 📁 Qué va en cada carpeta

| Carpeta | Contiene | Regla |
|---|---|---|
| `models/` | `Model.php` (clase base) y un modelo por tabla (`Athlete`, `Payment`, `Admin`...) | Acceden a la base de datos. **No** tocan `$_SESSION`, `$_GET`/`$_POST`, `header()`, HTML ni `BASE_URL`. |
| `controllers/` | Un `*Controller.php` por módulo, más el núcleo MVC: `Controller.php`, `View.php` y `Router.php` | Reciben la petición, validan, llaman a modelos y servicios y devuelven una vista o JSON. **No** contienen SQL. |
| `services/` | Lógica de negocio y utilidades que no son modelo ni controlador: `Mailer`, `PdfGenerator` (trait), `AthleteEnrollmentService`, `EmailReceiptService`, `WhatsAppReceiptService`, `WhatsAppClient` | Usados por controladores y entre sí. Pueden usar modelos, pero no renderizan vistas ni redirigen. |
| `config/` | `config.php`, `database.php`, `mail.php`, `whatsapp.php` (+ plantillas `*.example.php`) | Configuración y credenciales. `mail.php` y `whatsapp.php` están en `.gitignore`. |
| `views/` | Vistas, layouts y `assets/` (CSS) | Sin lógica de negocio ni estilos en línea. |

**Autoload:** `config/config.php` registra un autoload que busca cada clase por nombre de archivo en `models/`, `controllers/`, `services/` y `config/` (en ese orden). Por eso **un nombre de clase no puede repetirse entre esas carpetas**. Una clase nueva se coloca en la carpeta que le corresponde y se encuentra sola; no hace falta `require_once` (el código existente conserva los suyos por claridad).

**Dónde poner código nuevo:** si accede a la base de datos, es un modelo; si atiende una URL, es un controlador; si lo necesitan varios controladores, habla con un servicio externo (correo, WhatsApp) o genera algo (PDF), es un servicio. Si es un archivo que devuelve un array de ajustes o credenciales, va en `config/`.

El trait `PdfGenerator` declara `namespace App\Core\Traits` y se importa con `use App\Core\Traits\PdfGenerator;`. El namespace es independiente de la carpeta (el autoload del proyecto no usa PSR-4), por eso sigue igual aunque el archivo esté en `services/`.

## 🎨 Patrones de Diseño

1. **MVC (Model-View-Controller)** - Separación de responsabilidades
2. **Active Record** - Modelos representan tablas de BD
3. **Front Controller** - `index.php` como único punto de entrada
4. **Template Method** - Clase base define estructura
5. **Dependency Injection** - Controladores reciben dependencias
6. **Trait (Composición)** - `PdfGenerator` reutiliza lógica

## 🎮 Controladores

### Clase Base: Controller.php

**Métodos principales:**

```php
protected function requireAuth()                    // Verificar autenticación
protected function requireRole(array $roles)        // Verificar rol específico
protected function loadModel(string $modelName)     // Cargar modelo
protected function redirect(string $url)            // Redireccionar
protected function jsonResponse(array $data)        // Respuesta JSON
```

### Controladores Específicos

- **AthletesController** - Gestión de Atletas
- **PaymentsController** - Gestión de pagos
- **AdminsController** - Gestión de administradores
- **AuthController** - Autenticación y sesiones
- **DashboardController** - Dashboard principal
- **ReportsController** - Generación de reportes
- **PasswordResetController** - Recuperación de contraseña

## 📊 Modelos

### Clase Base: Model.php

**Métodos principales:**

```php
public function findById($id)                       // Buscar por ID
public function findAll($conditions, $orderBy)      // Buscar todos
public function create($data)                       // Crear registro
public function update($id, $data)                  // Actualizar registro
public function delete($id)                         // Eliminar registro
```

### Modelos Específicos

- **Athlete** - Modelo de Atletas
- **Payment** - Modelo de pagos
- **Admin** - Modelo de administradores

## 🛣️ Sistema de Rutas

**Formato de URL:**

```
BASE_URL/controlador/accion?parametros
```

**Ejemplos:**

- `/athletes` → AthletesController::index()
- `/athletes/create` → AthletesController::create()
- `/athletes/edit?id=5` → AthletesController::edit()
- `/payments/markPaid` → PaymentsController::markPaid()

## 🔍 Búsqueda Inteligente

**Endpoint:** `athletes/autocompleteUnified`

**Búsqueda en:**

1. Nombre completo del atleta
2. Cédula del atleta
3. Cédula del representante

**Características:**

- ✅ Búsqueda en tiempo real (mínimo 2 caracteres)
- ✅ Resaltado de coincidencias
- ✅ Límite de 10 resultados
- ✅ Cierre automático al hacer clic fuera
- ✅ Navegación con teclado

---

# 12. Documentación de API

## 🔐 Autenticación

Todos los endpoints requieren autenticación mediante sesión PHP, excepto los endpoints de login y recuperación de contraseña.

**Headers requeridos:**

```
Cookie: PHPSESSID={session_id}
```

## 👤 Endpoints de Atletas

### GET /athletes

Lista todos los Atletas con filtros opcionales.

**Parámetros de consulta:**

- `q` (string, opcional): Búsqueda inteligente
- `status` (string, opcional): Estado (active|inactive|suspended)
- `category` (string, opcional): Categoría
- `age` (int, opcional): Edad específica

### POST /athletes/create

Crea un nuevo atleta.

**Content-Type:** `multipart/form-data`

**Parámetros principales:**

- `first_name` (string, requerido)
- `last_name` (string, requerido)
- `id_card` (string, opcional)
- `date_of_birth` (date, requerido)
- `parent_name` (string, requerido)
- `parent_id_card` (string, requerido)
- `parent_phone` (string, requerido)
- `parent_email` (email, requerido)
- `category` (string, requerido)
- `jersey_number` (int, requerido)
- `photo` (file, opcional)

### GET /athletes/autocompleteUnified?term={búsqueda}

Autocompletado inteligente de Atletas (AJAX).

**Respuesta JSON:**

```json
[
  {
    "id": 1,
    "first_name": "Juan",
    "last_name": "Pérez",
    "id_card": "12345678",
    "parent_id_card": "9876543"
  }
]
```

### POST /athletes/delete

Elimina un atleta (AJAX).

**Parámetros:** `id` (int, requerido)

**Respuesta JSON:**

```json
{
  "success": true
}
```

## 💰 Endpoints de Pagos

Todas las acciones que cambian un pago son **POST del mismo sitio** (se rechaza un `Origin`/`Referer` ajeno con `403`) y responden JSON `{success, message, code}`.

| Ruta | Método | Quién | Descripción |
|---|---|---|---|
| `/payments` | GET | administrador, secretario, representante (el entrenador vuelve al Dashboard) | Lista; un representante solo ve sus atletas |
| `/payments/view?id=` | GET | autenticado, con alcance | Detalle, acciones permitidas e historial |
| `/payments/create` | GET/POST | administrador, secretario | Genera un cobro (siempre `pending`) |
| `/payments/edit?id=` | GET/POST | administrador, secretario | Solo si está `pending` o `rejected` |
| `/payments/report` | POST | representante, secretario, administrador | Reporta un pago → `in_review` |
| `/payments/approve` | POST | **secretario o administrador** (no el que lo registró) | `in_review` → `approved`; envía el recibo |
| `/payments/reject` | POST | **secretario o administrador** (no el que lo registró) | `in_review` → `rejected`; motivo obligatorio |
| `/payments/reopen` | POST | **administrador** | `approved` → `in_review`; motivo obligatorio |
| `/payments/review` | GET | secretario, administrador | Bandeja de pagos En Revisión |
| `/payments/downloadReceipt?id=` | GET | autenticado, con alcance (no entrenador) | PDF; solo si está `approved` |
| `/payments/receiptImage?id=` | GET | autenticado, con alcance (no entrenador) | Sirve el comprobante |
| `/payments/resendEmail`, `/payments/resendWhatsApp` | POST | administrador, secretario | Reenvía el recibo de un pago aprobado |
| `/concepts`, `/concepts/create`, `/concepts/update`, `/concepts/toggle` | GET/POST | **administrador** | Conceptos y tarifas |
| `/concepts/bank` | POST (mismo sitio) | **administrador** | Guarda los datos bancarios de la escuela; valida, normaliza y registra el cambio en el historial |

**Códigos de respuesta de las transiciones:** `400` datos inválidos o motivo faltante · `403` rol sin permiso, autoaprobación u origen no permitido · `404` pago inexistente o ajeno · `405` método no permitido · `409` transición inválida, pago ya modificado o referencia duplicada (la misma referencia en otro pago En Revisión o Aprobado, con cualquier método). Una referencia con letras, guiones, espacios o de menos de 4 / más de 20 dígitos se rechaza con `error=reference` y el pago no cambia.

`payments/markPaid` y `payments/delete` **ya no existen**.

## 🔑 Endpoints de Autenticación

### POST /auth/login

Procesa el inicio de sesión.

**Parámetros:**

- `username` (string, requerido)
- `password` (string, requerido)

### GET /auth/logout

Cierra la sesión del usuario.

### POST /auth/keepAlive

Mantiene la sesión activa (AJAX).

## 📊 Endpoints de Reportes

### GET /reports/athletesList

Genera PDF con Lista de Atletas.

**Parámetros opcionales:**

- `category` (string): Filtrar por categoría
- `status` (string): Filtrar por estado

### GET /reports/athleteCard?id={id}

Genera PDF con ficha individual del atleta.

### GET /reports/paymentsList

Genera PDF con lista de pagos.

### GET /reports/paymentHistory?athlete_id={id}

Genera PDF con historial de pagos de un atleta.

### GET /reports/financialReport

Genera PDF con reporte financiero.

## 📋 Códigos de Estado HTTP

| Código | Significado           | Uso                   |
| ------ | --------------------- | --------------------- |
| 200    | OK                    | Petición exitosa      |
| 302    | Found                 | Redirección           |
| 400    | Bad Request           | Datos inválidos       |
| 401    | Unauthorized          | No autenticado        |
| 403    | Forbidden             | Sin permisos          |
| 404    | Not Found             | Recurso no encontrado |
| 500    | Internal Server Error | Error del servidor    |

---

# 13. Seguridad

## 🔒 Características de Seguridad Implementadas

### 1. Autenticación

- Contraseñas hasheadas con bcrypt
- Sesiones seguras con regeneración de ID
- Protección contra fijación de sesión

### 2. Autorización

- Control de acceso basado en roles
- Verificación de permisos en cada acción
- Restricción de rutas según rol

### 3. Validación

- Validación de entrada en cliente (JavaScript)
- Validación de entrada en servidor (PHP)
- Sanitización de datos antes de mostrar (htmlspecialchars)
- Prepared statements para prevenir SQL injection

### 4. Sesiones

- Cierre automático por inactividad (10 minutos)
- Advertencia 30 segundos antes del cierre
- Opción de mantener sesión activa

### 5. Archivos

- Validación de tipo MIME **y del contenido real** de las imágenes (no basta la extensión)
- Límite de tamaño de archivo (5 MB en comprobantes de pago)
- Nombres de archivo aleatorios para evitar sobrescritura y adivinación
- **Comprobantes de pago y fotos de atletas (menores de edad) no se sirven por URL directa**: el servidor responde `403` a `img/comprobantes/*` y a `img/atleta_*`. Se entregan solo por `payments/receiptImage?id=` y `athletes/photo?id=`, que exigen sesión y respetan el alcance del usuario (un representante solo ve lo de sus atletas). La ruta del archivo sale siempre de la base de datos, nunca del cliente
- Los PDF incrustan las fotos leyéndolas del disco, no por URL

### 6. Integridad de Documentos PDF (QR de Verificación)

Todos los PDFs exportados incorporan un mecanismo de verificación de integridad que protege contra la falsificación y alteración de documentos impresos:

- **Token único** generado con `random_bytes(32)` (64 caracteres hexadecimales criptográficamente seguros)
- **HMAC-SHA256** calculado sobre el payload JSON de los datos clave del reporte, firmado con la clave `hmac_key` de `config/app.php` (**fuera del código**). Un documento es auténtico si su huella coincide con la clave actual o con una anterior listada en `hmac_previous_keys`: así rotar la clave no invalida los documentos ya emitidos
- **Registro en base de datos** con timestamp, usuario generador y contador de verificaciones
- **Código QR** embebido en el PDF apuntando a la URL pública de verificación: `BASE_URL/verify?token={token}`
- **Página de verificación pública** (`/verify`) — no requiere autenticación, accesible por cualquier persona que escanee el QR
- **Comparación `hash_equals`** (resistente a timing attacks) al momento de verificar

El sistema detecta cualquier alteración realizada con editores PDF externos porque los datos del impreso deben coincidir con los datos originales almacenados y protegidos por el HMAC en la base de datos.

**Archivos involucrados:**
- `services/PdfGenerator.php` — genera QR y registra verificación
- `models/PdfVerification.php` — CRUD de registros de verificación
- `controllers/VerifyController.php` — controlador público de verificación
- `views/verify/index.php` — página de resultado de verificación
- `views/reports/pdf/layouts/main.php` — layout PDF con bloque QR embebido

### 7. Entorno, servidor y envíos

- **Errores ocultos en producción** (`env` = `production`): se registran en el log del servidor, no se muestran. Un `app.php` ausente o roto arranca en este modo seguro
- **Cookie de sesión** `secure` (solo HTTPS), `httponly` y `SameSite=Lax`, acotada a la ruta del sistema
- **El servidor bloquea por URL** (`403`) las carpetas `config/`, `models/`, `controllers/`, `services/`, `database/`, `vendor/` y `views/` (salvo `views/assets/`), y los archivos `.md`, `.sql`, `.log`, `composer.*`, `.gitignore`, etc. La regla es por **carpeta y tipo**, no archivo a archivo: un archivo nuevo queda protegido sin acordarse de añadirlo, y sigue protegido aunque PHP dejara de interpretarse por una avería (que serviría el código —con las credenciales— como texto)
- **Cabeceras de seguridad** (`X-Frame-Options`, `nosniff`, `Referrer-Policy`...) dentro de `<IfModule mod_headers.c>`: si el módulo no está, se omiten en vez de tumbar el sitio con un error 500
- **Los secretos no se versionan**: `config/app.php`, `database.php`, `mail.php`, `whatsapp.php`, los comprobantes (`img/comprobantes/*`) y las fotos de atletas (`img/atleta_*`) están en `.gitignore`
- **Límite diario de envíos** automáticos por canal (ver sección 6)
- **Separación de funciones en pagos** y **detalle del estatus de atletas solo para administrador y secretario** (ver secciones 7 y 10)

## 🛡️ Protecciones de API

1. **CSRF Protection** - Validación de sesión en peticiones POST
2. **SQL Injection** - Uso exclusivo de prepared statements
3. **XSS Prevention** - Escape de salida con htmlspecialchars()
4. **File Upload Security** - Validación de tipo MIME y tamaño
5. **PDF Integrity** - HMAC-SHA256 + QR de verificación en todos los documentos exportados

---

# 14. Tecnologías Utilizadas

## Backend

- **PHP 8.2** - Lenguaje de programación principal
- **MySQL/MariaDB** - Sistema de gestión de base de datos
- **PDO** - Capa de abstracción de base de datos
- **PHPMailer** - Envío de correos electrónicos
- **Dompdf** - Generación de documentos PDF
- **endroid/qr-code** - Generación de códigos QR para verificación de documentos
- **HMAC-SHA256** (PHP nativo) - Firma criptográfica de integridad en PDFs exportados

## Frontend

- **HTML5** - Estructura de páginas
- **Hoja de estilos propia (`style.css`)** - Utilidades al estilo Tailwind definidas a mano, con el tema vinotinto del proyecto (no es el Tailwind completo)
- **Font Awesome 6.4** - Iconos (incluido en `style.css`, fuentes locales)
- **JavaScript Vanilla** - Interactividad y validaciones

## 🎨 Dónde vive la apariencia (CSS)

Toda la apariencia está en `views/assets/css/`, sin dependencias externas ni CDN:

```
views/assets/css/
├── style.css     ← ÚNICA hoja de estilos de la interfaz web
├── pdf.css       ← ÚNICA hoja de estilos de los PDF (Dompdf)
└── webfonts/     ← fuentes de Font Awesome
```

**Reglas del proyecto (arquitectura MVC):**

1. Las vistas (`views/**`) **no llevan bloques `<style>` ni atributos `style="..."`**. Si hace falta un estilo nuevo, se añade una clase en `style.css` y se usa en el HTML.
2. El JavaScript **no fija estilos** con `element.style.X`. Para mostrar u ocultar se alterna una clase: `.is-open` (modales, lightbox), `.hidden` (elementos) y `.is-visible` (mensajes de error del modal de clave). Para bloquear el scroll de la página se usa `.body-locked` en el `<body>`.
3. Antes de usar una clase utilitaria en una vista, comprobar que existe en `style.css`: es un Tailwind reducido, no completo. Una clase que no exista no produce ningún efecto (por ejemplo, un botón con `bg-green-600` y texto blanco queda invisible si esa clase no está definida).
4. El tema recolorea algunas familias de Tailwind: `blue-*` y `purple-*` son **vinotinto** (por eso `bg-blue-600` es `#8B0000`).
5. `colors.css` fijaba los tamaños de texto con `!important`; esas reglas se conservan agrupadas en la sección *Tipografía global* de `style.css` y afectan a toda la interfaz.

**Componentes reutilizables** (sección 7 de `style.css`): `btn-primary`, `btn-danger`, `btn-cancel`, `btn-disabled`, `card-hover`, `stats-card`, modales (`modal-overlay` + `modal-box`, `modal-title`, `modal-actions`...), modal de clave de operaciones (`pin-modal__*`) y `lightbox`.

**Pantallas de acceso** (sección 8 de `style.css`): el inicio de sesión y las tres pantallas de recuperación de contraseña (`auth/login`, `auth/password_request`, `auth/password_link_sent`, `auth/password_reset`) comparten una misma estructura con las clases `auth-*`: `auth-page` (contenedor a pantalla completa), `auth-brand` (panel vinotinto con el logo, el nombre y el lema), `auth-main` y `auth-card` (formulario), más `auth-field`, `auth-input`, `auth-submit`, `auth-alert` (`--error`, `--info`, `--success`) y `auth-link`. En escritorio son dos columnas y en móvil el panel pasa a una franja superior. Una pantalla nueva de acceso debe reutilizar estas clases, no definir las suyas. El logo (`img/logo-maporal.png`) mide solo 90 × 81 px, por eso se muestra a su tamaño natural sobre un disco blanco: ampliarlo lo pixela.

El login muestra un aviso cuando el sistema lo redirige con un parámetro en la URL: `?logged_out` (sesión cerrada), `?reason=inactive` (sesión cerrada por inactividad), `?error=invalid_token` (enlace de recuperación vencido) y `?status=password_updated` (contraseña actualizada). Los textos son fijos; nunca se muestra el valor recibido por la URL. Un error real de credenciales tiene prioridad sobre estos avisos. El botón del ojo alterna entre mostrar y ocultar la contraseña.

**Cache del navegador:** `.htaccess` guarda el CSS un mes. El `<link>` añade `?v=` con la fecha de modificación de `style.css`, así que al editarlo el navegador descarga la versión nueva sin pasos adicionales.

**PDF (`pdf.css`):** Dompdf recibe el HTML como texto y no carga CSS por URL, por eso `views/reports/pdf/layouts/main.php` lee `pdf.css` y lo incrusta en un `<style>`. Cada documento tiene una clase raíz `pdf-<vista>` puesta en `<html>` (por ejemplo `pdf-payment_receipt`), y sus reglas van acotadas con ese prefijo porque documentos distintos usan los mismos nombres de clase con tamaños distintos (cada uno está calibrado para caber en **una página**). Limitaciones de Dompdf, comprobadas: no soporta variables CSS (`var()`), `grid`, `calc()`, gradientes, `:where()` ni páginas con nombre en `@page`. No importar nada de `style.css` en un PDF.

## Arquitectura

- **Patrón MVC** - Modelo-Vista-Controlador
- **Enrutamiento personalizado** - Sistema de rutas limpio
- **Traits** - Reutilización de código (PdfGenerator)

---

# 15. Registro de Cambios

#### Guía de Uso Expandida y Mejorada

- **Sección de Acceso al Sistema:** Instrucciones claras para primer acceso y navegación de interfaz
- **Gestión de Atletas:** Desglose detallado en 7 subsecciones:
  - Crear nuevo atleta (7 pasos específicos con detalles de cada campo)
  - Buscar Atletas (búsqueda rápida y filtros avanzados)
  - Ver detalles del atleta
  - Editar atleta
  - Eliminar atleta
- **Gestión de Pagos:** Instrucciones paso a paso para:
  - Registrar nuevo pago (4 pasos detallados)
  - Ver lista de pagos
  - Filtrar pagos
  - Marcar pago como realizado (2 opciones diferentes)
  - Editar y eliminar pagos
- **Generar Reportes:** Detalles de 5 tipos de reportes con pasos específicos:
  - Lista de Atletas
  - Ficha individual del atleta
  - Reporte de pagos
  - Historial de pagos por atleta
  - Reporte financiero
- **Formato de Teléfonos:** Ejemplos válidos e inválidos para mayor claridad
- **Gestión de Fotografías:** Instrucciones detalladas de carga y validaciones automáticas
- **Gestión de Administradores:** Procedimientos completos para crear, editar y eliminar
- **Gestión de Sesión:** Información sobre mantenimiento, cierre y recuperación de contraseña
- **Dashboard:** Descripción completa de información disponible y actualización de datos

#### Tabla de Contenidos Actualizada

- Agregados enlaces a subsecciones de la Guía de Uso
- Mejor navegación dentro del documento

### 🎯 Cambios Específicos

- Versión actualizada a 1.0.0
- Instrucciones más claras y detalladas con pasos numerados
- Ejemplos prácticos en cada sección
- Indicadores visuales de campos obligatorios (\*)
- Notas de advertencia (⚠️) para acciones críticas
- Separadores visuales (---) para mejor organización
- Información de interfaz principal (barra de navegación, menú lateral, etc.)

### ✨ Nuevas Características

#### Búsqueda Inteligente

- Implementada búsqueda unificada en Gestión de Atletas
  - Búsqueda por nombre completo del atleta
  - Búsqueda por cédula del atleta
  - Búsqueda por cédula del representante
  - Autocompletado en tiempo real con resaltado de coincidencias
- Implementada búsqueda inteligente en Gestión de Pagos

#### Gestión de Fotografías

- Campo de carga de fotografía en formulario de Atletas
- Validación de formato (JPG, JPEG, PNG)
- Validación de tamaño (máximo 2MB)
- Vista previa en tiempo real al seleccionar imagen
- Almacenamiento en directorio `img/` con nombres únicos
- Visualización en lista, detalles y reportes PDF
- Eliminación automática de foto anterior al reemplazar

#### Formato de Teléfonos Venezolanos

- Campo dividido en dos partes:
  - Selector de código de área (0412, 0414, 0416, 0424, 0426, 0422)
  - Campo numérico de exactamente 7 dígitos
- Formato final: 0414-1234567
- Validación en cliente y servidor

### 🔧 Mejoras

- Mejorada validación de campos opcionales en JavaScript
- La cédula del atleta es opcional
- Validación mejorada de formato de teléfonos
- Validación de fotografías en cliente y servidor

### 🗄️ Base de Datos

- Agregada columna `photo` a tabla `athletes`

### 📝 Documentación

- Creados archivos de documentación completa
- Documentación de arquitectura del sistema
- Documentación de API endpoints
- Guía de instalación y uso

### 🔒 Seguridad

- Validación de tipo MIME para fotografías
- Validación de tamaño de archivo
- Nombres de archivo únicos
- Sanitización de rutas de archivo

---

## 🆕 Versión 1.1.0 — Mejoras de Seguridad y Nuevas Funcionalidades

### 🔐 Clave de Operaciones Especiales (Eliminaciones Protegidas)

Se implementó un sistema de doble confirmación para operaciones de eliminación de alto riesgo, disponible exclusivamente para el rol **Administrador**.

**Funcionamiento:**

- Al crear o editar una cuenta con rol Administrador, se configura una **clave numérica de 4 dígitos** (campo "Clave de Operaciones Especiales") que se almacena hasheada con bcrypt.
- Cada vez que el Administrador intenta **eliminar un atleta** o **eliminar un pago**, el sistema muestra un modal de confirmación que solicita esta clave antes de ejecutar la acción.
- La clave se verifica en el servidor — no puede ser omitida manipulando el HTML o el JavaScript del cliente.
- Si la clave es incorrecta, se muestra un mensaje de error y se puede reintentar sin cerrar el modal.

**Archivos involucrados:**
- `views/admins/create.php` y `edit.php` — sección de clave que aparece dinámicamente al seleccionar rol Administrador
- `controllers/AdminsController.php` — validación y almacenamiento hasheado de la clave; endpoint `verifyOperationKey`
- `models/Admin.php` — método `verifyOperationKey()` con `password_verify`
- `controllers/AthletesController.php` — verificación de clave en `delete()`
- `controllers/PaymentsController.php` — verificación de clave en `delete()`
- `views/athletes/index.php` — modal PIN para eliminar atleta
- `views/payments/index.php` — modal PIN para eliminar pago

**Campo en base de datos:** `admins.operation_key` (varchar 255, almacena hash bcrypt)

---

### 👥 Actualización de Roles de Usuarios

Se amplió el sistema de roles de **3 a 5 roles**:

| Rol | Valor en BD | Descripción |
|-----|-------------|-------------|
| Administrador | `administrador` | Control total + clave de operaciones para eliminar |
| Secretario | `secretario` | Gestión operativa sin permisos de eliminación |
| Entrenador | `entrenador` | Gestión de sus propios cronogramas + visualización |
| Representante | `representante` | Solo lectura: cronogramas activos, atletas y pagos |

**Cambios en base de datos:**
```sql
ALTER TABLE admins MODIFY COLUMN role 
  enum('administrador','secretario','entrenador','representante') 
  DEFAULT 'entrenador';
```

---

### 📅 Cronogramas de Entrenamiento

Nuevo módulo completo accesible desde el menú **Entrenamientos**:

- **Tabla `training_schedules`:** título, categoría, entrenador asignado, lugar, objetivo, notas, estado (activo/pausado/finalizado), fechas de inicio y fin.
- **Tabla `training_sessions`:** sesiones individuales con día de la semana, hora inicio/fin, actividad, intensidad (baja/media/alta) y materiales.
- Vista en **lista de tarjetas** y **calendario semanal** con código de colores por intensidad.
- Validación de **solapamiento de horarios** en el mismo día dentro del mismo cronograma.
- Los representantes ven únicamente cronogramas **activos** con la pestaña de calendario como vista predeterminada.

**Archivos nuevos:**
- `models/TrainingSchedule.php`
- `controllers/TrainingController.php`
- `views/training/index.php`, `create.php`, `edit.php`, `view.php`

---

### 📱 Verificación de PDFs con Código QR

Ver sección **6. Integridad de Documentos PDF** en el capítulo de Seguridad.

---

### ⏱️ Tiempo de Inactividad

Reducido el tiempo de cierre de sesión automático por inactividad de **10 minutos a 2 minutos** (120 segundos), con advertencia 30 segundos antes del cierre.

---

### 📦 Módulo de Inventario

Nuevo módulo completo accesible desde el menú **Inventario**, disponible para todos los roles excepto Representante.

**Funcionalidades:**

- **Registro de ítems** con nombre, código, categoría, descripción, ubicación, condición y estado
- **Control de stock** con cantidad actual y cantidad mínima de alerta
- **Movimientos de inventario**: Entradas (incremento de stock), Salidas (reducción de stock) y Ajustes (corrección del total)
- **Trazabilidad completa**: cada movimiento registra quién lo hizo, cuándo y por qué motivo
- **Alertas de stock bajo**: aviso visual cuando la cantidad cae por debajo del mínimo configurado
- **Historial de movimientos** por ítem con antes/después de cada operación
- **Filtros**: por categoría, condición, estado y alerta de stock bajo

**Permisos:**

| Acción | Administrador | Secretario | Entrenador | Representante |
|--------|:---:|:---:|:---:|:---:|
| Ver inventario y detalles | ✅ | ✅ | ✅ | ❌ |
| Registrar / Editar ítems | ✅ | ✅ | ❌ | ❌ |
| Registrar movimientos | ✅ | ✅ | ❌ | ❌ |
| Eliminar ítems | ✅ | ❌ | ❌ | ❌ |

**Tablas de base de datos:** `inventory_items`, `inventory_movements`

**Archivos nuevos:**
- `models/Inventory.php`
- `controllers/InventoryController.php`
- `views/inventory/index.php`, `create.php`, `edit.php`, `view.php`

---

---

## 🆕 Versión 1.2.0 — Correcciones, Mejoras de UI y Nuevas Funcionalidades (Junio 2026)

### 🐛 Corrección: Modal de eliminación de pagos no se mostraba

**Problema:** Al intentar eliminar un pago, el sistema se quedaba sin respuesta visual. El modal de confirmación nunca aparecía.

**Causa:** La función `deletePayment()` usaba `modal.classList.remove('hidden')` para mostrar el modal, pero el elemento tenía `style="display:none"` como atributo inline. Los estilos inline tienen mayor prioridad que las clases CSS, por lo que el modal permanecía oculto.

**Solución:** Se reemplazó `modal.classList.remove('hidden')` y `errEl.classList.add('hidden')` por `modal.style.display = 'flex'` y `errEl.style.display = 'none'`, consistente con el resto del código del modal.

**Archivo afectado:** `views/payments/index.php`

---

### 🐛 Corrección: Botón "Eliminar" quedaba bloqueado ante error de red

**Problema:** Si la petición AJAX de eliminación fallaba por un error de red, el botón quedaba permanentemente deshabilitado mostrando "Eliminando…", sin posibilidad de reintentar.

**Causa:** El `.catch()` del `fetch()` en `ajaxRequest()` solo registraba el error en consola y nunca llamaba al callback.

**Solución:** El `.catch()` ahora invoca `callback({ success: false, message: 'Error de conexión. Intente nuevamente.' })`, lo que reactiva el botón y muestra el mensaje de error en el modal.

**Archivo afectado:** `views/layouts/main.php` — función `ajaxRequest()`

---

### 🐛 Corrección: Símbolo `?>` visible en la esquina superior de la pantalla

**Problema:** Se mostraba el texto `?>` en la esquina superior izquierda de la interfaz en todas las páginas.

**Causa:** La etiqueta de cierre PHP `?>` al final de archivos puramente PHP (como `config/config.php` y `config/database.php`) hace que PHP emita ese texto como output antes del HTML. Aunque `ob_start()` estaba en `index.php`, otros flujos de inclusión podían escaparlo.

**Buena práctica aplicada:** En archivos PHP puros (sin HTML), se debe **omitir el `?>`** al final para evitar que cualquier whitespace o texto accidental se emita.

**Archivos corregidos (20 archivos):**

| Directorio | Archivos |
|---|---|
| `config/` | `config.php`, `database.php` |
| `controllers/` | `AuthController.php`, `Controller.php`, `DashboardController.php`, `InventoryController.php`, `Model.php`, `PaymentsController.php`, `ReportsController.php`, `Router.php`, `TrainingController.php`, `VerifyController.php`, `View.php` |
| `models/` | `Admin.php` (tenía dos `?>` seguidos), `Athlete.php`, `Inventory.php`, `Payment.php`, `PdfVerification.php`, `TrainingSchedule.php` |

---

### ✨ Nueva funcionalidad: Comprobante de pago adjunto

Se implementó la posibilidad de adjuntar una imagen del comprobante de pago en cada registro de pago.

**Características:**

- **Formatos permitidos:** JPG, PNG, WEBP (validación por MIME real, no por extensión)
- **Tamaño máximo:** 5 MB
- **Almacenamiento:** `img/comprobantes/` con nombre único `comprobante_{uniqid}_{timestamp}.ext`
- **Vista previa:** Al seleccionar el archivo se muestra miniatura en tiempo real
- **Reemplazo:** Al editar, se puede subir un nuevo comprobante (el anterior se elimina automáticamente) o marcar el checkbox "Eliminar comprobante actual"
- **Limpieza automática:** Al eliminar un pago, su comprobante adjunto se elimina del disco
- **Interfaz:** Botón `📎 Adjuntar comprobante` + nombre de archivo + botón `✕` para quitar + miniatura previa

**Vistas actualizadas:**

| Vista | Cambio |
|---|---|
| `payments/create.php` | Campo `enctype="multipart/form-data"` + botón de adjuntar con vista previa |
| `payments/edit.php` | Miniatura del comprobante actual + checkbox eliminar + botón adjuntar nuevo |
| `payments/view.php` | Muestra la imagen con efecto hover, lightbox al hacer clic, enlace "Ver en tamaño completo" y botón "Descargar" |
| `payments/index.php` | Nueva columna "Comprobante" con badge azul "📎 Adjunto" o "—" |

**Base de datos:**
```sql
ALTER TABLE payments ADD COLUMN receipt_image VARCHAR(255) DEFAULT NULL 
  COMMENT 'Ruta relativa al comprobante de pago adjunto' AFTER notes;
```

**Archivos afectados:**
- `models/Athlete.php` — el modelo base `create()`/`update()` ya es dinámico, sin cambios necesarios
- `controllers/PaymentsController.php` — métodos `create()`, `edit()`, `delete()` + nuevo método privado `handleReceiptUpload()`
- `views/payments/create.php`, `edit.php`, `view.php`, `index.php`
- `database/escuela_beisbol_maporal.sql` — columna `receipt_image` añadida al schema

---

### ✨ Mejora de UI: Header fijo sin saltos de layout

**Problema:** La barra de navegación superior se movía horizontalmente al navegar entre módulos que tienen scrollbar y módulos que no.

**Causa:** El scrollbar del navegador (~15-17px) aparece y desaparece según el contenido de cada página, desplazando el layout del header.

**Solución aplicada en `views/layouts/main.php`:**

1. **`scrollbar-gutter: stable` + `overflow-y: scroll`** en `html` — reserva permanentemente el espacio del scrollbar aunque la página no tenga scroll, eliminando el desplazamiento horizontal.
2. **`sticky top-0 z-40`** en el contenedor del header — el header permanece visible al hacer scroll en páginas largas.

---

### ✨ Mejora de UI: Foto del atleta en sugerencias del autocompletado con resaltado de coincidencias

**Antes:** Las sugerencias de búsqueda por cédula mostraban solo texto plano (nombre + cédulas), sin foto.

**Ahora:** Cada sugerencia muestra:
- La **foto circular** del atleta (36×36px) a la izquierda
- El **nombre** del atleta en negrita con las coincidencias **resaltadas en rojo**
- La **cédula** (atleta y/o representante) en gris con las coincidencias también **resaltadas en rojo**

**Detalles técnicos:**

- Si el atleta no tiene foto, se muestra un avatar SVG gris generado inline (sin dependencias de archivos externos)
- La foto se obtiene del campo `photo` incluido ahora en el endpoint `athletes/autocompleteUnified`
- El resaltado aplica el mismo regex que el sistema usaba antes para texto plano, ahora sobre el nombre y la cédula dentro del HTML personalizado del item
- Se agregó la función auxiliar `highlightTerm(text, term)` reutilizable en `main.php`
- `initAutocomplete()` pasa el `term` actual como segundo argumento al `resultFormatter`, permitiendo que cualquier formatter personalizado aplique su propio resaltado

**Archivos afectados:**

| Archivo | Cambio |
|---|---|
| `models/Athlete.php` | `searchAthletes()` — agregado `photo` al `SELECT` |
| `views/layouts/main.php` | Nueva función `highlightTerm()`; `athleteResultFormatter()` acepta `term` y aplica resaltado; `initAutocomplete()` pasa `term` al formatter |
| `views/athletes/index.php` | Formatter actualizado para recibir y reenviar `term` |
| `views/payments/index.php` | Formatter actualizado para recibir y reenviar `term` |
| `views/payments/create.php` | Formatter actualizado para recibir y reenviar `term` |
| `views/reports/index.php` | Formatter actualizado para recibir y reenviar `term` |

---

### 🔤 Cambio de terminología: "Estado" → "Estatus"

Se reemplazó la palabra **Estado** por **Estatus** en todos los labels, encabezados de tablas, vistas de detalle y reportes PDF del sistema, para referirse uniformemente a la condición de atletas, pagos, administradores, inventario y entrenamientos.

**Excepción preservada:** La etiqueta `'Mal Estado'` en el inventario (que describe condición física de equipamiento) no fue modificada ya que no hace referencia a un campo de base de datos de tipo `status`.

**Archivos actualizados (24 archivos):**

`views/athletes/index.php`, `create.php`, `edit.php` · `views/payments/index.php`, `create.php`, `edit.php`, `view.php` · `views/admins/index.php`, `create.php`, `edit.php`, `view.php` · `views/inventory/index.php`, `create.php`, `edit.php` · `views/training/create.php`, `edit.php` · `views/reports/index.php`, `_athletes_report.php`, `_payments_report.php`, `_payment_history_report.php`, `_financial_report.php` · `views/reports/pdf/athlete_list.php`, `payment_history.php`, `financial_report.php`

---

---

## 🆕 Versión 1.2.1 — Módulo de Cronograma de Torneos (Junio 2026)

### 🏆 Nuevo módulo: Cronograma de Torneos

Se implementó un módulo completo para la gestión de torneos y competencias deportivas accesible desde el menú **Torneos** (ícono de trofeo) para todos los roles autenticados.

**Funcionalidades:**

- **Registrar torneos** con nombre, categoría, lugar, fechas de inicio y fin, descripción y notas internas
- **Estatus automático** — el sistema actualiza automáticamente el estatus del torneo según las fechas:
  - `Programado` → `En Curso` cuando llega la fecha de inicio
  - `En Curso` → `Finalizado` cuando pasa la fecha de fin
- **Inscripción de atletas** — desde la vista de detalle del torneo, se pueden inscribir atletas activos como participantes con un selector con búsqueda
- **Retirar participantes** sin recargar la página (AJAX)
- **Filtros** por nombre/lugar/categoría, estatus, categoría y año
- **Estadísticas** — tarjetas con conteo de torneos totales, programados, en curso y finalizados
- **Eliminación protegida** con clave de operaciones de 4 dígitos (solo Administrador)
- **Vista de detalle** con foto circular de cada atleta inscrito, nombre, categoría y edad

**Estatus disponibles:**

| Estatus | Descripción |
|---|---|
| `Programado` | Torneo registrado, aún no ha comenzado |
| `En Curso` | Torneo actualmente en desarrollo |
| `Finalizado` | Torneo concluido |
| `Cancelado` | Torneo cancelado manualmente |

**Permisos:**

| Acción | Administrador | Secretario | Entrenador | Representante |
|---|:---:|:---:|:---:|:---:|
| Ver lista y detalles | ✅ | ✅ | ✅ | ✅ |
| Crear / Editar torneos | ✅ | ✅ | ❌ | ❌ |
| Inscribir / Retirar atletas | ✅ | ✅ | ❌ | ❌ |
| Eliminar torneos | ✅ | ❌ | ❌ | ❌ |

**Tablas de base de datos:**

```sql
-- Torneos
CREATE TABLE tournaments (
  id           int(11)       NOT NULL AUTO_INCREMENT,
  name         varchar(150)  NOT NULL,
  category     varchar(50)   NOT NULL,
  location     varchar(150)  NOT NULL,
  start_date   date          NOT NULL,
  end_date     date          DEFAULT NULL,
  description  text          DEFAULT NULL,
  notes        text          DEFAULT NULL,
  status       enum('programado','en_curso','finalizado','cancelado') DEFAULT 'programado',
  created_by   int(11)       DEFAULT NULL,  -- FK → admins.id ON DELETE SET NULL
  created_at   datetime      DEFAULT current_timestamp(),
  updated_at   datetime      DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
);

-- Participantes por torneo
CREATE TABLE tournament_participants (
  id            int(11) NOT NULL AUTO_INCREMENT,
  tournament_id int(11) NOT NULL,  -- FK → tournaments.id ON DELETE CASCADE
  athlete_id    int(11) NOT NULL,  -- FK → athletes.id ON DELETE CASCADE
  position      varchar(50)  DEFAULT NULL,
  result        varchar(100) DEFAULT NULL,
  notes         text         DEFAULT NULL,
  added_at      datetime     DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY uq_tournament_athlete (tournament_id, athlete_id)
);
```

**Archivos nuevos:**

- `models/Tournament.php` — modelo con métodos: `getTournamentsWithFilters()`, `getTournamentWithParticipants()`, `getParticipants()`, `addParticipant()`, `removeParticipant()`, `getStats()`, `updateStatusByDate()`, `isParticipant()`, `countParticipants()`
- `controllers/TournamentsController.php` — acciones: `index()`, `create()`, `edit()`, `view()`, `delete()`, `addParticipant()`, `removeParticipant()`
- `views/tournaments/index.php` — lista de torneos en tarjetas con filtros y estadísticas
- `views/tournaments/create.php` — formulario de registro
- `views/tournaments/edit.php` — formulario de edición
- `views/tournaments/view.php` — detalle con gestión de participantes en tiempo real (AJAX)

**Archivos modificados:**

- `views/layouts/main.php` — agregado ítem `Torneos` (fa-trophy) al menú de navegación desktop y móvil

---

## 🔮 Próximas Características (Roadmap)

### Planificadas para v1.2.0

- [ ] Exportación de reportes a Excel
- [ ] Notificaciones por email de pagos pendientes con fecha de vencimiento pasada
- [ ] Asistencia de Atletas
- [ ] Galería de fotos de eventos
- [ ] Estadísticas de rendimiento deportivo

### Planificadas para v1.3.0

- [ ] API REST completa
- [ ] Aplicación móvil
- [ ] Chat interno
- [ ] Notificaciones push

---

# 16. Solución de Problemas

## 🐛 Problemas Comunes

### Error al subir fotografías

**Solución:**

- Verificar permisos del directorio `img/` (755)
- Verificar tamaño del archivo (máx. 2MB)
- Verificar formato (solo JPG, JPEG, PNG)
- Verificar configuración PHP: `upload_max_filesize` y `post_max_size`

### Error de base de datos

**Solución:**

- Verificar credenciales en `config/database.php`
- Verificar que la base de datos existe
- Verificar que se ejecutaron todos los scripts SQL

### Sesión expira muy rápido

**Solución:**

- Ajustar tiempo de inactividad en `views/layouts/main.php`
- Buscar: `const inactivityTime = 120;` (valor actual: 2 minutos / 120 segundos)
- Aumentar el valor según necesidad (ej: `300` para 5 minutos, `600` para 10 minutos)

### Fotografías no se muestran

Las fotos de los atletas **no se sirven por URL directa**: se piden a `athletes/photo?id=N`, que exige sesión. Si no aparecen:

1. ¿Hay sesión iniciada? Sin sesión la foto devuelve `404` a propósito
2. ¿El archivo existe en `img/`? La ruta que guarda la base de datos (`athletes.photo`) debe apuntar a un archivo real llamado `atleta_*`
3. ¿El usuario es un representante? Solo ve las fotos de **sus** atletas (cédula del representante = cédula de su cuenta)
4. ¿Se cambió el nombre de un archivo a mano a algo que no empieza por `atleta_`? El endpoint solo sirve archivos `img/atleta_*`
5. Verificar permisos: `chmod 755 img/`

**No** abras `img/atleta_xxx.jpeg` directamente en el navegador para probar: devuelve `403` por diseño.

### Error: "Headers already sent"

**Solución:**

- Verificar que no haya espacios o BOM antes de `<?php`

### Autocompletado no funciona

**Solución:**

- Verificar consola del navegador
- Verificar que initAutocomplete esté definido

## 🔧 Verificar Extensión GD

Para verificar que GD está habilitada:

```php
<?php
if (extension_loaded('gd')) {
    echo "GD está habilitada\n";
    print_r(gd_info());
} else {
    echo "GD NO está habilitada\n";
}
?>
```

O desde la línea de comandos:

```bash
php -m | grep -i gd
```

## 🔄 Mantenimiento

### Tareas Periódicas

**Diarias:**

- Limpieza automática de fotografías huérfanas
- Limpieza de tokens de reseteo expirados

**Semanales:**

- Backup de base de datos
- Revisión de logs de errores

**Mensuales:**

- Optimización de tablas de BD

### Backup de Base de Datos

```bash
mysqldump -u usuario -p escuela_beisbol_maporal > backup_$(date +%Y%m%d).sql
```

---

# 17. Puesta en producción

Esta sección es el procedimiento para mover el sistema desde la máquina de desarrollo (XAMPP) a un servidor real. **Sigue el orden**: varios pasos no se pueden deshacer (un mensaje enviado a una familia, un QR impreso).

## 17.1 Antes de empezar: lo que NO puede esperar

| # | Qué | Por qué |
|---|---|---|
| 1 | **Cambiar la contraseña de la cuenta `admin`** | Hasta hace poco usaba `admin123`, publicada en este mismo documento. Si importas el SQL de referencia en un servidor nuevo, esa cuenta nace con una clave por defecto: cambiarla **antes** de abrir el sistema |
| 2 | **Cambiar la clave de operaciones de cada administrador** | Protege las acciones destructivas. Las cuentas actuales tienen un PIN trivial (`1234`...). Administradores → Editar |
| 3 | **No usar `root` de MySQL ni una base sin contraseña** | Ver 17.3 |
| 4 | **Regenerar las credenciales de Gmail y de Meta** si alguna vez se compartieron, se enviaron por correo o se subieron a un repositorio | Están en `config/mail.php` y `config/whatsapp.php` en texto plano |

## 17.2 Orden recomendado

1. **Servidor** con PHP 8+, MySQL/MariaDB, Apache con `mod_rewrite` (y, recomendado, `mod_headers`), extensiones `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `openssl`, `curl`
2. **Dominio y HTTPS funcionando** (certificado válido). **No actives `https => true` antes**: sin HTTPS la cookie "secure" no se envía y nadie podría iniciar sesión
3. **Copiar el sistema** al servidor (sin `config/app.php`, `database.php`, `mail.php`, `whatsapp.php`, sin `img/comprobantes/*` ni `img/atleta_*`: no se versionan)
4. **Base de datos** (17.3)
5. **`config/app.php`** (copiar de `app.example.php`):
   - `env` => `'production'`
   - `base_url` => la URL **definitiva** con `https://` y `/` final
   - `base_path` => `'/'` o `'/sistema/'`, igual que la ruta de `base_url`
   - `https` => `true`
   - `hmac_key` => nueva, generada con `php -r "echo bin2hex(random_bytes(32));"`
   - `hmac_previous_keys` => **vacío** en una instalación nueva. Si migras desde el sistema actual **con documentos ya emitidos**, copia aquí la `hmac_key` y las `hmac_previous_keys` del `app.php` antiguo para que sus QR sigan validándose
6. **`config/mail.php` y `config/whatsapp.php`** con las credenciales (ver 17.4 y 17.5)
7. **Permisos**: la carpeta `img/` y `img/comprobantes/` deben ser escribibles por el servidor web; los archivos de `config/` no
8. **Probar sin enviar nada** (17.6), luego **un solo envío de prueba**
9. **Recién entonces** emitir documentos reales

> ⚠️ **Fija la `base_url` antes de emitir documentos reales.** El QR de cada PDF contiene la URL que había al generarlo. Si después cambias de dominio, los QR ya impresos dejan de abrir la página de verificación (el documento sigue siendo auténtico, pero nadie podrá comprobarlo escaneándolo).

## 17.3 Base de datos

1. Crear la base e importar el esquema: `database/escuela_beisbol_maporal.sql` (importa limpio en una base vacía; incluye las tablas `send_log`, `send_counters` y `school_bank_account` **vacías**)
2. **Crear un usuario dedicado con permisos mínimos** (no `root`):

```sql
CREATE USER 'maporal_app'@'localhost' IDENTIFIED BY '<clave larga y aleatoria>';
GRANT SELECT, INSERT, UPDATE, DELETE ON escuela_beisbol_maporal.* TO 'maporal_app'@'localhost';
FLUSH PRIVILEGES;
```

El sistema no necesita `CREATE`, `DROP` ni `ALTER` en el día a día. Las migraciones se hacen con otra cuenta con más permisos.

3. Completar `config/database.php` con ese usuario
4. **Cambiar la contraseña de `admin`** y la clave de operaciones (17.1)
5. El archivo `.sql` **no se sirve por URL** (el servidor responde `403`), pero no lo dejes en el servidor si no hace falta
6. **Cargar los datos bancarios de la escuela** (Conceptos → *Datos bancarios de la escuela*) **antes de abrir el sistema a las familias**: nacen vacíos y mientras tanto los representantes ven el aviso *"Datos de pago no disponibles"*

## 17.4 Correo (Gmail)

- Requiere una **contraseña de aplicación** de Google (no la contraseña de la cuenta), con verificación en dos pasos activa
- Remitente = cuenta SMTP (`from_address` igual a `username`): si no, Gmail reescribe el remitente
- Límite de Gmail: unos 500 correos al día por cuenta. El tope del sistema (150) deja margen
- Comprobar la conexión **sin enviar nada**: abrir conexión SMTP, STARTTLS y autenticar, y cerrar

## 17.5 WhatsApp (Meta Cloud API)

Estado verificado en la cuenta actual:

| Elemento | Estado |
|---|---|
| Token de acceso | Usuario del sistema, **permanente** (no caduca) |
| Número | Conectado, calidad verde |
| Plantillas `recibo_pago_mes_pdf` e `inscripcion_atleta_pdf` | Aprobadas (UTILITY, idioma `es`) |
| Verificación del negocio | **No verificado** → límite de **250 conversaciones por día** |

- **El límite de 250/día** es suficiente para esta escuela, pero un envío masivo lo superaría. Verificar el negocio en Meta (Business Manager) lo amplía
- El estado `code_verification_status = EXPIRED` del número no impide enviar; es la verificación por código del registro original
- **No hay webhook**: el sistema sabe que Meta **aceptó** el mensaje, no que **se entregó** ni que se leyó. Un estado `sent` significa "aceptado por Meta"
- No se envía WhatsApp de rechazo de pagos: no existe plantilla aprobada. Para la constancia de pertenencia tampoco (texto propuesto en la sección 3.8)
- Si el token se revoca, los envíos fallan con error `190`; el pago o el alta **no se revierten** y el error queda en el registro de cada pago/atleta

## 17.6 Cómo probar antes de abrir el sistema

1. **Sin enviar nada**: comprobar autenticación SMTP, y consultar a Meta el estado del token, del número y de las plantillas (consultas de solo lectura)
2. **Con listas blancas** (opcional, recomendado la primera vez): añadir en `config/mail.php` y `config/whatsapp.php` una clave `'allowed_recipients' => [...]` (correos, y teléfonos en formato `58412XXXXXXX`). Mientras exista, el sistema **no envía a nadie más** y registra el intento bloqueado. Ver los `.example.php`
3. **Un único envío real** a tu propio correo y teléfono: dar de alta un atleta de prueba cuyo representante seas tú y comprobar que llegan la planilla por correo y por WhatsApp
4. **Quitar las listas blancas** (borrar la clave `allowed_recipients`) y borrar el atleta de prueba

> ⚠️ Con una lista blanca activa **tampoco funciona la recuperación de contraseña** para nadie que no esté en la lista. Quítala antes de abrir el sistema a los usuarios.

## 17.7 Qué hay que revisar al día siguiente

- **Log de errores del servidor**: buscar `[SendLimiter]`, `[config]`, `[PdfVerification]`, `[Mailer]`, `[WhatsAppClient]`
- **Envíos bloqueados por el límite**: `SELECT * FROM send_log WHERE status = 'blocked';`
- **Envíos fallidos**: pagos aprobados con `email_status` o `whatsapp_status` = `failed`, y atletas con `enrollment_*_status` = `failed`. La pantalla del pago/atleta permite **Reenviar**
- **Pagos aprobados sin recibo enviado** y **atletas sin planilla enviada** (los datos anteriores a la puesta en marcha): **no se envían solos**. Si alguien pulsa Reenviar, el mensaje **sale a la familia real**

## 17.8 Recuperar el acceso si se pierde la contraseña

- La recuperación por correo envía el enlace al **correo de la cuenta**. El correo de `admin` en el SQL de referencia es `admin@beisbolmaporal.com`, que **puede no ser un buzón real**: comprueba que cada administrador tenga un correo que sí lea
- Recuperación desde el servidor (necesita acceso a la base de datos o a la consola): generar una contraseña nueva y su hash, y actualizarla

```bash
php -r "echo password_hash('NuevaClave#2026x', PASSWORD_DEFAULT);"
```

```sql
UPDATE admins SET password = '<hash generado>' WHERE username = 'admin';
```

La contraseña debe tener al menos 8 caracteres con mayúscula, minúscula, número y un símbolo de `!@#$%^&*`.

## 17.9 Cómo revertir

| Qué | Cómo |
|---|---|
| Volver a **bloquear los envíos** a todos salvo unos destinos | Añadir `'allowed_recipients' => [...]` en `config/mail.php` y `config/whatsapp.php` |
| **Cortar los envíos de un canal** de golpe | Poner `daily_send_limit_email` o `daily_send_limit_whatsapp` en `1` en `config/app.php` (no `0`: `0` no desactiva, usa el valor por defecto) |
| Volver a **ver los errores** en pantalla (solo en desarrollo) | `'env' => 'local'` en `config/app.php` |
| Rotar la **clave de firma de los QR** | Mover la `hmac_key` actual a `hmac_previous_keys` y poner una nueva en `hmac_key`. Los documentos ya emitidos siguen validándose |

## 17.10 Lo que el sistema NO hace (límites conocidos)

- **No hay envíos automáticos programados**: ni recordatorios de cobros vencidos, ni resúmenes mensuales, ni generación automática de cobros. Cada mensaje sale solo cuando una persona aprueba un pago, da de alta un atleta o pulsa Reenviar. Los "reportes automáticos" no existen: todos los reportes se generan a mano
- **El entrenador puede ver el listado del personal** (correos, cédulas, teléfonos) por una decisión original del sistema
- **La recuperación de contraseña indica si un correo está registrado** o no (permite averiguar qué correos existen). Es el comportamiento original
- **Una constancia de pertenencia entregada no se puede revocar**: la verificación por QR avisa si el atleta ya no está activo, pero el papel sigue existiendo
- **No hay copia de seguridad automática**: ver "Backup de Base de Datos" en la sección 16. Programa una copia diaria de la base de datos y de `img/`

---

## 📄 Licencia

Este proyecto es propiedad de Henry Velazquez para la Escuela de Béisbol Menor Maporal (Pdvsa).

## 👨‍💻 Soporte

Para soporte técnico o consultas, contactar al administrador del sistema.

---

**Documentación Completa Consolidada**
**Sistema de Gestión - Escuela de Béisbol Menor Maporal**
**Versión:** 1.2.1
**Última actualización:** Junio 2026
