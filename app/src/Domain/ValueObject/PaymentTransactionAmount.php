<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Money\Currency;
use Money\Money;

class PaymentTransactionAmount
{
    private Money $money;

    private function __construct(int $amountInMinorUnits, string $currencyCode)
    {
        $this->money = new Money($amountInMinorUnits, new Currency($currencyCode));
    }

    public static function fromMinorUnits(int $amountInMinorUnits, string $currencyCode): self
    {
        return new self($amountInMinorUnits, $currencyCode);
    }

    public static function fromMajorUnits(float $amount, string $currencyCode): self
    {
        return new self((int) ($amount * 100), $currencyCode);
    }

    public function getAmount(): int
    {
        return (int) $this->money->getAmount();
    }

    public function getCurrencyCode(): string
    {
        return $this->money->getCurrency()->getCode();
    }

    public function getAmountFormatted(): string
    {
        return number_format($this->getAmount() / 100, 2);
    }
}
