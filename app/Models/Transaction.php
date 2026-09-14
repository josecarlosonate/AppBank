<?php

namespace App\Models;

use PDO;

class Transaction
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function create(int $sourceAccountId, int $destinationAccountId, string $amount, ?string $concept): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO transactions (source_account_id, destination_account_id, amount, concept)
                    VALUES (:source_account_id, :destination_account_id, :amount, :concept)"
        );

        $stmt->execute([
            'source_account_id' => $sourceAccountId,
            'destination_account_id' => $destinationAccountId,
            'amount' => $amount,
            'concept' => $concept
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
