<?php

declare(strict_types=1);

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\PaymentCard;
use App\Domain\ValueObject\CardCvv;
use App\Domain\ValueObject\CardExpMonth;
use App\Domain\ValueObject\CardExpYear;
use App\Domain\ValueObject\CardHolder;
use App\Domain\ValueObject\CardNumber;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class PaymentCardTest extends TestCase
{
    public function testIsExpiredWithPastMonth(): void
    {
        $year = (int) date('Y');
        $month = (int) date('n') - 1;

        $paymentCard = PaymentCard::create(
            CardHolder::fromString('John Doe'),
            CardNumber::fromString('4242424242424242'),
            CardExpMonth::fromInt($month),
            CardExpYear::fromInt($year),
            CardCvv::fromString('123')
        );

        $this->assertTrue($paymentCard->isExpired());
    }

    public function testIsNotExpiredWithFutureDate(): void
    {
        $year = (int) date('Y');
        $month = (int) date('n') + 1;

        $paymentCard = PaymentCard::create(
            CardHolder::fromString('John Doe'),
            CardNumber::fromString('4242424242424242'),
            CardExpMonth::fromInt($month),
            CardExpYear::fromInt($year),
            CardCvv::fromString('123')
        );

        $this->assertFalse($paymentCard->isExpired());
    }
}
