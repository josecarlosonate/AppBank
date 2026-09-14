<?php

/** @var array $sourceAccounts */
/** @var array $registeredAccounts */
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
                    <li class="breadcrumb-item">
                        <a href="/transfers" class="breadcrumb-item active">
                            Transferencias
                        </a>
                    </li>
                </ol>
            </nav>

            <div class="mb-4">
                <div>
                    <h1 class="fw-bold">Nueva transferencia</h1>
                    <p class="text-secondary fs-5 mb-0">
                        Envía dinero entre tus cuentas o a cuentas inscritas.
                    </p>
                </div>
            </div>
        </div>

        <div>
            <?php if (isset($_SESSION['success'])): ?>

                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>

                <?php unset($_SESSION['success']); ?>

            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>

                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">

                        <form id="transaction-form" method="POST" action="/transfers">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="source_account_id" class="form-label fw-semibold">
                                        Desde
                                    </label>
                                    <select class="form-select" id="source_account_id" name="source_account_id" required>
                                        <option value="" selected disabled>Seleccione una cuenta</option>
                                        <?php foreach ($sourceAccounts as $account): ?>
                                            <?php if ((int)$account['is_active'] === 1): ?>
                                                <option value="<?= $account['id'] ?>">
                                                    <?= $account['account_type'] === 'SAVINGS'
                                                        ? 'Cuenta de ahorros'
                                                        : 'Cuenta corriente'
                                                    ?>
                                                    ·
                                                    <?= htmlspecialchars($account['account_number']) ?>
                                                    ·
                                                    $<?= number_format($account['balance'], 2, ',', '.') ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="destination_account_id" class="form-label fw-semibold">
                                        Hacia
                                    </label>
                                    <select class="form-select" id="destination_account_id" name="destination_account_id" required>
                                        <option value="" selected disabled>Seleccione una cuenta</option>
                                        <optgroup label="Mis cuentas">
                                            <?php foreach ($sourceAccounts as $account): ?>
                                                <?php if ((int)$account['is_active'] === 1): ?>
                                                    <option value="<?= $account['id'] ?>">
                                                        <?= $account['account_type'] === 'SAVINGS'
                                                            ? 'Cuenta de ahorros'
                                                            : 'Cuenta corriente'
                                                        ?>
                                                        ·
                                                        <?= htmlspecialchars($account['account_number']) ?>
                                                        ·
                                                        $<?= number_format($account['balance'], 2, ',', '.') ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </optgroup>

                                        <?php if (!empty($registeredAccounts)): ?>
                                            <optgroup label="Cuentas inscritas">
                                                <?php foreach ($registeredAccounts as $account): ?>
                                                    <option value="<?= $account['account_id'] ?>">
                                                        <?= htmlspecialchars($account['account_number']) ?>
                                                        · <?= htmlspecialchars($account['first_name'] . ' ' . $account['last_name']) ?>
                                                        · <?= $account['account_type'] === 'SAVINGS' ? 'Ahorros' : 'Corriente' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-4">

                                <div class="col-md-6">
                                    <label for="amount" class="form-label fw-semibold">
                                        Monto
                                    </label>

                                    <input type="text" class="form-control" id="amount"
                                        name="amount" placeholder="0,00" inputmode="decimal" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="concept" class="form-label fw-semibold">
                                        Concepto (opcional)
                                    </label>

                                    <input type="text" class="form-control" id="concept"
                                        name="concept" maxlength="255" placeholder="Ej. Pago de alquiler">
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn btn-success">
                                    Confirmar transferencia
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </main>

</body>

</html>