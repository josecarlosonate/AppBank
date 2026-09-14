<?php

namespace App\Enums;

enum TransferResult
{
    case SUCCESS;
    case SOURCE_ACCOUNT_NOT_FOUND;
    case DESTINATION_ACCOUNT_NOT_FOUND;
    case SOURCE_ACCOUNT_INACTIVE;
    case DESTINATION_ACCOUNT_INACTIVE;
    case INSUFFICIENT_BALANCE;
}
