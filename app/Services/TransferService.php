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
        int $sourceAccountId,
        int $destinationAccountId,
        string $amount,
        ?string $concept = null
    ): TransferResult {

        // validar que exista cuenta de origen y destino
        $sourceAccount = $this->account->findById($sourceAccountId);
        if (!$sourceAccount) {
            return TransferResult::SOURCE_ACCOUNT_NOT_FOUND;
        }

        $destinationAccount = $this->account->findById($destinationAccountId);
        if (!$destinationAccount) {
            return TransferResult::DESTINATION_ACCOUNT_NOT_FOUND;
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

            $debited = $this->account->debit($sourceAccountId, $amount);
            if (!$debited) {
                throw new \RuntimeException('No se pudo debitar la cuenta de origen.');
            }

            $credited = $this->account->credit($destinationAccountId, $amount);
            if (!$credited) {
                throw new \RuntimeException('No se se puedo acreditar la cuenta destino.');
            }

            //crear transferencia
            $transactionId = $this->transaction->create($sourceAccountId, $destinationAccountId, $amount, $concept);

            // obtener saldo de de origen y destino despues de una transferencia
            $sourceAccountAfterDebit = $this->account->findById($sourceAccountId);
            $destinationAccountAfterCredit = $this->account->findById($destinationAccountId);
            if (!$sourceAccountAfterDebit || !$destinationAccountAfterCredit) {
                throw new \RuntimeException(
                    'No se pudieron obtener los saldos actualizados.'
                );
            }

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
