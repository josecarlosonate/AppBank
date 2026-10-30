# AppBank

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?logo=docker&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-MVC-informational)

Aplicación de banca digital simulada construida en **PHP nativo** con
arquitectura MVC.

AppBank permite a los clientes autenticarse, administrar múltiples
cuentas, inscribir cuentas de terceros, realizar transferencias y
consultar el historial y resumen mensual de sus movimientos, aplicando
reglas de negocio, controles de seguridad y una arquitectura orientada a
mantener separadas las responsabilidades de la aplicación.

> Proyecto de portafolio enfocado en fundamentos de arquitectura backend con PHP nativo, incluyendo routing, middleware e inyección de dependencias mediante componentes propios.

---

## Contenido

- [Sobre el proyecto](#sobre-el-proyecto)
- [Capturas de pantalla](#capturas-de-pantalla)
- [Funcionalidades](#funcionalidades)
- [Uso de la aplicación](#uso-de-la-aplicación)
- [Credenciales de prueba](#credenciales-de-prueba)
- [Stack](#stack)
- [Arquitectura](#arquitectura)
- [Decisiones técnicas](#decisiones-técnicas)
- [Modelo de datos](#modelo-de-datos)
- [Reglas de negocio](#reglas-de-negocio)
- [Cómo ejecutar el proyecto](#cómo-ejecutar-el-proyecto)
- [Autor](#autor)

---

## Sobre el proyecto

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
responsabilidades, seguridad y control de acceso a recursos 
utilizando **PHP nativo**.

---

## Capturas de pantalla

### Inicio de sesión

AppBank cuenta con autenticación de usuarios y protección de las rutas
privadas mediante sesiones y middleware.

<p align="center">
  <img
    src="docs/screenshots/01-login.png"
    alt="Inicio de sesión de AppBank"
    width="900"
  >
</p>

### Panel principal

El panel principal permite acceder a los módulos de cuentas, transferencias
y movimientos.

<p align="center">
  <img
    src="docs/screenshots/02-dashboard.png"
    alt="Panel principal de AppBank"
    width="900"
  >
</p>

### Gestión de cuentas

Los clientes pueden consultar sus cuentas, visualizar el saldo y estado,
activar o desactivar cuentas y administrar cuentas de terceros inscritas.

<p align="center">
  <img
    src="docs/screenshots/03-accounts.png"
    alt="Gestión de cuentas en AppBank"
    width="900"
  >
</p>

### Transferencias

El módulo de transferencias permite enviar dinero desde una cuenta propia
hacia otra cuenta propia o una cuenta previamente inscrita.

<p align="center">
  <img
    src="docs/screenshots/04-transfer.png"
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
    src="docs/screenshots/05-movements.png"
    alt="Historial y resumen mensual de movimientos en AppBank"
    width="900"
  >
</p>

---

## Funcionalidades

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

## Uso de la aplicación

Una vez iniciada la aplicación, inicia sesión con cualquiera de las [credenciales de prueba](#credenciales-de-prueba). El panel principal permite acceder a la gestión de cuentas, transferencias y movimientos.

### Caso de uso 1: Administrar cuentas propias

1. Inicia sesión con un cliente de prueba.
2. Accede al módulo de cuentas.
3. Consulta las cuentas asociadas al cliente, su tipo, saldo y estado.
4. Desde este módulo puedes crear una nueva cuenta o activar/desactivar una cuenta existente.

La aplicación valida que el cliente autenticado sea propietario de la cuenta antes de permitir cambios de estado.

### Caso de uso 2: Inscribir un destinatario y realizar una transferencia

1. Inicia sesión con un cliente que tenga una cuenta activa.
2. Accede a la gestión de cuentas inscritas y registra una cuenta perteneciente a otro cliente utilizando su número de cuenta y documento.
3. Accede al módulo de transferencias.
4. Selecciona una cuenta propia activa como origen y la cuenta inscrita como destino.
5. Indica un monto mayor a cero y confirma la operación.

Antes de ejecutar la transferencia, AppBank valida propiedad, estado de las cuentas, saldo disponible y que origen y destino sean diferentes. La operación se ejecuta dentro de una transacción de base de datos y genera los movimientos de débito y crédito correspondientes.

### Caso de uso 3: Consultar movimientos y resumen mensual

1. Accede al módulo de movimientos.
2. Selecciona una de las cuentas del cliente.
3. Consulta el historial agrupado por fecha, incluyendo tipo de movimiento, concepto, monto, hora y saldo resultante.
4. Revisa el resumen mensual de débitos y créditos representado mediante Chart.js.

El resumen mensual se obtiene de forma asíncrona mediante Fetch API desde un endpoint JSON protegido.

---

## Credenciales de prueba

Después de ejecutar `database/seed-demo.php`, la base de datos incluye cuatro clientes de prueba. Todos utilizan la contraseña `Test123`.

| Cliente | Documento | Cuentas | Activas | Inactivas |
| --- | --- | ---: | ---: | ---: |
| Jose Demo | `123456789` | 2 | 2 | 0 |
| Jhon Doe | `987654321` | 2 | 2 | 0 |
| Maria Lopez | `456789123` | 2 | 2 | 0 |
| Carlos Martinez | `741852963` | 2 | 1 | 1 |

En total, los datos de prueba incluyen **8 cuentas: 7 activas y 1 inactiva**.

> Estas credenciales están destinadas exclusivamente al entorno local de desarrollo.

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
- SweetAlert2

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
  seed-demo.php             → Generador de datos de demostración
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

## Reglas de negocio

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

## Cómo ejecutar el proyecto

> **Demo local, no banca real.** AppBank utiliza datos ficticios. Las credenciales públicas de ejemplo y el generador no deben usarse en producción.

Puedes ejecutar AppBank con **Docker (recomendado)** o con **PHP y MySQL locales**. En ambos casos se crea primero la estructura de las tablas con `database/schema.sql` y después se generan los datos con `database/seed-demo.php`.

### Opción 1: Docker (recomendada)

**Requisitos:** Git, Docker y Docker Compose.

1. Clona el repositorio y entra a la carpeta:

```bash
git clone https://github.com/josecarlosonate/AppBank.git
cd AppBank
```

> Para probar los cambios antes de integrarlos en `main`, ejecuta `git switch feature/portfolio-demo-seeder` después de clonar.

2. Crea tu configuración local:

```bash
cp .env.example .env
```

3. Edita **`.env`** y configura los valores para el contenedor MySQL (usa contraseñas propias; no subas el archivo a Git):

```dotenv
DB_HOST=db
DB_PORT=3306
DB_DATABASE=appbank_db
DB_USERNAME=appbank_user
DB_PASSWORD=elige_una_clave_local
MYSQL_ROOT_PASSWORD=elige_otra_clave_local
```

4. Construye la aplicación y levanta **solo MySQL** para inicializar el esquema:

```bash
docker compose build app
docker compose up -d db
```

Espera a que MySQL esté listo. En una base nueva, Docker importa automáticamente **solo** `database/schema.sql`. No genera clientes ni transferencias por sí solo.

5. Genera los datos de demostración. La primera orden simula y revierte los cambios; la segunda los guarda:

```bash
docker compose run --rm --no-deps app php database/seed-demo.php --dry-run
docker compose run --rm --no-deps app php database/seed-demo.php
```

Si aparece un error de conexión al iniciar MySQL, espera unos segundos y vuelve a ejecutar la orden.

6. Inicia la aplicación y Adminer:

```bash
docker compose up -d app adminer
```

Abre **http://localhost:8080** y entra con las [credenciales de prueba](#credenciales-de-prueba). Adminer está disponible en **http://localhost:8081**.

Para detener los contenedores sin eliminar la base:

```bash
docker compose down
```

**Importante:** no utilices `docker compose down -v` si quieres conservar los datos; `-v` elimina el volumen de MySQL.

### Opción 2: Instalación local (PHP + MySQL)

**Requisitos:** Git, PHP **8.3 o superior (64 bits)** con `pdo_mysql`, Composer y MySQL **8.4**. Los siguientes comandos de terminal se pueden ejecutar desde **Git Bash** en Windows. Para desarrollo local se utiliza el servidor integrado de PHP; no es necesario configurar Apache.

1. Clona el repositorio e instala las dependencias:

```bash
git clone https://github.com/josecarlosonate/AppBank.git
cd AppBank
composer install
cp .env.example .env
```

> Si estás evaluando la rama de la demo antes de su integración, ejecuta `git switch feature/portfolio-demo-seeder`.

2. En la **terminal**, abre MySQL con un usuario administrador:

```bash
mysql -u root -p
```

3. Dentro de MySQL (prompt **`mysql>`**), crea la base y un usuario dedicado. Cambia la contraseña del ejemplo:

```sql
CREATE DATABASE appbank_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

CREATE USER 'appbank_user'@'localhost'
  IDENTIFIED BY 'elige_una_clave_local';

GRANT SELECT, INSERT, UPDATE, DELETE
  ON appbank_db.* TO 'appbank_user'@'localhost';

EXIT;
```

Si el usuario ya existe, configura sus permisos en lugar de volver a crearlo. No utilices la cuenta `root` para la conexión habitual de la aplicación.

4. Edita **`.env`** para que coincida con tu base local:

```dotenv
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=appbank_db
DB_USERNAME=appbank_user
DB_PASSWORD=elige_una_clave_local
```

`MYSQL_ROOT_PASSWORD` solo es necesario para configurar el servicio MySQL de Docker.

5. De vuelta en **Git Bash** (no dentro del prompt de MySQL), importa únicamente el esquema. Se utiliza `root` porque el usuario de la aplicación no tiene permisos para crear tablas:

```bash
mysql -u root -p appbank_db < database/schema.sql
```

**Advertencia:** `schema.sql` contiene instrucciones `DROP TABLE`. Ejecútalo únicamente en una base nueva o desechable: podría borrar datos existentes.

6. Carga la demo. Puedes comprobar primero el resultado sin guardar cambios:

```bash
php database/seed-demo.php --dry-run
php database/seed-demo.php
```

El generador crea cuatro clientes, ocho cuentas, 84 transferencias y 168 movimientos distribuidos durante seis meses (el actual y los cinco anteriores). Conserva un total de **31.450.000 COP** entre las cuentas. No necesitas importar ningún otro archivo SQL.

7. Inicia el servidor de desarrollo:

```bash
php -S localhost:8000 -t public
```

Abre **http://localhost:8000** e inicia sesión con las [credenciales de prueba](#credenciales-de-prueba). Detén el servidor con `Ctrl+C`.

### Notas para desarrolladores

- La carpeta `database/` contiene solo `schema.sql` (estructura) y `seed-demo.php` (datos de ejemplo).
- `seed-demo.php` está pensado para una **base de demostración**, con la aplicación detenida durante la carga. Detecta la demo existente mediante identificadores internos y evita volver a generar sus transferencias.
- `--dry-run` ejecuta la simulación dentro de una transacción y revierte los cambios. MySQL puede consumir valores `AUTO_INCREMENT` aunque se haga rollback.
- Si ya hay datos ajenos a la demo, el generador puede rechazar la carga para evitar alterarlos. No borres una base con información que necesites conservar.
- La base de Docker se inicializa con el esquema solamente la primera vez que se crea su volumen. Reiniciar los contenedores no vuelve a crear tablas ni datos.
- `.env` contiene credenciales locales: no lo publiques. Nunca utilices las contraseñas de los usuarios ficticios en un entorno real.

---

## Autor

**Jose Carlos Oñate Rodríguez**

Proyecto de portafolio --- PHP nativo / MySQL / Arquitectura MVC
