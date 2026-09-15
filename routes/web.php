<?php

use App\Controllers\AccountsController;
use App\Controllers\AuthController;
use App\Controllers\MovementController;
use App\Controllers\TransactionController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

// Rutas públicas
$router->view('/', 'home');
$router->view('/login', 'auth/login');

// Rutas protegidas
$router->post(
    '/login',
    [AuthController::class, 'login'],
    [CsrfMiddleware::class]
);
$router->view(
    '/dashboard',
    'dashboard',
    [AuthMiddleware::class]
);
$router->get(
    '/accounts',
    [AccountsController::class, 'index'],
    [AuthMiddleware::class]
);
$router->post(
    '/accounts',
    [AccountsController::class, 'store'],
    [AuthMiddleware::class, CsrfMiddleware::class]
);
$router->post(
    '/logout',
    [AuthController::class, 'logout'],
    [AuthMiddleware::class, CsrfMiddleware::class]
);
$router->view(
    '/accounts/create',
    'accounts/create',
    [AuthMiddleware::class]
);
$router->post(
    '/accounts/status',
    [AccountsController::class, 'updateStatus'],
    [AuthMiddleware::class, CsrfMiddleware::class]
);

// Rutas protegidas - Cuentas inscritas
$router->view(
    '/accounts/register',
    'accounts/register',
    [AuthMiddleware::class]
);
$router->post(
    '/accounts/register',
    [AccountsController::class, 'register'],
    [AuthMiddleware::class, CsrfMiddleware::class]
);
$router->post(
    '/accounts/unregister',
    [AccountsController::class, 'unregister'],
    [AuthMiddleware::class, CsrfMiddleware::class]
);

// Rutas protegidas - Transferencias
$router->post(
    '/transfers',
    [TransactionController::class, 'store'],
    [AuthMiddleware::class, CsrfMiddleware::class]
);
$router->get(
    '/transfers',
    [TransactionController::class, 'index'],
    [AuthMiddleware::class]
);

// Rutas protegidas - historial de movimientos
$router->get(
    '/movements',
    [MovementController::class, 'index'],
    [AuthMiddleware::class]
);
$router->get(
    '/movements/summary',
    [MovementController::class, 'monthlySummary'],
    [AuthMiddleware::class]
);
