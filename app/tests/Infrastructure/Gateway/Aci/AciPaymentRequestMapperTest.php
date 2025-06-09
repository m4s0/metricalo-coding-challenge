<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Gateway\Aci;

use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\Entity\PaymentCard;
use App\Domain\Service\PaymentBrandService;
use App\Domain\ValueObject\CardBin;
use App\Domain\ValueObject\CardCvv;
use App\Domain\ValueObject\CardExpMonth;
use App\Domain\ValueObject\CardExpYear;
use App\Domain\ValueObject\CardHolder;
use App\Domain\ValueObject\CardNumber;
use App\Domain\ValueObject\PaymentGateway;
use App\Domain\ValueObject\PaymentTransactionAmount;
use App\Infrastructure\Gateway\Aci\AciPaymentRequestMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class AciPaymentRequestMapperTest extends TestCase
{
    private PaymentBrandService|MockObject $paymentBrandService;
    private AciPaymentRequestMapper $aciPaymentRequestMapper;

    protected function setUp(): void
    {
        $this->paymentBrandService = $this->createMock(PaymentBrandService::class);
        $this->aciPaymentRequestMapper = new AciPaymentRequestMapper($this->paymentBrandService);
    }

    /**
     * @dataProvider brandMappingProvider
     */
    public function testMapBrand(string $inputBrand, string $expectedBrand): void
    {
        $this->assertSame($expectedBrand, $this->aciPaymentRequestMapper->mapBrand($inputBrand));
    }

    public function testToGatewayFormat(): void
    {
        $paymentTransaction = PaymentTransaction::create(
            PaymentTransactionAmount::fromMinorUnits(1000, 'USD'),
            PaymentCard::create(
                CardHolder::fromString('John Doe'),
                CardNumber::fromString('4242424242424242'),
                CardExpMonth::fromString('12'),
                CardExpYear::fromInt(2025),
                CardCvv::fromString('123'),
            ),
            PaymentGateway::ACI
        );

        $this->paymentBrandService
            ->expects(self::once())
            ->method('determineFromBin')
            ->with(self::callback(fn (CardBin $cardBin) => '424242' === $cardBin->getValue()))
            ->willReturn('visa');

        $result = $this->aciPaymentRequestMapper->toGatewayFormat($paymentTransaction, 'merchant123', 'DB');

        $this->assertSame([
            'entityId' => 'merchant123',
            'amount' => '10.00',
            'currency' => 'USD',
            'paymentBrand' => 'VISA',
            'paymentType' => 'DB',
            'card.holder' => 'John Doe',
            'card.number' => '4242424242424242',
            'card.expiryMonth' => '12',
            'card.expiryYear' => 2025,
            'card.cvv' => '123',
        ], $result);
    }

    public static function brandMappingProvider(): array
    {
        return [
            ['visa', 'VISA'],
            ['mastercard', 'MASTER'],
            ['amex', 'AMEX'],
            ['discover', 'DISCOVER'],
            ['jcb', 'JCB'],
            ['maestro', 'MAESTRO'],
            ['visadebit', 'VISADEBIT'],
            ['vpay', 'VPAY'],
            ['VISA', 'VISA'],
            ['MASTERCARD', 'MASTER'],
        ];
    }
}
