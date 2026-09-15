# AppBank

Aplicación de banca digital simulada construida en **PHP nativo** con
arquitectura MVC.

AppBank permite a los clientes autenticarse, administrar múltiples
cuentas, inscribir cuentas de terceros, realizar transferencias y
consultar el historial y resumen mensual de sus movimientos, aplicando
reglas de negocio, controles de seguridad y una arquitectura orientada a
mantener separadas las responsabilidades de la aplicación.

---

## Contenido

- [¿Qué problema resuelve?](#qué-problema-resuelve)
- [Capturas de pantalla](#capturas-de-pantalla)
- [Funcionalidades actuales](#funcionalidades-actuales)
- [Stack](#stack)
- [Arquitectura](#arquitectura)
- [Decisiones técnicas](#decisiones-técnicas)
- [Modelo de datos](#modelo-de-datos)
- [Reglas importantes](#reglas-importantes)
- [Flujo de una transferencia](#flujo-de-una-transferencia)
- [Resumen mensual de movimientos](#resumen-mensual-de-movimientos)
- [Cómo ejecutar el proyecto](#cómo-ejecutar-el-proyecto)
- [Autor](#autor)

---

## ¿Qué problema resuelve?

Modela las operaciones básicas de un banco digital:

- Centraliza la gestión básica de productos bancarios de un cliente en
  una sola aplicación.
- Un cliente puede tener varias cuentas.
- Puede activar o desactivar sus cuentas.
- Puede inscribir cuentas de otros clientes como destinatarios.
- Puede realizar transferencias entre cuentas autorizadas.
- Puede consultar los movimientos de sus cuentas.
- Puede visualizar un resumen mensual de débitos y créditos.
- El sistema valida propiedad, estado y reglas de negocio antes de
  operar.

El objetivo del proyecto es demostrar diseño limpio, separación de
responsabilidades, seguridad y control de acceso sobre recursos en una
aplicación PHP real.

---

## Capturas de pantalla

### Inicio de sesión

AppBank cuenta con autenticación de usuarios y protección de las rutas
privadas mediante sesiones y middleware.

<p align="center">
  <img
    src="docs/screenshots/login.png"
    alt="Inicio de sesión de AppBank"
    width="900"
  >
</p>

### Panel principal

El panel principal permite acceder a los módulos de cuentas, transferencias
y movimientos.

<p align="center">
  <img
    src="docs/screenshots/dashboard.png"
    alt="Panel principal de AppBank"
    width="900"
  >
</p>

### Gestión de cuentas

Los clientes pueden consultar sus cuentas, visualizar el saldo y estado,
activar o desactivar cuentas y administrar cuentas de terceros inscritas.

<p align="center">
  <img
    src="docs/screenshots/accounts.png"
    alt="Gestión de cuentas en AppBank"
    width="900"
  >
</p>

### Transferencias

El módulo de transferencias permite enviar dinero desde una cuenta propia
hacia otra cuenta propia o una cuenta previamente inscrita.

<p align="center">
  <img
    src="docs/screenshots/transfer.png"
    alt="Transferencias en AppBank"
    width="900"
  >
</p>

### Historial y resumen de movimientos

Los movimientos se muestran agrupados por fecha e incluyen tipo, monto y
saldo resultante. El resumen mensual compara débitos y créditos mediante
una gráfica generada con Chart.js.

<p align="center">
  <img
    src="docs/screenshots/movements.png"
    alt="Historial y resumen mensual de movimientos en AppBank"
    width="900"
  >
</p>

---

## Funcionalidades actuales

### Autenticación

- Login con documento y contraseña.
- Passwords hasheados y verificados con `password_verify()`.
- Regeneración de sesión al autenticarse.
- Logout seguro.
- Protección de rutas con middleware.

### Cuentas propias

- Listado de cuentas del cliente.
- Creación de cuentas de ahorro y corriente.
- Generación de número de cuenta único.
- Activación y desactivación con validación de ownership.

### Cuentas inscritas (destinatarios)

- Inscripción de cuentas de terceros.
- Validación por número de cuenta y documento del titular.
- Prevención de:
  - Inscribir una cuenta propia.
  - Inscribir la misma cuenta dos veces.
  - Inscribir una cuenta inexistente.
- Eliminación de inscripción.

### Transferencias

- Transferencias entre cuentas.
- Validación de cuenta origen y cuenta destino.
- Validación del estado de las cuentas.
- Validación de saldo disponible.
- Prevención de transferencias hacia la misma cuenta de origen.
- Registro de la transacción y sus movimientos asociados.
- Operaciones atómicas mediante transacciones de base de datos.
- Registro del saldo resultante después de cada movimiento.

### Movimientos

- Consulta del historial de movimientos por cuenta.
- Movimientos clasificados como débito o crédito.
- Visualización del concepto, monto, hora y saldo posterior.
- Agrupación del historial por fecha.
- Formato monetario para visualización en pesos.
- Resumen mensual de débitos y créditos.
- Consulta asíncrona del resumen mensual mediante Fetch API.
- Endpoint JSON protegido para obtener el resumen de una cuenta.
- Gráfica mensual de movimientos con Chart.js.

### Seguridad

- Protección CSRF en formularios `POST`.
- Middleware dedicado para autenticación y CSRF.
- Validación de ownership sobre recursos del cliente.
- Prepared statements mediante PDO.
- Validaciones de entrada tanto en cliente como en servidor.
- Constraints de integridad en la base de datos.

---

## Stack

### Backend

- **PHP 8.x** (Nativo)
- **PDO**
- **Composer**
- **Dotenv**

### Frontend

- **HTML5**
- **CSS3**
- **Bootstrap 5**
- **JavaScript (ES6+)**
- **jQuery**
- **Fetch API (AJAX)**
- **Chart.js**

### Base de datos

- **MySQL 8**

### Infraestructura y herramientas

- **Docker**
- **Docker Compose**
- **Apache**
- **Adminer**

---

## Arquitectura

```text
public/
  index.php                → Front Controller
  js/                      → JavaScript del cliente

app/
  Core/                     → Router, Container (DI) y CSRF
  Middleware/               → AuthMiddleware + CsrfMiddleware
  Controllers/              → Orquestación de casos de uso
  Services/                 → Lógica de negocio
  Models/                   → Acceso a datos
  Enums/                    → Resultados de dominio
  Views/                    → Capa de presentación
  helpers.php               → Funciones auxiliares

routes/
  web.php                   → Definición de rutas

config/
  database.php              → Configuración de base de datos

database/
  schema.sql                → Estructura de la base de datos
  seed.sql                  → Datos de prueba
```

La aplicación utiliza un **Front Controller** como único punto de
entrada. El Router resuelve las rutas y ejecuta los middleware
correspondientes antes de delegar la petición al controlador.

El contenedor de dependencias utiliza **Reflection** para resolver
automáticamente dependencias estructurales entre controllers, services,
models y PDO.

---

## Decisiones técnicas

- **Router propio** con soporte para vistas, controllers y middleware.
- **Contenedor de dependencias** con resolución mediante Reflection.
- **Middleware de autenticación** para rutas protegidas.
- **Middleware CSRF** para proteger operaciones `POST`.
- **Constructor Dependency Injection** para controllers y services.
- **Service Layer** para encapsular la lógica de las transferencias.
- **Ownership checks** en operaciones sobre cuentas.
- **PDO Prepared Statements** para acceso seguro a la base de datos.
- **Transacciones de base de datos** para garantizar atomicidad
  durante transferencias.
- **DECIMAL** para almacenar valores monetarios, evitando cálculos
  financieros con `float`.
- **Fetch API** para consumir de forma asíncrona el resumen mensual de
  movimientos.
- **Chart.js** para representar visualmente débitos y créditos.
- **Constraints** en base de datos (`CHECK`, `UNIQUE`, `FOREIGN KEY`)
  para reforzar reglas de integridad.

---

## Modelo de datos

Tablas

| Tabla               | Descripción                                                                  |
| ------------------- | ---------------------------------------------------------------------------- |
| customers           | Clientes del banco                                                           |
| accounts            | Cuentas bancarias                                                            |
| registered_accounts | Cuentas de terceros inscritas por un cliente                                 |
| transactions        | Operaciones de transferencia entre una cuenta de origen y una cuenta destino |
| movements           | Efecto débito o crédito producido por una transacción sobre una cuenta       |

Una transferencia genera una entrada en `transactions` y dos movimientos
asociados:

- **DEBIT** para la cuenta origen.
- **CREDIT** para la cuenta destino.

Cada movimiento almacena el saldo de la cuenta después de aplicar la
operación (`balance_after`).

---

## Reglas importantes

- Un cliente puede tener múltiples cuentas.
- Una cuenta pertenece a un único cliente.
- Una cuenta de terceros debe existir antes de poder ser inscrita.
- Un cliente no puede inscribir una cuenta propia como cuenta de
  terceros.
- Una misma cuenta de terceros no puede inscribirse más de una vez.
- El saldo de una cuenta no puede ser negativo.
- Solo el propietario de una cuenta puede modificar su estado.
- Una transferencia no puede tener la misma cuenta como origen y
  destino.
- El monto de una transferencia debe ser mayor a 0.
- Las cuentas involucradas en una transferencia deben estar activas.
- Una transferencia debe ejecutarse completamente o revertirse.
- Cada transacción genera los movimientos correspondientes de débito y
  crédito.

---

## Flujo de una transferencia

```text
Usuario
  ↓
Router
  ↓
AuthMiddleware + CsrfMiddleware
  ↓
TransactionController
  ↓
TransferService
  ↓
Account + Transaction + Movement
  ↓
PDO
  ↓
MySQL
```

`TransferService` coordina la operación dentro de una transacción de
base de datos:

```text
BEGIN
  ↓
Validar cuentas y reglas de negocio
  ↓
Debitar cuenta origen
  ↓
Acreditar cuenta destino
  ↓
Registrar transaction
  ↓
Registrar movimiento DEBIT
  ↓
Registrar movimiento CREDIT
  ↓
COMMIT
```

Si ocurre una excepción durante el proceso, la operación se revierte
mediante `ROLLBACK`.

---

## Resumen mensual de movimientos

El módulo de movimientos consulta el historial de una cuenta y genera un
resumen mensual de débitos y créditos.

El navegador solicita los datos de forma asíncrona:

```text
movements.js
  ↓
Fetch API
  ↓
GET /movements/summary?account_id={id}
  ↓
AuthMiddleware
  ↓
MovementController
  ↓
Movement
  ↓
MySQL
  ↓
JSON
  ↓
Chart.js
```

La agregación mensual se realiza en la base de datos y Chart.js utiliza
el resultado para construir una gráfica comparativa de débitos y
créditos.

---

## Cómo ejecutar el proyecto

AppBank puede ejecutarse de dos formas:

1. **Con Docker** — opción recomendada.
2. **Instalación manual** — utilizando PHP, Composer y MySQL instalados localmente.

---

### Opción 1: Docker

#### Requisitos

- Docker
- Docker Compose
- Git

No es necesario instalar PHP, Composer, Apache ni MySQL localmente.

#### Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/josecarlosonate/AppBank.git
cd AppBank
```

2. Crea el archivo de configuración .env:

```bash
cp .env.example .env
```

3. Configura las variables de la base de datos en .env.

4. Construye e inicia los contenedores:

```bash
docker compose up -d --build
```

5. Abre la aplicación en:

```text
   http://localhost:8080
```

Adminer está disponible en:

```text
   http://localhost:8081
```

La base de datos se inicializa automáticamente durante la primera ejecución utilizando:

```text
- database/schema.sql
- database/seed.sql
```

Credenciales de prueba

Puedes iniciar sesión con:

```text
- Documento: 123456789
- Contraseña: Test123
```

Detener el proyecto con

```bash
docker compose down
```

### Opción 2: Instalación manual

#### Requisitos

- Git
- PHP 8.3
- Composer
- MySQL 8.4
- Apache con mod_rewrite habilitado

PHP debe tener habilitada la extensión:

```text
   pdo_mysql
```

#### Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/josecarlosonate/AppBank.git
cd AppBank
```

2. Instala las dependencias de PHP:

```bash
composer install
```

3. Crea el archivo de configuración .env:

```bash
cp .env.example .env
```

4. Configura las variables de tu base de datos en .env.

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=appbank
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
MYSQL_ROOT_PASSWORD=
```

5. Crea la base de datos:

```sql
CREATE DATABASE appbank
CHARACTER SET utf8mb4
COLLATE utf8mb4_0900_ai_ci;
```

6. Importa el esquema:

```bash
mysql -u tu_usuario -p appbank < database/schema.sql
```

7. Importa los datos de prueba:

```bash
mysql -u tu_usuario -p appbank < database/seed.sql
```

8. Inicia el servidor de desarrollo de PHP.

```bash
php -S localhost:8000 -t public
```

o tambien puedes configurar Apache para utilizar como DocumentRoot y apuntar
el virtual host al directorio **public/**

9. Abre la aplicación en:

```text
   http://localhost:8000
```

10. Credenciales de prueba:

```text
Documento: 123456789
Contraseña: Test123
```

---

## Autor

**Jose Carlos Oñate Rodríguez**

Proyecto de portafolio --- PHP nativo / MySQL / Arquitectura MVC
