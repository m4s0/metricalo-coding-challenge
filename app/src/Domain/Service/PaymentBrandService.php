<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\ValueObject\CardBin;

class PaymentBrandService implements PaymentBrandServiceInterface
{
    private const VISA = 'VISA';
    private const MASTERCARD = 'MASTERCARD';
    private const AMEX = 'AMEX';
    private const DISCOVER = 'DISCOVER';
    private const UNKNOWN = 'Unknown';

    public function determineFromBin(CardBin $cardBin): string
    {
        $firstDigit = (int) $cardBin->getValue()[0];
        $firstTwoDigits = (int) substr($cardBin->getValue(), 0, 2);

        return match (true) {
            4 === $firstDigit => self::VISA,
            in_array($firstTwoDigits, range(51, 55)) || in_array($firstTwoDigits, range(22, 27)) => self::MASTERCARD,
            in_array($firstTwoDigits, [34, 37]) => self::AMEX,
            60 === $firstTwoDigits => self::DISCOVER,
            default => self::UNKNOWN,
        };
    }
}
