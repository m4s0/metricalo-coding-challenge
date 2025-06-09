<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class CardExpMonth
{
    private int $value;

    private function __construct(int $month)
    {
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException('Month must be between 1 and 12');
        }

        $this->value = $month;
    }

    public static function fromInt(int $month): self
    {
        return new self($month);
    }

    public static function fromString(string $month): self
    {
        if (!is_numeric($month)) {
            throw new \InvalidArgumentException('Month must be numeric');
        }

        return new self((int) $month);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getFormatted(): string
    {
        return str_pad((string) $this->value, 2, '0', STR_PAD_LEFT);
    }
}
