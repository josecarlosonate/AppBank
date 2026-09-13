<?php

namespace App\Controllers;

use App\Models\Customer;

class AuthController
{
    public function __construct(
        private Customer $customer
    ) {}

    public function login()
    {
        $documentNumber = $_POST['document_number'] ?? '';
        $password = $_POST['password'] ?? '';

        $customerData = $this->customer->findByDocumentNumber($documentNumber);

        if (
            $customerData === false ||
            (!password_verify($password, $customerData['password']))
        ) {
            $_SESSION['error'] = 'Credenciales incorrectas';
            header('Location: /login');
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['customer_id'] = $customerData['id'];
        $_SESSION['first_name'] = $customerData['first_name'];
        $_SESSION['last_name'] = $customerData['last_name'];

        header('Location: /dashboard');
        exit;
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();

        header('Location: /login');
        exit;
    }
}
