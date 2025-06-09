<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use App\Application\DTO\GatewayResponse;
use App\Application\DTO\PaymentRequest;
use App\Application\DTO\PaymentResponse;
use App\Application\UseCase\ProcessPaymentUseCase;
use App\Domain\ValueObject\PaymentGateway;
use App\Domain\ValueObject\PaymentTransactionStatus;
use App\Infrastructure\Entity\PaymentTransactionEntity;
use App\Infrastructure\Gateway\Aci\AciPaymentGateway;
use App\Infrastructure\Gateway\Shift4\Shift4PaymentGateway;
use App\Tests\Utils\Helper\DropAndRecreateDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @group Integration
 */
class ProcessPaymentUseCaseTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ProcessPaymentUseCase $processPaymentUseCase;

    public function testExecuteShift4Successfully(): void
    {
        self::bootKernel();

        $shift4PaymentGatewayMocked = $this->createMock(Shift4PaymentGateway::class);
        $shift4PaymentGatewayMocked->expects(self::once())
            ->method('processPayment')
            ->willReturn(GatewayResponse::success(
                externalTransactionId: Uuid::v4()->toRfc4122(),
            ));

        static::getContainer()->set(Shift4PaymentGateway::class, $shift4PaymentGatewayMocked);
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->processPaymentUseCase = static::getContainer()->get(ProcessPaymentUseCase::class);
        DropAndRecreateDatabase::execute($this->entityManager);

        $paymentRequest = PaymentRequest::create(
            cardHolder: 'John Doe',
            amount: 1000,
            currency: 'USD',
            cardNumber: '4111111111111111',
            cardExpMonth: 12,
            cardExpYear: 2025,
            cardCvv: '123',
            gateway: PaymentGateway::SHIFT4->value,
        );

        $result = $this->processPaymentUseCase->execute($paymentRequest);
        $this->assertInstanceOf(PaymentResponse::class, $result);
        $this->assertEquals(1000, $result->amount);
        $this->assertEquals('411111', $result->cardBin);
        $this->assertEquals(PaymentGateway::SHIFT4->value, $result->gateway);
        $this->assertEquals(PaymentTransactionStatus::SUCCESSFUL->value, $result->status);
        $this->assertNotEmpty($result->externalTransactionId);
        $this->assertNotEmpty($result->dateOfCreation);
        $this->assertNotEmpty($result->paymentTransactionId);

        $paymentTransactionEntity = $this->entityManager->find(PaymentTransactionEntity::class, $result->paymentTransactionId);
        $this->assertEquals($result->paymentTransactionId, $paymentTransactionEntity->getId());
        $this->assertEquals($result->externalTransactionId, $paymentTransactionEntity->getExternalTransactionId());
        $this->assertEquals(1000, $paymentTransactionEntity->getAmountInMinorUnits());
        $this->assertEquals('USD', $paymentTransactionEntity->getCurrency());
        $this->assertEquals('1111', $paymentTransactionEntity->getLast4Digits());
        $this->assertEquals(PaymentTransactionStatus::SUCCESSFUL, $paymentTransactionEntity->getStatus());
    }

    public function testExecuteAci4Successfully(): void
    {
        self::bootKernel();

        $aciPaymentGatewayMocked = $this->createMock(AciPaymentGateway::class);
        $aciPaymentGatewayMocked->expects(self::once())
            ->method('processPayment')
            ->willReturn(GatewayResponse::success(
                externalTransactionId: Uuid::v4()->toRfc4122(),
            ));
        static::getContainer()->set(AciPaymentGateway::class, $aciPaymentGatewayMocked);

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->processPaymentUseCase = static::getContainer()->get(ProcessPaymentUseCase::class);
        DropAndRecreateDatabase::execute($this->entityManager);

        $paymentRequest = PaymentRequest::create(
            cardHolder: 'John Doe',
            amount: 1000,
            currency: 'USD',
            cardNumber: '4111111111111111',
            cardExpMonth: 12,
            cardExpYear: 2025,
            cardCvv: '123',
            gateway: PaymentGateway::ACI->value,
        );

        $result = $this->processPaymentUseCase->execute($paymentRequest);
        $this->assertInstanceOf(PaymentResponse::class, $result);
        $this->assertEquals(1000, $result->amount);
        $this->assertEquals('411111', $result->cardBin);
        $this->assertEquals(PaymentGateway::ACI->value, $result->gateway);
        $this->assertEquals(PaymentTransactionStatus::SUCCESSFUL->value, $result->status);
        $this->assertNotEmpty($result->externalTransactionId);
        $this->assertNotEmpty($result->dateOfCreation);
        $this->assertNotEmpty($result->paymentTransactionId);

        $paymentTransactionEntity = $this->entityManager->find(PaymentTransactionEntity::class, $result->paymentTransactionId);
        $this->assertEquals($result->paymentTransactionId, $paymentTransactionEntity->getId());
        $this->assertEquals($result->externalTransactionId, $paymentTransactionEntity->getExternalTransactionId());
        $this->assertEquals(1000, $paymentTransactionEntity->getAmountInMinorUnits());
        $this->assertEquals('USD', $paymentTransactionEntity->getCurrency());
        $this->assertEquals('1111', $paymentTransactionEntity->getLast4Digits());
        $this->assertEquals(PaymentTransactionStatus::SUCCESSFUL, $paymentTransactionEntity->getStatus());
    }
}
