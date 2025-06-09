<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\ValueObject\PaymentTransactionDate;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class PaymentTransactionDateTest extends TestCase
{
    public function testCanCreateFromNow(): void
    {
        $date = PaymentTransactionDate::now();

        self::assertInstanceOf(\DateTimeImmutable::class, $date->getValue());
    }

    public function testCanCreateFromDateTime(): void
    {
        $dateTime = new \DateTimeImmutable('2024-01-01 12:00:00');
        $date = PaymentTransactionDate::fromDateTime($dateTime);

        self::assertEquals($dateTime, $date->getValue());
    }

    public function testCreateFromDateTimeUsesCurrentTimeWhenNull(): void
    {
        $date = PaymentTransactionDate::fromDateTime();

        self::assertInstanceOf(\DateTimeImmutable::class, $date->getValue());
    }

    public function testCanGetFormattedDate(): void
    {
        $dateTime = new \DateTimeImmutable('2024-01-01 12:00:00');
        $date = PaymentTransactionDate::fromDateTime($dateTime);

        self::assertEquals('2024-01-01 12:00:00', $date->getFormatted());
        self::assertEquals('2024-01-01', $date->getFormatted('Y-m-d'));
    }

    public function testCanConvertToString(): void
    {
        $dateTime = new \DateTimeImmutable('2024-01-01 12:00:00');
        $date = PaymentTransactionDate::fromDateTime($dateTime);

        self::assertEquals($dateTime->format(\DateTimeImmutable::ATOM), $date->toString());
    }
}
