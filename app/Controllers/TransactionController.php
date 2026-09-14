<?php

namespace App\Controllers;

use App\Enums\TransferResult;
use App\Services\TransferService;
use App\Models\Account;

class TransactionController
{

    public function __construct(
        private TransferService $transferService,
        private Account $account
    ) {}

    private function redirect(string $location): never
    {
        header("Location: {$location}");
        exit;
    }

    public function store(): void
    {
        $customerId = (int) $_SESSION['customer_id'];

        $destinationAccountId = filter_var(
            $_POST['destination_account_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($destinationAccountId === false) {
            $_SESSION['error'] = 'La cuenta de destino no es válida.';
            $this->redirect('/transfers');
        }

        $sourceAccountId = filter_var(
            $_POST['source_account_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($sourceAccountId === false) {
            $_SESSION['error'] = 'La cuenta de origen no es válida.';
            $this->redirect('/transfers');
        }

        $amount = trim($_POST['amount'] ?? '');

        //Validar formato monetario y monto mayor que cero
        if (
            !preg_match('/^\d{1,13}(\.\d{1,2})?$/', $amount)
            || bccomp($amount, '0', 2) <= 0
        ) {
            $_SESSION['error'] = 'El monto no es válido.';
            $this->redirect('/transfers');
        }

        $concept = trim($_POST['concept'] ?? '');
        if ($concept === '') {
            $concept = null;
        }

        if ($concept !== null && mb_strlen($concept) > 255) {
            $_SESSION['error'] = 'El concepto no puede superar los 255 caracteres.';
            $this->redirect('/transfers');
        }

        try {

            $result = $this->transferService->transfer(
                $customerId,
                $sourceAccountId,
                $destinationAccountId,
                $amount,
                $concept
            );

            $message  = match ($result) {
                TransferResult::SOURCE_ACCOUNT_NOT_FOUND => 'La cuenta de origen no existe.',
                TransferResult::DESTINATION_ACCOUNT_NOT_FOUND => 'La cuenta de destino no existe',
                TransferResult::SOURCE_ACCOUNT_INACTIVE => 'La cuenta de origen está inactiva.',
                TransferResult::DESTINATION_ACCOUNT_INACTIVE => 'La cuenta de destino está inactiva.',
                TransferResult::INSUFFICIENT_BALANCE => 'Saldo insuficiente.',
                TransferResult::SUCCESS => 'Transferencia realizada correctamente.',
                TransferResult::SAME_ACCOUNT => 'La cuenta de origen y destino no pueden ser la misma.',
                TransferResult::DESTINATION_ACCOUNT_NOT_ALLOWED => 'La cuenta de destino no está autorizada para transferencias.'
            };

            if ($result !== TransferResult::SUCCESS) {
                $_SESSION['error'] = $message;
                $this->redirect('/transfers');
            }

            $_SESSION['success'] = $message;
            $this->redirect('/transfers');
        } catch (\Throwable $e) {
            error_log($e->getMessage());

            $_SESSION['error'] = 'No fue posible realizar la transferencia.';
            $this->redirect('/transfers');
        }
    }

    public function index()
    {
        $customerId = (int) $_SESSION['customer_id'];

        // obtener cuentas propias
        $sourceAccounts = $this->account->findByCustomerId($customerId);

        // obtener cuentas registradas
        $registeredAccounts = $this->account->findRegisteredByCustomerId($customerId);

        require __DIR__ . '/../views/transfers/index.php';
    }
}
