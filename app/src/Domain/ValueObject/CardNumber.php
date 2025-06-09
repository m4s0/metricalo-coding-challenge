<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class CardNumber
{
    private string $value;

    private function __construct(string $cardNumber)
    {
        $this->value = $cardNumber;
    }

    public static function fromString(string $cardNumber): self
    {
        $cleaned = preg_replace('/\s+/', '', $cardNumber);

        if (!self::isValid($cleaned)) {
            throw new \InvalidArgumentException('Invalid card number format');
        }

        return new self($cleaned);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLast4Digits(): string
    {
        return substr($this->value, -4);
    }

    public function getBin(): string
    {
        return substr($this->value, 0, 6);
    }

    private static function isValid(string $cardNumber): bool
    {
        if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
            return false;
        }

        // Luhn algorithm validation
        $sum = 0;
        $alternate = false;

        for ($i = strlen($cardNumber) - 1; $i >= 0; --$i) {
            $digit = (int) $cardNumber[$i];

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return ($sum % 10) === 0;
    }
}
