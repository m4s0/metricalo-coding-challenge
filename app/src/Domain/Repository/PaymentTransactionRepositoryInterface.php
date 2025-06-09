<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\ValueObject\PaymentTransactionId;

interface PaymentTransactionRepositoryInterface
{
    public function save(PaymentTransaction $paymentTransaction): void;

    public function findById(PaymentTransactionId $id): ?PaymentTransaction;
}
