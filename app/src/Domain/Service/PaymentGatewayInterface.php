<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Application\DTO\GatewayResponse;
use App\Domain\Aggregate\PaymentTransaction;

interface PaymentGatewayInterface
{
    public function processPayment(PaymentTransaction $paymentTransaction): GatewayResponse;
}
