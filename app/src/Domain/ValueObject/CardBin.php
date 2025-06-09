<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class CardBin
{
    private function __construct(
        private string $binNumber,
    ) {
        if (strlen($this->binNumber) < 6) {
            throw new \InvalidArgumentException('BIN must be at least 6 digits');
        }

        if (!is_numeric($this->binNumber)) {
            throw new \InvalidArgumentException('Card BIN must be numeric');
        }
    }

    public static function create(
        string $binNumber,
    ): self {
        return new self($binNumber);
    }

    public static function fromCardNumber(string $cardNumber): self
    {
        $cleanNumber = preg_replace('/\D/', '', $cardNumber);
        $bin = substr($cleanNumber, 0, 6);

        return new self($bin);
    }

    public function getValue(): string
    {
        return $this->binNumber;
    }
}
