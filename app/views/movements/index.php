<?php

/** @var array $accounts */
/** @var array $movementsByDate */
/** @var int|null $accountId */
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AppBank</title>

    <!-- Bootstrap 5.3.8 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/2c9eef3e53.js" crossorigin="anonymous"></script>

    <!-- Mis estilos -->
    <link rel="stylesheet" href="/css/app.css">
</head>

<body class="bg-light">
    <?php require __DIR__ . '/../partials/navbar.php'; ?>
    <main class="container py-5">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/dashboard" class="text-success text-decoration-none">
                            <i class="fa-solid fa-house me-1"></i>
                            Panel principal
                        </a>
                    </li>

                    <li class="breadcrumb-item active" aria-current="page">
                        Mis Movimientos
                    </li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fw-bold">Mis Movimientos</h1>
                    <p class="text-secondary fs-5 mb-0">
                        Consulta los movimientos de tus cuentas.
                    </p>
                </div>
            </div>

        </div>

        <div>
            <?php if (isset($_SESSION['error'])): ?>

                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>
        </div>

        <div class="row">

            <form method="GET" action="/movements">
                <div class="row g-2">
                    <div class="col">
                        <select class="form-select" id="account_id" name="account_id" required>
                            <option value="" selected disabled>Seleccione una cuenta</option>
                            <?php foreach ($accounts as $account): ?>
                                <option
                                    value="<?= $account['id'] ?>"
                                    <?= ($accountId === (int) $account['id']) ? 'selected' : '' ?>>
                                    <?= $account['account_type'] === 'SAVINGS'
                                        ? 'Cuenta de ahorros: '
                                        : 'Cuenta corriente: '
                                    ?>

                                    <?= htmlspecialchars($account['account_number']) ?>
                                    — Saldo: $<?= number_format($account['balance'], 2, ',', '.') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success">
                            Consultar
                        </button>
                    </div>
                </div>
            </form>

            <div class="row g-4 mt-2">

                <div class="col-md-6">

                    <div class="row py-2 border-bottom border-dark fw-semibold sticky-top bg-light mb-3">
                        <div class="col-5">
                            Detalle
                        </div>
                        <div class="col-2">
                            Tipo
                        </div>
                        <div class="col-2 text-end">
                            Monto
                        </div>
                        <div class="col-3 text-end">
                            Saldo
                        </div>
                    </div>

                    <?php foreach ($movementsByDate as $date => $movements): ?>
                        <section>
                            <div class="fw-bold py-2 border-bottom border-3 border-dark">
                                <?= htmlspecialchars($date) ?>
                            </div>

                            <?php foreach ($movements as $movement): ?>
                                <?php
                                $typeClass = $movement['type'] === 'DEBIT' ? 'danger' : 'success';
                                $formattedBalanceAfter = number_format($movement['balance_after'], 2, ',', '.');
                                $amountSign = $movement['type'] === 'DEBIT' ? '-' : '+';
                                $formattedAmount = $amountSign .
                                    '$' .
                                    number_format($movement['amount'], 2, ',', '.');
                                ?>

                                <div class="row align-items-center py-3 border-bottom">
                                    <div class="col-5">
                                        <?= htmlspecialchars($movement['time']) ?> -
                                        <?= htmlspecialchars($movement['concept'] ?? 'Transferencia') ?>
                                    </div>
                                    <div class="col-2 text-<?= $typeClass  ?>">
                                        <?= $movement['type'] === 'DEBIT' ? 'Débito' : 'Crédito' ?>
                                    </div>
                                    <div class="col-2 text-end">
                                        <span class="text-<?= $typeClass ?>">
                                            <?= $formattedAmount ?>
                                        </span>
                                    </div>
                                    <div class="col-3 text-end">
                                        $ <?= $formattedBalanceAfter ?>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </section>
                    <?php endforeach; ?>

                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5>Resumen de movimientos</h5>
                            <?php if ($accountId !== null): ?>
                                <canvas id="movementsChart" data-account-id="<?= (int) $accountId ?>">

                                </canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/movements.js"></script>
</body>

</html>