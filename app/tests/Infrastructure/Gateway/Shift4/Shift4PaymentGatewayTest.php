<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Gateway\Shift4;

use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\ValueObject\PaymentTransactionId;
use App\Infrastructure\Gateway\Shift4\Shift4PaymentGateway;
use App\Infrastructure\Gateway\Shift4\Shift4PaymentRequestMapper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shift4\Exception\Shift4Exception;
use Shift4\Request\ChargeRequest;
use Shift4\Response\Charge;
use Shift4\Shift4Gateway;

/**
 * @group Unit
 */
class Shift4PaymentGatewayTest extends TestCase
{
    private LoggerInterface $logger;
    private Shift4PaymentRequestMapper $requestMapper;
    private Shift4Gateway $shift4Gateway;
    private Shift4PaymentGateway $gateway;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->requestMapper = $this->createMock(Shift4PaymentRequestMapper::class);
        $this->shift4Gateway = $this->createMock(Shift4Gateway::class);

        $this->gateway = new Shift4PaymentGateway(
            $this->shift4Gateway,
            $this->logger,
            $this->requestMapper
        );
    }

    public function testProcessPaymentSuccessfully(): void
    {
        $transactionId = PaymentTransactionId::generate();
        $transaction = $this->createMock(PaymentTransaction::class);
        $transaction->method('getId')->willReturn($transactionId);

        $chargeRequest = new ChargeRequest();
        $chargeRequest->set('amount', 1000);
        $chargeRequest->set('currency', 'USD');

        $charge = $this->createMock(Charge::class);
        $charge->method('getId')->willReturn('external-transaction-id');

        $this->requestMapper
            ->expects($this->once())
            ->method('toGatewayFormat')
            ->with($transaction)
            ->willReturn($chargeRequest);

        $this->shift4Gateway
            ->expects($this->once())
            ->method('createCharge')
            ->with($chargeRequest)
            ->willReturn($charge);

        $result = $this->gateway->processPayment($transaction);

        $this->assertTrue($result->isSuccessful);
        $this->assertEquals('external-transaction-id', $result->externalTransactionId);
    }

    public function testProcessPaymentFailure(): void
    {
        $transactionId = PaymentTransactionId::generate();
        $transaction = $this->createMock(PaymentTransaction::class);
        $transaction->method('getId')->willReturn($transactionId);

        $chargeRequest = new ChargeRequest();

        $this->requestMapper
            ->method('toGatewayFormat')
            ->willReturn($chargeRequest);

        $this->shift4Gateway
            ->method('createCharge')
            ->willThrowException(new Shift4Exception('Payment failed'));

        $result = $this->gateway->processPayment($transaction);

        $this->assertFalse($result->isSuccessful);
        $this->assertEquals('Payment failed', $result->errorMessage);
    }
}
