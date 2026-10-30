<?php

declare(strict_types=1);

// Run from the CLI against a dedicated demo database, with the app stopped.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$arguments = array_slice($argv, 1);
if (array_diff($arguments, ['--dry-run', '--help']) !== []) {
    fwrite(STDERR, "Opción desconocida. Usa --help.\n");
    exit(1);
}
if (in_array('--help', $arguments, true)) {
    echo "Uso: php database/seed-demo.php [--dry-run]\n";
    echo "Requiere schema.sql y una base vacía o el seed original sin historial.\n";
    echo "Genera 84 transferencias en seis meses, incluido el actual.\n";
    echo "Ejecutar sin tráfico de la aplicación. --dry-run revierte los cambios.\n";
    exit(0);
}

$dryRun = in_array('--dry-run', $arguments, true);
$pdo = null;
$lockName = null;
$locked = false;
$exitCode = 0;

// Monetary arithmetic uses integer cents; no float or BCMath dependency.
function cents(string $value): int
{
    if (!preg_match('/^([0-9]+)(?:\.([0-9]{1,2}))?$/D', $value, $matches)) {
        throw new RuntimeException('Importe decimal inválido.');
    }
    return (int) $matches[1] * 100 + (int) str_pad($matches[2] ?? '', 2, '0');
}

function decimal(int $value): string
{
    return intdiv($value, 100) . '.' . str_pad((string) ($value % 100), 2, '0', STR_PAD_LEFT);
}

