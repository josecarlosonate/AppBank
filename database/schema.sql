-- AppBank database schema - MySQL 8.4

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `document_number` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_number` (`document_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;

CREATE TABLE `accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned NOT NULL,
  `balance` decimal(15,2) NOT NULL,
  `account_type` enum('SAVINGS','CHECKING') NOT NULL DEFAULT 'SAVINGS',
  `account_number` varchar(30) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `idx_accounts_customer_id` (`customer_id`),
  CONSTRAINT `fk_accounts_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_accounts_balance_non_negative` CHECK ((`balance` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `registered_accounts`
--

DROP TABLE IF EXISTS `registered_accounts`;

CREATE TABLE `registered_accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned NOT NULL,
  `account_id` int unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  UNIQUE KEY `uq_registered_accounts_customer_account` (`customer_id`,`account_id`),

  KEY `fk_registered_accounts_account` (`account_id`),

  CONSTRAINT `fk_registered_accounts_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_registered_accounts_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;

CREATE TABLE `transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source_account_id` int unsigned NOT NULL,
  `destination_account_id` int unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `concept` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transactions_source_account_id` (`source_account_id`),
  KEY `idx_transactions_destination_account_id` (`destination_account_id`),
  CONSTRAINT `fk_transactions_destination_account` FOREIGN KEY (`destination_account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_transactions_source_account` FOREIGN KEY (`source_account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `chk_transactions_amount_positive` CHECK ((`amount` > 0)),
  CONSTRAINT `chk_transactions_different_accounts` CHECK ((`source_account_id` <> `destination_account_id`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Table structure for table `movements`
--

CREATE TABLE `movements` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `transaction_id` int unsigned NOT NULL,
    `account_id` int unsigned NOT NULL,
    `type` enum('DEBIT', 'CREDIT') NOT NULL,
    `balance_after` decimal(15,2) NOT NULL,

    PRIMARY KEY (`id`),

    UNIQUE KEY `uq_movements_transaction_account` (`transaction_id`, `account_id`),

    KEY `idx_movements_account_id` (`account_id`),

    CONSTRAINT `fk_movements_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_movements_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT `chk_movements_balance_nonnegative` CHECK (`balance_after` >= 0)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;