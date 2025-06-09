<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class CardHolder
{
    private string $value;

    private function __construct(string $cardHolder)
    {
        $this->value = $cardHolder;
    }

    public static function fromString(string $cardHolder): self
    {
        if (empty(trim($cardHolder))) {
            throw new \InvalidArgumentException('Card holder name cannot be empty');
        }

        return new self($cardHolder);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
