<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Gateway\Aci;

use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\ValueObject\PaymentTransactionAmount;
use App\Domain\ValueObject\PaymentTransactionId;
use App\Infrastructure\Gateway\Aci\AciPaymentGateway;
use App\Infrastructure\Gateway\Aci\AciPaymentRequestMapper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @group Unit
 */
class AciPaymentGatewayTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private AciPaymentRequestMapper $requestMapper;
    private AciPaymentGateway $gateway;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->requestMapper = $this->createMock(AciPaymentRequestMapper::class);

        $this->gateway = new AciPaymentGateway(
            $this->httpClient,
            'test-api-key',
            'test-entity-id',
            'https://api.test.com',
            $this->logger,
            $this->requestMapper
        );
    }

    public function testProcessPaymentSuccessfully(): void
    {
        $transactionId = PaymentTransactionId::generate();
        $amount = PaymentTransactionAmount::fromMinorUnits(1000, 'USD');
        $transaction = $this->createMock(PaymentTransaction::class);
        $transaction->method('getId')->willReturn($transactionId);
        $transaction->method('getPaymentTransactionAmount')->willReturn($amount);

        $requestData = ['payment_data' => 'test'];
        $responseData = ['id' => 'external-transaction-id'];

        $this->requestMapper
            ->expects($this->once())
            ->method('toGatewayFormat')
            ->with($transaction, 'test-entity-id', AciPaymentGateway::DEBIT_PAYMENT)
            ->willReturn($requestData);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($responseData);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.test.com',
                $this->callback(function ($options) use ($requestData) {
                    return isset($options['headers']['Authorization'])
                        && 'Bearer test-api-key' === $options['headers']['Authorization']
                        && $options['body'] === http_build_query($requestData);
                })
            )
            ->willReturn($response);

        $processPayment = $this->gateway->processPayment($transaction);

        $this->assertTrue($processPayment->isSuccessful);
        $this->assertEquals('external-transaction-id', $processPayment->externalTransactionId);
    }

    public function testProcessPaymentFailure(): void
    {
        $transaction = $this->createMock(PaymentTransaction::class);
        $transaction
            ->method('getId')
            ->willReturn(PaymentTransactionId::generate());

        $this->requestMapper
            ->method('toGatewayFormat')
            ->willReturn([]);

        $this->httpClient
            ->method('request')
            ->willThrowException(new TransportException('Connection error', 500));

        $processPayment = $this->gateway->processPayment($transaction);

        $this->assertFalse($processPayment->isSuccessful);
        $this->assertEquals('Connection error', $processPayment->errorMessage);
    }
}
