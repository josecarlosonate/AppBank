<?php

/** @var array $accounts */
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
                        Mis cuentas
                    </li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fw-bold">Mis cuentas</h1>
                    <p class="text-secondary fs-5 mb-0">
                        Consulta y administra tus cuentas.
                    </p>
                </div>

                <a href="/accounts/create" class="btn btn-success">
                    <i class="fa-solid fa-plus me-2"></i>
                    Nueva cuenta
                </a>
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
            <?php if (empty($accounts)): ?>

                <div class="card border-0 shadow">
                    <div class="card-body text-center py-5">

                        <i class="fa-solid fa-wallet fs-1 text-success mb-3"></i>

                        <h4 class="fw-bold">
                            No tienes cuentas registradas
                        </h4>

                        <p class="text-secondary mb-4">
                            Crea tu primera cuenta para comenzar a utilizar AppBank.
                        </p>

                        <a href="/accounts/create" class="btn btn-success">
                            <i class="fa-solid fa-plus me-2"></i>
                            Nueva cuenta
                        </a>

                    </div>
                </div>

            <?php else: ?>

                <div class="card border-1 shadow">
                    <div class="card-body p-4">

                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover">

                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Número de cuenta</th>
                                        <th>Saldo</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($accounts as $index => $account): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($account['account_number']) ?></td>
                                            <td>$ <?= number_format($account['balance'], 2, ',', '.') ?></td>
                                            <td>
                                                <?= $account['account_type'] === 'SAVINGS'
                                                    ? 'Cuenta de ahorros'
                                                    : 'Cuenta corriente'
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($account['is_active'] == 1): ?>
                                                    <span class="badge text-bg-success d-inline-block account-status-badge">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary d-inline-block account-status-badge">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="/accounts/status">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="account_id" value="<?= $account['id'] ?>">

                                                    <button
                                                        type="submit"
                                                        class="btn account-status-button <?= $account['is_active'] == 1
                                                                                                ? 'btn-outline-danger'
                                                                                                : 'btn-outline-success' ?>">
                                                        <?= $account['is_active'] == 1
                                                            ? 'Desactivar'
                                                            : 'Activar' ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>

            <?php endif; ?>
        </div>

        <!-- Cuentas inscritas -->
        <div class="mt-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold">Cuentas inscritas</h2>
                    <p class="text-secondary mb-0">
                        Administra las cuentas de terceros que tienes inscritas.
                    </p>
                </div>

                <a href="/accounts/register" class="btn btn-success">
                    <i class="fa-solid fa-plus me-2"></i>
                    Inscribir cuenta
                </a>
            </div>

            <div class="row">
                <?php if (empty($registeredAccounts)): ?>

                    <div class="card border-0 shadow">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-wallet fs-1 text-success mb-3"></i>
                            <h4 class="fw-bold">
                                No tienes cuentas inscritas
                            </h4>
                            <p class="text-secondary mb-4">
                                Registra tu primera cuenta para comenzar a transferir a terceros.
                            </p>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card border-1 shadow">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Número de cuenta</th>
                                            <th>Titular</th>
                                            <th>Tipo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registeredAccounts as $index => $registeredAccount): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($registeredAccount['account_number']) ?></td>
                                                <td><?= htmlspecialchars($registeredAccount['first_name'] . ' ' . $registeredAccount['last_name']) ?></td>
                                                <td><?= $registeredAccount['account_type'] == 'SAVINGS' ? 'Cuenta de ahorros' : 'Cuenta corriente' ?></td>
                                                <td>
                                                    <form method="POST" class="unregister-account-form" action="/accounts/unregister">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="account_id" value="<?= $registeredAccount['account_id'] ?>">
                                                        <button type="submit" class="btn account-status-button btn-outline-danger">
                                                            Desvincular
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div>

        </div>

    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/accounts.js"></script>
</body>

</html>