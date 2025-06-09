<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Gateway\Shift4;

use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\Entity\PaymentCard;
use App\Domain\ValueObject\CardCvv;
use App\Domain\ValueObject\CardExpMonth;
use App\Domain\ValueObject\CardExpYear;
use App\Domain\ValueObject\CardHolder;
use App\Domain\ValueObject\CardNumber;
use App\Domain\ValueObject\PaymentGateway;
use App\Domain\ValueObject\PaymentTransactionAmount;
use App\Infrastructure\Gateway\Shift4\Shift4PaymentRequestMapper;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class Shift4PaymentRequestMapperTest extends TestCase
{
    private Shift4PaymentRequestMapper $shift4PaymentRequestMapper;

    protected function setUp(): void
    {
        $this->shift4PaymentRequestMapper = new Shift4PaymentRequestMapper();
    }

    /**
     * @test
     */
    public function itShouldMapPaymentTransactionToGatewayFormat(): void
    {
        $paymentTransaction = PaymentTransaction::create(
            //            PaymentTransactionId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            PaymentTransactionAmount::fromMinorUnits(1000, 'USD'),
            PaymentCard::create(
                CardHolder::fromString('John Doe'),
                CardNumber::fromString('4242424242424242'),
                CardExpMonth::fromString('12'),
                CardExpYear::fromInt(2025),
                CardCvv::fromString('123'),
            ),
            PaymentGateway::SHIFT4
        );

        $result = $this->shift4PaymentRequestMapper->toGatewayFormat($paymentTransaction);

        $this->assertEquals(1000, $result->getAmount());
        $this->assertEquals('USD', $result->getCurrency());
        $this->assertEquals('Payment transaction '.$paymentTransaction->getId()->getValue(), $result->getDescription());
        $this->assertEquals('John Doe', $result->getCard()->getCardholderName());
        $this->assertEquals('4242424242424242', $result->getCard()->getNumber());
        $this->assertEquals('12', $result->getCard()->getExpMonth());
        $this->assertEquals('2025', $result->getCard()->getExpYear());
        $this->assertEquals('123', $result->getCard()->getCvc());
    }
}
