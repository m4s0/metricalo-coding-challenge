<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class CardExpYear
{
    private int $value;

    private function __construct(int $year)
    {
        $currentYear = (int) date('Y');

        if ($year < $currentYear) {
            throw new \InvalidArgumentException('Invalid expiration year');
        }

        $this->value = $year;
    }

    public static function fromInt(int $year): self
    {
        return new self($year);
    }

    public static function fromTwoDigitYear(string $twoDigitYear): self
    {
        if (!preg_match('/^\d{2}$/', $twoDigitYear)) {
            throw new \InvalidArgumentException('Year must be exactly 2 digits');
        }

        $currentYear = (int) date('Y');
        $century = (int) floor($currentYear / 100) * 100;
        $fullYear = $century + (int) $twoDigitYear;

        if ($fullYear < $currentYear) {
            $fullYear += 100;
        }

        return new self($fullYear);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getTwoDigit(): string
    {
        return substr((string) $this->value, -2);
    }
}
