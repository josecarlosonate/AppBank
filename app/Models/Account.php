<?php

namespace App\Models;

use PDO;

class Account
{
    public function __construct(
        private PDO $pdo
    ) {}

    /*=============================================
    CUENTAS PROPIAS
    =============================================*/
    public function findByCustomerId(int $customerId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, balance, account_number, account_type, is_active FROM accounts
         WHERE customer_id = :customer_id"
        );

        $stmt->execute([
            'customer_id' => $customerId
        ]);

        return $stmt->fetchAll();
    }

    public function findByIdAndCustomerId(int $accountId, int $customerId): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, is_active FROM accounts
                WHERE id = :account_id AND customer_id = :customer_id"
        );

        $stmt->execute([
            'account_id' => $accountId,
            'customer_id' => $customerId
        ]);

        return $stmt->fetch();
    }

    public function updateStatus(int $accountId, int $customerId, int $status): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE accounts  SET is_active = :status
                WHERE id = :account_id  AND customer_id = :customer_id"
        );

        return $stmt->execute([
            'account_id' => $accountId,
            'customer_id' => $customerId,
            'status' => $status
        ]);
    }

    public function create(int $customerId, string $accountNumber, string $accountType, string $balance): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO accounts ( customer_id, account_number, account_type, balance, is_active )
                    VALUES (:customer_id, :account_number, :account_type, :balance, 1 )"
        );

        return $stmt->execute([
            'customer_id' => $customerId,
            'account_number' => $accountNumber,
            'account_type' => $accountType,
            'balance' => $balance
        ]);
    }

    public function generateAccountNumber(): string
    {
        /* Formato de cuenta: XX-XXXXX-XXX */
        do {
            $part1 = random_int(10, 99);
            $part2 = random_int(10000, 99999);
            $part3 = random_int(100, 999);

            $accountNumber = "{$part1}-{$part2}-{$part3}";
        } while ($this->accountNumberExists($accountNumber));

        return $accountNumber;
    }

    public function accountNumberExists(string $accountNumber): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM accounts WHERE account_number = :account_number");

        $stmt->execute([
            'account_number' => $accountNumber
        ]);

        return $stmt->fetch() !== false;
    }

    /*=============================================
    CUENTAS INSCRITAS
    =============================================*/
    public function findRegisteredByCustomerId(int $customerId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
            ra.id AS registration_id,
            a.id AS account_id,
            a.account_number,
            a.account_type,
            c.first_name,
            c.last_name  FROM registered_accounts ra INNER JOIN accounts a ON ra.account_id = a.id
                        INNER JOIN customers c ON a.customer_id = c.id WHERE ra.customer_id = :customer_id"
        );

        $stmt->execute([
            'customer_id' => $customerId
        ]);

        return $stmt->fetchAll();
    }

    public function findByAccountNumberAndDocument(string $accountNumber, string $documentNumber): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.id AS account_id, c.document_number, c.id AS customer_id FROM accounts a INNER JOIN customers c ON a.customer_id = c.id
                     WHERE a.account_number = :account_number AND c.document_number = :document_number"
        );

        $stmt->execute([
            'account_number' => $accountNumber,
            'document_number' => $documentNumber
        ]);

        return $stmt->fetch();
    }

    public function isAlreadyRegistered(int $customerId, int $accountId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM registered_accounts 
             WHERE customer_id = :customer_id AND account_id = :account_id"
        );

        $stmt->execute([
            'customer_id' => $customerId,
            'account_id' => $accountId
        ]);

        return $stmt->fetch() !== false;
    }

    public function register(int $customerId, int $accountId): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO registered_accounts (customer_id, account_id) 
             VALUES (:customer_id, :account_id)"
        );

        return $stmt->execute([
            'customer_id' => $customerId,
            'account_id' => $accountId
        ]);
    }

    public function unregister(int $customerId, int $accountId): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM registered_accounts WHERE customer_id = :customer_id AND account_id = :account_id"
        );

        $result = $stmt->execute([
            'customer_id' => $customerId,
            'account_id' => $accountId
        ]);

        return $result && $stmt->rowCount() > 0;
    }

    // Buscar cuenta
    public function findById(int $accountId): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, customer_id, balance, is_active
         FROM accounts
         WHERE id = :account_id"
        );

        $stmt->execute([
            'account_id' => $accountId
        ]);

        return $stmt->fetch();
    }

    // Restar saldo de transferencia
    public function debit(int $accountId, string $amount): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE accounts SET balance = balance - :amount
            WHERE id = :account_id and balance >= :amount"
        );

        $stmt->execute([
            'account_id' => $accountId,
            'amount' => $amount
        ]);

        return $stmt->rowCount() === 1;
    }

    // Sumar saldo de transferencia
    public function credit(int $accountId, string $amount): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE accounts SET balance = balance + :amount
            WHERE id = :account_id"
        );

        $stmt->execute([
            'account_id' => $accountId,
            'amount' => $amount
        ]);

        return $stmt->rowCount() === 1;
    }
}
