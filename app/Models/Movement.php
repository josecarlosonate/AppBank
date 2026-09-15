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

    public function findByAccountId(int $accountId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
            t.created_at, 
            t.concept, 
            t.amount,
            m.balance_after,
            m.type
         FROM movements m 
         INNER JOIN transactions t  ON m.transaction_id = t.id
         WHERE m.account_id = :account_id 
         ORDER BY t.created_at DESC"
        );

        $stmt->execute([
            'account_id' => $accountId
        ]);

        return $stmt->fetchAll();
    }

    public function getMonthlySummaryByAccountId(int $accountId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                DATE_FORMAT(t.created_at, '%Y-%m') AS month,
                SUM(
                    CASE
                        WHEN m.type = 'DEBIT' THEN t.amount
                        ELSE 0
                    END
                ) AS total_debit,

                SUM(
                    CASE
                        WHEN m.type = 'CREDIT' THEN t.amount
                        ELSE 0
                    END
                ) AS total_credit
                FROM movements m
                INNER JOIN transactions t ON m.transaction_id = t.id
                WHERE m.account_id = :account_id
                GROUP BY month
                ORDER BY month ASC
            "
        );

        $stmt->execute([
            'account_id' => $accountId
        ]);

        return $stmt->fetchAll();
    }
}
