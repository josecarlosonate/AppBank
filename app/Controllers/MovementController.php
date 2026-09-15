<?php

namespace App\Controllers;

use App\Models\Account;
use App\Models\Movement;

class MovementController
{
    public function __construct(
        private Movement $movement,
        private Account $account
    ) {}

    public function index(): void
    {
        $customerId = (int) $_SESSION['customer_id'];
        //muestra las cuentas para que el usuario seleccione una
        $accounts = $this->account->findByCustomerId($customerId);

        $accountId = isset($_GET['account_id']) ? (int) $_GET['account_id'] : null;

        $movements = [];
        $movementsByDate = [];

        if ($accountId !== null) {
            //verificar que la cuenta pertenece al usuario
            if ($this->account->findByIdAndCustomerId($accountId, $customerId) === false) {
                $_SESSION['error'] = 'La cuenta seleccionada no está disponible.';
                header('Location: /movements');
                exit;
            };

            //obtener los movimientos de la cuenta selecionada
            $movements = $this->movement->findByAccountId($accountId);
            $movementsByDate = $this->groupMovementsByDate($movements);
        }

        require __DIR__ . '/../views/movements/index.php';
    }

    private function groupMovementsByDate(array $movements): array
    {
        $movementsByDate = [];

        foreach ($movements as $movement) {

            $timestamp = strtotime($movement['created_at']);
            $date = date('Y-m-d', $timestamp);
            $movement['time'] = date('h:i A', $timestamp);
            unset($movement['created_at']);

            $movementsByDate[$date][] = $movement;
        }
        return $movementsByDate;
    }

    public function monthlySummary()
    {
        header('Content-Type: application/json');

        $customerId = (int) $_SESSION['customer_id'];

        $accountId = filter_var(
            $_GET['account_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($accountId === false) {
            http_response_code(422);
            echo json_encode(['error' => 'Cuenta inválida.']);
            exit;
        }

        if ($this->account->findByIdAndCustomerId($accountId, $customerId) === false) {
            http_response_code(403);
            echo json_encode(['error' => 'La cuenta seleccionada no está disponible.']);
            exit;
        }

        //Obtener el resumen mensual
        $monthlySummary = $this->movement->getMonthlySummaryByAccountId($accountId);

        echo json_encode($monthlySummary);
    }
}
