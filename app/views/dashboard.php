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
</head>

<body class="bg-light">

    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <main class="container py-5">

        <div class="mb-5">
            <h1 class="fw-bold">Panel principal</h1>
            <p class="text-secondary fs-5">
                Administra tus cuentas y transferencias desde un solo lugar.
            </p>
        </div>

        <div class="row g-4">

            <!-- Mis cuentas -->
            <div class="col-md-4">
                <div class="card h-100 shadow border-0">
                    <div class="card-body p-4">
                        <i class="fa-solid fa-wallet fs-2 text-success mb-3"></i>

                        <h4 class="card-title fw-bold">
                            Mis cuentas
                        </h4>

                        <p class="card-text text-secondary">
                            Consulta tus cuentas, saldos y estado.
                        </p>

                        <a href="/accounts" class="btn btn-outline-success">
                            Ver cuentas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Transferencias -->
            <div class="col-md-4">
                <div class="card h-100 shadow border-0">
                    <div class="card-body p-4">
                        <i class="fa-solid fa-money-bill-transfer fs-2 text-success mb-3"></i>

                        <h4 class="card-title fw-bold">
                            Transferencias
                        </h4>

                        <p class="card-text text-secondary">
                            Envía dinero entre tus cuentas o a otras cuentas.
                        </p>

                        <a href="/transfers" class="btn btn-outline-success">
                            Transferir
                        </a>
                    </div>
                </div>
            </div>

            <!-- Movimientos -->
            <div class="col-md-4">
                <div class="card h-100 shadow border-0">
                    <div class="card-body p-4">
                        <i class="fa-solid fa-clock-rotate-left fs-2 text-success mb-3"></i>

                        <h4 class="card-title fw-bold">
                            Movimientos
                        </h4>

                        <p class="card-text text-secondary">
                            Consulta el historial de movimientos de tus cuentas.
                        </p>

                        <a href="/transactions" class="btn btn-outline-success">
                            Ver movimientos
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </main>

</body>

</html>