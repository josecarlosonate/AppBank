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

La base de datos incluye cuatro clientes de prueba. Todos utilizan la contraseña `Test123`.

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
  seed.sql                  → Datos iniciales de prueba
  seed-demo.php             → Historial de demostración
  DEMO.md                   → Guía del seeder
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

Para iniciar sesión, utiliza cualquiera de las [credenciales de prueba](#credenciales-de-prueba).

Detener el proyecto con

```bash
docker compose down
```

### Opción 2: Instalación manual

#### Requisitos

- Git
- PHP 8.3 con extensión `pdo_mysql`
- Composer
- MySQL 8.4
- Apache con `mod_rewrite` habilitado (opcional si utilizas el servidor de PHP)

#### Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/josecarlosonate/AppBank.git
cd AppBank
```

Para utilizar el seeder de portafolio mientras esté en esta rama:

```bash
git switch feature/portfolio-demo-seeder
```

2. Instala las dependencias:

```bash
composer install
```

3. Crea el archivo de entorno:

```bash
cp .env.example .env
```

4. Entra a MySQL con el usuario administrador. La contraseña de `root` se configura al instalar MySQL; **no es la contraseña del usuario de AppBank**:

```bash
mysql -u root -p
```

5. Crea la base de datos y un usuario dedicado, dentro de la consola de MySQL. Sustituye la contraseña de ejemplo por una contraseña propia y guárdala para el siguiente paso:

```sql
CREATE DATABASE IF NOT EXISTS appbank_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

CREATE USER IF NOT EXISTS 'appbank_user'@'localhost'
  IDENTIFIED BY 'CAMBIA_ESTA_CONTRASENA';

GRANT SELECT, INSERT, UPDATE, DELETE
  ON appbank_db.*
  TO 'appbank_user'@'localhost';
```

Si el usuario ya existe, `CREATE USER IF NOT EXISTS` no falla ni cambia su contraseña. Comprueba que conoces su contraseña; si necesitas restablecerla, ejecuta como administrador `ALTER USER 'appbank_user'@'localhost' IDENTIFIED BY 'TU_NUEVA_CONTRASENA';` y actualiza también `.env`. No ejecutes `ALTER USER` automáticamente en cada instalación. El usuario `root` se utiliza para tareas administrativas y para importar el esquema; `appbank_user` se utiliza para ejecutar la aplicación y cargar los datos.

Sal de MySQL con `exit`.

6. Configura las variables en `.env` con la contraseña elegida:

```dotenv
APP_ENV=local
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=appbank_db
DB_USERNAME=appbank_user
DB_PASSWORD="CAMBIA_ESTA_CONTRASENA"
```

No publiques el archivo `.env` ni compartas contraseñas reales. `MYSQL_ROOT_PASSWORD`, si aparece en el ejemplo de entorno, corresponde a la configuración de Docker y no sustituye a `DB_PASSWORD` en la instalación manual.

7. Importa las tablas en la base recién creada. Ejecuta este comando desde la raíz del proyecto, **fuera de la consola de MySQL**:

```bash
mysql -u root -p appbank_db < database/schema.sql
```

8. Valida la carga de demostración sin guardar cambios:

```bash
php database/seed-demo.php --dry-run
```

9. Si la validación termina correctamente, guarda los datos de demostración:

```bash
php database/seed-demo.php
```

El script utiliza `database/seed.sql` automáticamente cuando no existen clientes ni cuentas; **no es necesario importar `seed.sql` por separado**. Genera 4 clientes, 8 cuentas, 84 transferencias y 168 movimientos en seis meses, incluido el actual. Solo debe utilizarse en una base de demostración nueva o con los datos originales intactos y sin historial. Detén la aplicación mientras lo ejecutas. No está permitido en `APP_ENV=production`.

10. Inicia el servidor de desarrollo:

```bash
php -S localhost:8000 -t public
```

Alternativamente, configura Apache con `public/` como `DocumentRoot`.

11. Abre `http://localhost:8000` e inicia sesión con las [credenciales de prueba](#credenciales-de-prueba).

Para detalles y restricciones del seeder, consulta [database/DEMO.md](database/DEMO.md).


---

## Autor

**Jose Carlos Oñate Rodríguez**

Proyecto de portafolio --- PHP nativo / MySQL / Arquitectura MVC
