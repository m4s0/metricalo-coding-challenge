<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class PaymentTransactionDate
{
    private \DateTimeImmutable $value;

    private function __construct(\DateTimeImmutable $date)
    {
        $this->value = $date;
    }

    public static function now(): self
    {
        return new self(new \DateTimeImmutable());
    }

    public static function fromDateTime(?\DateTimeImmutable $date = null): self
    {
        return new self($date ?? new \DateTimeImmutable());
    }

    public function getValue(): \DateTimeImmutable
    {
        return $this->value;
    }

    public function getFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->value->format($format);
    }

    public function toString(): string
    {
        return $this->value->format(\DateTimeImmutable::ATOM);
    }
}
