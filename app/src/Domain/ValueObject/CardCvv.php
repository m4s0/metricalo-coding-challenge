<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class CardCvv
{
    private string $value;

    private function __construct(string $cvv)
    {
        $this->value = $cvv;
    }

    public static function fromString(string $cvv): self
    {
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            throw new \InvalidArgumentException('CVV must be 3 or 4 digits');
        }

        return new self($cvv);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
