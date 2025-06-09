<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\CardCvv;
use App\Domain\ValueObject\CardExpMonth;
use App\Domain\ValueObject\CardExpYear;
use App\Domain\ValueObject\CardHolder;
use App\Domain\ValueObject\CardNumber;

final readonly class PaymentCard
{
    private function __construct(
        private CardHolder $cardHolder,
        private CardNumber $cardNumber,
        private CardExpMonth $cardExpMonth,
        private CardExpYear $cardExpYear,
        private CardCvv $cardCvv,
    ) {
    }

    public static function create(
        CardHolder $cardHolder,
        CardNumber $cardNumber,
        CardExpMonth $cardExpMonth,
        CardExpYear $cardExpYear,
        CardCvv $cardCvv,
    ): self {
        return new self($cardHolder, $cardNumber, $cardExpMonth, $cardExpYear, $cardCvv);
    }

    public function getCardHolder(): CardHolder
    {
        return $this->cardHolder;
    }

    public function getCardNumber(): CardNumber
    {
        return $this->cardNumber;
    }

    public function getCardExpMonth(): CardExpMonth
    {
        return $this->cardExpMonth;
    }

    public function getCardExpYear(): CardExpYear
    {
        return $this->cardExpYear;
    }

    public function getCardCvv(): CardCvv
    {
        return $this->cardCvv;
    }

    public function getBin(): string
    {
        return $this->cardNumber->getBin();
    }

    public function getLast4Digits(): string
    {
        return $this->cardNumber->getLast4Digits();
    }

    public function isExpired(): bool
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        return $this->cardExpYear->getValue() < $currentYear
            || ($this->cardExpYear->getValue() === $currentYear && $this->cardExpMonth->getValue() < $currentMonth);
    }
}
