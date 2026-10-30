# Datos para la demo de portafolio

`seed-demo.php` genera 84 transferencias y 168 movimientos durante seis meses
calendario (el actual y los cinco anteriores), con fechas en `America/Bogota`.
Mantiene los cuatro clientes y las ocho cuentas del seed original; solo operan
las siete cuentas activas. Los importes se calculan en centavos enteros.

## Requisitos

- PHP 8.3 de 64 bits, PDO MySQL y dependencias de Composer.
- Tablas de `schema.sql`, en una base dedicada a la demo.
- Base vacía o únicamente los datos originales de `seed.sql`, sin cambios
  de saldo, cuentas adicionales, inscripciones ni historial previo.
- Aplicación sin tráfico durante la carga.

No ejecutes `schema.sql` sobre una base con datos: contiene `DROP TABLE`.
Si ya tienes historial, usa otra base para la demo; el script no lo borra.

## Docker (desde la carpeta del proyecto)

Primero obtén esta rama y construye la imagen: el Dockerfile copia el código
durante la construcción, por lo que reiniciar una imagen vieja no añade el script.

```bash
git switch feature/portfolio-demo-seeder
docker compose build app
docker compose stop app
docker compose up -d db
```

Espera a que MySQL esté listo. Una base nueva cargará automáticamente el esquema
y el seed configurados en `compose.yaml`. Simula la carga:

```bash
docker compose run --rm --no-deps app php database/seed-demo.php --dry-run
```

Si termina correctamente, guarda la demo y levanta la aplicación:

```bash
docker compose run --rm --no-deps app php database/seed-demo.php
docker compose up -d app adminer
```

## PHP y MySQL locales

Con las tablas ya creadas y `.env` apuntando a la base demo:

```bash
composer install
php database/seed-demo.php --dry-run
php database/seed-demo.php
```

Si las tablas están vacías, el script importa `seed.sql` dentro de la misma
transacción. No debes importarlo dos veces. Las variables del proceso tienen
prioridad sobre `.env`, incluido `DB_HOST=db` dentro de Docker.

## Resultado y garantías

- Crea las inscripciones de terceros necesarias antes de las transferencias.
- Genera operaciones entre cuentas propias y cuentas de otros clientes.
- Cada transferencia tiene un débito, un crédito y sus saldos posteriores.
- Conserva el total inicial de **31.450.000 COP**; no hay saldos negativos.
- La cuenta inactiva conserva sus 750.000 COP y no registra movimientos.
- Adelanta las fechas de creación del seed, cuando es necesario, para que las
  cuentas y los clientes existan antes de las operaciones históricas.
- Todo se guarda en una transacción. Cualquier error revierte la carga.
- `--dry-run` ejecuta la carga y la revierte; MySQL puede consumir valores de
  AUTO_INCREMENT aunque haya rollback. Los saltos en IDs son normales.
- Una segunda ejecución detecta los 84 conceptos con el prefijo reservado
  `[AppBank demo v1 #` y termina sin añadir ni actualizar datos. No edites ese
  prefijo manualmente. La demo no se regenera cada mes al repetir el comando.
- Rechaza `APP_ENV=production` o `prod`. Esto no sustituye comprobar que `.env`
  apunta a la base correcta: AppBank no exige actualmente esa variable.
- El bloqueo MySQL evita dos seeders simultáneos, pero no sustituye detener
  el tráfico de la aplicación.

Login: documento `123456789`, contraseña `Test123`. Los demás clientes conservan
las credenciales descritas en el README. Son credenciales de demostración.

Para verificar, abre movimientos de cualquiera de las siete cuentas activas:
habrá débitos y créditos en cada uno de los seis meses. El saldo inicial es
capital de apertura del seed, no una transferencia ni un ingreso generado.

## Comprobaciones SQL

```sql
SELECT COUNT(*) AS transfers FROM transactions; -- 84 en la demo recién cargada
SELECT COUNT(*) AS movements FROM movements;   -- 168
SELECT SUM(balance) AS total FROM accounts;    -- 31450000.00

SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS transfers
FROM transactions GROUP BY month ORDER BY month; -- 14 por mes

SELECT t.id
FROM transactions t LEFT JOIN movements m ON m.transaction_id = t.id
GROUP BY t.id
HAVING COUNT(m.id) <> 2
   OR SUM(m.type = 'DEBIT' AND m.account_id = t.source_account_id) <> 1
   OR SUM(m.type = 'CREDIT' AND m.account_id = t.destination_account_id) <> 1;
-- Sin filas: cada transferencia tiene sus dos movimientos correctos.
```
