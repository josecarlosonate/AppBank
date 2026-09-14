<?php

namespace App\Models;

use PDO;

class Movement
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function create(int $transactionId, int $accountId, string $type, string $balanceAfter): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO movements (transaction_id, account_id, type, balance_after)
                    VALUES (:transaction_id, :account_id, :type, :balance_after)"
        );

        return $stmt->execute([
            'transaction_id' => $transactionId,
            'account_id' => $accountId,
            'type' => $type,
            'balance_after' => $balanceAfter
        ]);
    }
}