try {
    if (PHP_INT_SIZE < 8) {
        throw new RuntimeException('Se requiere PHP de 64 bits.');
    }
    $root = dirname(__DIR__);
    if (!is_file($root . '/vendor/autoload.php')) {
        throw new RuntimeException('Faltan dependencias: ejecuta composer install.');
    }
    require $root . '/vendor/autoload.php';

    // Docker injects DB_HOST=db. Process variables take precedence over .env.
    foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_ENV'] as $key) {
        $value = getenv($key);
        if ($value !== false) {
            $_ENV[$key] = $value;
        }
    }
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
    if (in_array(strtolower($_ENV['APP_ENV'] ?? ''), ['production', 'prod'], true)) {
        throw new RuntimeException('No se permite cargar datos demo en APP_ENV=production/prod.');
    }
    foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
        if (!isset($_ENV[$key])) {
            throw new RuntimeException("Falta la variable {$key}.");
        }
    }
    $pdo = require $root . '/config/database.php';
    $lockName = 'appbank-demo-' . substr(hash('sha256', $_ENV['DB_DATABASE']), 0, 40);
    $lock = $pdo->prepare('SELECT GET_LOCK(?, 0)');
    $lock->execute([$lockName]);
    $locked = (int) $lock->fetchColumn() === 1;
    if (!$locked) {
        throw new RuntimeException('Ya hay otra carga demo en ejecución.');
    }

    $pdo->beginTransaction();
    // Lock seed accounts before checking history. Keep the web app stopped too.
    $customers = $pdo->query('SELECT * FROM customers ORDER BY id FOR UPDATE')->fetchAll();
    $accounts = $pdo->query('SELECT * FROM accounts ORDER BY id FOR UPDATE')->fetchAll();
    $demoCount = (int) $pdo->query("SELECT COUNT(*) FROM transactions WHERE concept LIKE '[AppBank demo v1 #%'")->fetchColumn();
    if ($demoCount === 84) {
        // Normalize legacy demo descriptions without touching amounts or balances.
        $legacyConcepts = ['Arriendo', 'Servicios del hogar', 'Mercado', 'Ahorro mensual', 'Pago de proyecto', 'Transporte', 'Reembolso de compra'];
        $placeholders = implode(', ', array_fill(0, count($legacyConcepts), '?'));
        $normalize = $pdo->prepare(
            "UPDATE transactions SET concept = CONCAT(
                SUBSTRING(concept, 1, LOCATE('] ', concept) + 1),
                'Transferencia entre cuentas'
            )
            WHERE concept LIKE '[AppBank demo v1 #%] %'
              AND LOCATE('] ', concept) > 0
              AND SUBSTRING(concept, LOCATE('] ', concept) + 2) IN ($placeholders)"
        );
        $normalize->execute($legacyConcepts);
        $updated = $normalize->rowCount();
        if ($dryRun) {
            $pdo->rollBack();
            echo "Simulación: {$updated} conceptos se actualizarían. Cambios revertidos.\n";
        } else {
            $pdo->commit();
            echo "Demo existente: {$updated} conceptos actualizados; no se duplicaron movimientos.\n";
        }
    } else {
        foreach (['transactions', 'movements', 'registered_accounts'] as $table) {
            if ((int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn() !== 0) {
                throw new RuntimeException('La base contiene historial o inscripciones previas. Usa una base demo nueva; no se borrará ningún dato.');
            }
        }
        if ($customers === [] && $accounts === []) {
            // Initial demo customers and accounts, formerly in seed.sql.
            $initialSeedSql = <<<'SQL'
INSERT INTO customers (
    id,
    first_name,
    last_name,
    document_number,
    password
) VALUES
(
    1,
    'Jose',
    'Demo',
    '123456789',
    '$2y$12$hyQLRTKNcQLYHlWrVYIQleVvNTnkjZJ0IzEaeBGYPIr4bmYH3yCVC'
),
(
    2,
    'Jhon',
    'Doe',
    '987654321',
    '$2y$12$hyQLRTKNcQLYHlWrVYIQleVvNTnkjZJ0IzEaeBGYPIr4bmYH3yCVC'
),
(
    3,
    'Maria',
    'Lopez',
    '456789123',
    '$2y$12$hyQLRTKNcQLYHlWrVYIQleVvNTnkjZJ0IzEaeBGYPIr4bmYH3yCVC'
),
(
    4,
    'Carlos',
    'Martinez',
    '741852963',
    '$2y$12$hyQLRTKNcQLYHlWrVYIQleVvNTnkjZJ0IzEaeBGYPIr4bmYH3yCVC'
);




INSERT INTO accounts (
    id,
    customer_id,
    balance,
    account_type,
    account_number,
    is_active
) VALUES
(
    1,
    1,
    5000000.00,
    'SAVINGS',
    '23-45661-124',
    1
),
(
    2,
    1,
    4000000.00,
    'CHECKING',
    '31-72978-990',
    1
),
(
    3,
    2,
    3000000.00,
    'SAVINGS',
    '38-59868-742',
    1
),
(
    4,
    2,
    2500000.00,
    'CHECKING',
    '42-13579-864',
    1
),
(
    5,
    3,
    6500000.00,
    'SAVINGS',
    '51-24680-135',
    1
),
(
    6,
    3,
    1200000.00,
    'CHECKING',
    '64-97531-246',
    1
),
(
    7,
    4,
    8500000.00,
    'SAVINGS',
    '72-86420-357',
    1
),
(
    8,
    4,
    750000.00,
    'CHECKING',
    '83-75319-468',
    0
);
SQL;
            $pdo->exec($initialSeedSql);
            $customers = $pdo->query('SELECT * FROM customers ORDER BY id FOR UPDATE')->fetchAll();
            $accounts = $pdo->query('SELECT * FROM accounts ORDER BY id FOR UPDATE')->fetchAll();
        }

        $documents = [1 => '123456789', 2 => '987654321', 3 => '456789123', 4 => '741852963'];
        $expected = [
            1 => [1, '23-45661-124', '5000000.00', 'SAVINGS', 1],
            2 => [1, '31-72978-990', '4000000.00', 'CHECKING', 1],
            3 => [2, '38-59868-742', '3000000.00', 'SAVINGS', 1],
            4 => [2, '42-13579-864', '2500000.00', 'CHECKING', 1],
            5 => [3, '51-24680-135', '6500000.00', 'SAVINGS', 1],
            6 => [3, '64-97531-246', '1200000.00', 'CHECKING', 1],
            7 => [4, '72-86420-357', '8500000.00', 'SAVINGS', 1],
            8 => [4, '83-75319-468', '750000.00', 'CHECKING', 0],
        ];
        if (count($customers) !== 4 || count($accounts) !== 8) {
            throw new RuntimeException('La base no coincide con los cuatro clientes y ocho cuentas del seed original.');
        }
        foreach ($customers as $customer) {
            if (($documents[(int) $customer['id']] ?? null) !== $customer['document_number']) {
                throw new RuntimeException('Los clientes no coinciden con el seed original.');
            }
        }
        $balances = [];
        foreach ($accounts as $account) {
            $id = (int) $account['id'];
            $actual = [(int) $account['customer_id'], $account['account_number'], $account['balance'], $account['account_type'], (int) $account['is_active']];
            if (($expected[$id] ?? null) !== $actual) {
                throw new RuntimeException('Las cuentas o sus saldos cambiaron. Usa una base demo nueva.');
            }
            $balances[$id] = cents($account['balance']);
        }
        $initialTotal = array_sum($balances);
        $now = new DateTimeImmutable('now', new DateTimeZone('America/Bogota'));
        $firstMonth = $now->modify('first day of this month')->setTime(0, 0)->modify('-5 months');
        $openedAt = $firstMonth->modify('-1 day')->format('Y-m-d H:i:s');

        // Historical operations must not precede account creation or enrollment.
        $pdo->prepare('UPDATE customers SET created_at = LEAST(created_at, ?)')->execute([$openedAt]);
        $pdo->prepare('UPDATE accounts SET created_at = LEAST(created_at, ?)')->execute([$openedAt]);
        $register = $pdo->prepare('INSERT INTO registered_accounts (customer_id, account_id, created_at) VALUES (?, ?, ?)');
        $registered = [];
        $transaction = $pdo->prepare('INSERT INTO transactions (source_account_id, destination_account_id, amount, concept, created_at) VALUES (?, ?, ?, ?, ?)');
        $movement = $pdo->prepare('INSERT INTO movements (transaction_id, account_id, type, balance_after) VALUES (?, ?, ?, ?)');
        $concepts = ['Transferencia personal', 'Traspaso de fondos', 'Transferencia entre cuentas'];
        $sequence = 0;

        for ($month = 0; $month < 6; $month++) {
            $start = $firstMonth->modify("+{$month} months");
            $end = $month === 5 ? $now : $start->modify('+1 month')->modify('-1 second');
            $span = $end->getTimestamp() - $start->getTimestamp();
            // Two rounds across all seven active accounts; includes own transfers.
            for ($slot = 0; $slot < 14; $slot++) {
                $source = ($slot % 7) + 1;
                $destination = (($source - 1 + ($slot < 7 ? 1 : 3)) % 7) + 1;
                $owner = $expected[$source][0];
                $key = $owner . ':' . $destination;
                if ($owner !== $expected[$destination][0] && !isset($registered[$key])) {
                    $register->execute([$owner, $destination, $openedAt]);
                    $registered[$key] = true;
                }
                $amount = (50000 + (($month * 17 + $slot * 11) % 31) * 5000) * 100;
                if ($balances[$source] < $amount) {
                    throw new RuntimeException('Saldo insuficiente al generar la demo.');
                }
                $balances[$source] -= $amount;
                $balances[$destination] += $amount;
                $date = $start->setTimestamp($start->getTimestamp() + intdiv($span * ($slot + 1), 15));
                $sequence++;
                $concept = sprintf('[AppBank demo v1 #%03d] %s', $sequence, $concepts[($month + $slot) % count($concepts)]);
                $transaction->execute([$source, $destination, decimal($amount), $concept, $date->format('Y-m-d H:i:s')]);
                $id = (int) $pdo->lastInsertId();
                $movement->execute([$id, $source, 'DEBIT', decimal($balances[$source])]);
                $movement->execute([$id, $destination, 'CREDIT', decimal($balances[$destination])]);
            }
        }
        if (array_sum($balances) !== $initialTotal) {
            throw new RuntimeException('La suma de saldos no se conserva.');
        }
        $update = $pdo->prepare('UPDATE accounts SET balance = ? WHERE id = ?');
        foreach ($balances as $id => $balance) {
            $update->execute([decimal($balance), $id]);
        }
        if ($dryRun) {
            $pdo->rollBack();
        } else {
            $pdo->commit();
        }
        echo $dryRun ? "Simulación correcta; cambios revertidos.\n" : "Demo guardada correctamente.\n";
        echo '4 clientes, 8 cuentas, ' . count($registered) . " inscripciones, 84 transferencias y 168 movimientos.\n";
        echo 'Período: ' . $firstMonth->format('Y-m-d') . ' a ' . $now->format('Y-m-d') . " (America/Bogota).\n";
        echo 'Saldo total conservado: ' . decimal($initialTotal) . " COP.\n";
        echo "La cuenta inactiva conserva su saldo y no recibe movimientos.\n";
    }
} catch (Throwable $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'No se cargó la demo: ' . $error->getMessage() . "\n");
    $exitCode = 1;
} finally {
    if ($locked && $pdo instanceof PDO) {
        $release = $pdo->prepare('SELECT RELEASE_LOCK(?)');
        $release->execute([$lockName]);
    }
}
exit($exitCode);
