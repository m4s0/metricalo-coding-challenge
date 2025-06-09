<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\ValueObject\PaymentTransactionAmount;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class PaymentTransactionAmountTest extends TestCase
{
    public function testCreateFromMinorUnits(): void
    {
        $amount = PaymentTransactionAmount::fromMinorUnits(1000, 'USD');

        $this->assertSame(1000, $amount->getAmount());
        $this->assertSame('USD', $amount->getCurrencyCode());
        $this->assertSame('10.00', $amount->getAmountFormatted());
    }

    public function testCreateFromMajorUnits(): void
    {
        $amount = PaymentTransactionAmount::fromMajorUnits(10.00, 'EUR');

        $this->assertSame(1000, $amount->getAmount());
        $this->assertSame('EUR', $amount->getCurrencyCode());
        $this->assertSame('10.00', $amount->getAmountFormatted());
    }

    public function testFormatAmountWithDecimals(): void
    {
        $amount = PaymentTransactionAmount::fromMinorUnits(1234, 'USD');

        $this->assertSame('12.34', $amount->getAmountFormatted());
    }

    public function testZeroAmount(): void
    {
        $amount = PaymentTransactionAmount::fromMinorUnits(0, 'USD');

        $this->assertSame(0, $amount->getAmount());
        $this->assertSame('0.00', $amount->getAmountFormatted());
    }

    public function testLargeAmount(): void
    {
        $amount = PaymentTransactionAmount::fromMajorUnits(999999.99, 'EUR');

        $this->assertSame(99999999, $amount->getAmount());
        $this->assertSame('999,999.99', $amount->getAmountFormatted());
    }
}
