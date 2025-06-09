<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum PaymentGateway: string
{
    case SHIFT4 = 'shift4';
    case ACI = 'aci';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::SHIFT4 => 'Shift4',
            self::ACI => 'ACI Worldwide',
        };
    }
}
