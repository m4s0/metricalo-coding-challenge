<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

final readonly class PaymentTransactionId
{
    private Uuid $value;

    private function __construct(Uuid $uuid)
    {
        $this->value = $uuid;
    }

    public static function generate(): self
    {
        return new self(Uuid::v4());
    }

    public static function fromString(string $id): self
    {
        return new self(Uuid::fromString($id));
    }

    public function getValue(): Uuid
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value->toRfc4122();
    }
}
