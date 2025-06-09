<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory;

use Shift4\Shift4Gateway;

class Shift4GatewayFactory
{
    public function create(string $secretKey): Shift4Gateway
    {
        return new Shift4Gateway($secretKey);
    }
}
