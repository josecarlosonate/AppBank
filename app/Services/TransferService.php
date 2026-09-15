<?php

namespace App\Services;

use App\Enums\TransferResult;
use PDO;
use App\Models\Account;
use App\Models\Movement;
use App\Models\Transaction;

class TransferService
{
    public function __construct(
        private PDO $pdo,
        private Account $account,
        private Transaction $transaction,
        private Movement $movement
    ) {}

    public function transfer(
        int $customerId,
        int $sourceAccountId,
        int $destinationAccountId,
        string $amount,
        ?string $concept = null
    ): TransferResult {

        //validar que cuenta origen y destino no sean la misma cuenta
        if ($sourceAccountId === $destinationAccountId) {
            return TransferResult::SAME_ACCOUNT;
        }

        //validar: Usuario ¿estás autorizado para transferir desde esta cuenta?
        $sourceAccount = $this->account->findByIdAndCustomerId($sourceAccountId, $customerId);
        if (!$sourceAccount) {
            return TransferResult::SOURCE_ACCOUNT_NOT_FOUND;
        }

        //validar que cuenta destino exista
        $destinationAccount = $this->account->findById($destinationAccountId);
        if (!$destinationAccount) {
            return TransferResult::DESTINATION_ACCOUNT_NOT_FOUND;
        }

        /* El usuario puede transferir hacia otra cuenta propia 
         o una cuenta de terceros previamente registrada. */
        $destinationIsOwnAccount = $this->account->findByIdAndCustomerId($destinationAccountId, $customerId);
        $destinationIsRegistered = $this->account->isAlreadyRegistered($customerId, $destinationAccountId);

        if (!$destinationIsOwnAccount && !$destinationIsRegistered) {
            return TransferResult::DESTINATION_ACCOUNT_NOT_ALLOWED;
        }

        // validar monto - saldo insuficiente : saldo < monto a enviar
        if (bccomp($sourceAccount['balance'], $amount, 2) === -1) {
            return TransferResult::INSUFFICIENT_BALANCE;
        }

        //validar estado de cuenta de origen y destino
        if ((int) $destinationAccount['is_active'] === 0) {
            return TransferResult::DESTINATION_ACCOUNT_INACTIVE;
        }

        if ((int) $sourceAccount['is_active'] === 0) {
            return TransferResult::SOURCE_ACCOUNT_INACTIVE;
        }

        //iniciar transacción
        $this->pdo->beginTransaction();

        try {

            $sourceAccountAfterDebit = $this->account->debit($sourceAccountId, $amount);
            if (!$sourceAccountAfterDebit) {
                throw new \RuntimeException('No se pudo debitar la cuenta de origen.');
            }

            $destinationAccountAfterCredit = $this->account->credit($destinationAccountId, $amount);
            if (!$destinationAccountAfterCredit) {
                throw new \RuntimeException('No se pudo acreditar la cuenta destino.');
            }

            //crear transferencia
            $transactionId = $this->transaction->create($sourceAccountId, $destinationAccountId, $amount, $concept);

            // guardar movimientos generados por la transferencia
            $debitMovement = $this->movement->create($transactionId, $sourceAccountId, 'DEBIT', $sourceAccountAfterDebit['balance']);
            if (!$debitMovement) {
                throw new \RuntimeException('No se pudo registrar el movimiento débito');
            }

            $creditMovement = $this->movement->create($transactionId, $destinationAccountId, 'CREDIT', $destinationAccountAfterCredit['balance']);
            if (!$creditMovement) {
                throw new \RuntimeException('No se pudo registrar el movimiento crédito');
            }

            $this->pdo->commit();
            return TransferResult::SUCCESS;
        } catch (\Throwable $th) {
            $this->pdo->rollBack();
            throw $th;
        }
    }
}
