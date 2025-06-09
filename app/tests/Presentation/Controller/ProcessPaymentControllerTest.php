<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Application\DTO\PaymentResponse;
use App\Application\UseCase\ProcessPaymentUseCase;
use App\Domain\ValueObject\PaymentGateway;
use App\Domain\ValueObject\PaymentTransactionStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

/**
 * @group Integration
 */
class ProcessPaymentControllerTest extends WebTestCase
{
    public function testSuccessfulPayment(): void
    {
        self::bootKernel();

        $externalTransactionId = Uuid::v4()->toRfc4122();
        $now = new \DateTime();

        $processPaymentUseCaseMocked = $this->createMock(ProcessPaymentUseCase::class);
        $processPaymentUseCaseMocked->expects(self::once())
            ->method('execute')
            ->willReturn(PaymentResponse::create(
                paymentTransactionId: '12345',
                amount: 2500,
                currency: 'USD',
                cardBin: '424242',
                dateOfCreation: $now->format('Y-m-d H:i:s'),
                gateway: PaymentGateway::SHIFT4->value,
                status: PaymentTransactionStatus::SUCCESSFUL->value,
                externalTransactionId: $externalTransactionId,
            ));

        static::getContainer()->set(ProcessPaymentUseCase::class, $processPaymentUseCaseMocked);
        $client = static::getContainer()->get('test.client');

        $payload = [
            'amount' => 2500,
            'currency' => 'USD',
            'cardHolder' => 'Jane Jones',
            'cardNumber' => '4242424242424242',
            'cardExpMonth' => 12,
            'cardExpYear' => 2029,
            'cardCvv' => '123',
        ];

        $client->jsonRequest('POST', '/app/example/shift4', $payload);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('transaction_id', $content);
        $this->assertArrayHasKey('amount', $content);
        $this->assertArrayHasKey('currency', $content);
        $this->assertArrayHasKey('card_bin', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('gateway', $content);
        $this->assertArrayHasKey('external_transaction_id', $content);
        $this->assertSame('12345', $content['transaction_id']);
        $this->assertSame(2500, $content['amount']);
        $this->assertSame('USD', $content['currency']);
        $this->assertSame('424242', $content['card_bin']);
        $this->assertSame($now->format('Y-m-d H:i:s'), $content['created_at']);
        $this->assertSame(PaymentGateway::SHIFT4->value, $content['gateway']);
        $this->assertSame($externalTransactionId, $content['external_transaction_id']);
    }

    public function testInvalidCardData(): void
    {
        self::bootKernel();
        $client = static::getContainer()->get('test.client');

        $payload = [
            'amount' => 2500,
            'currency' => 'USD',
            'cardHolder' => 'Jane Jones',
            'cardNumber' => '4242424242424242',
            'cardExpMonth' => 13,
            'cardExpYear' => 2023,
            'cardCvv' => '13',
        ];

        $client->jsonRequest('POST', '/app/example/shift4', $payload);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('VALIDATION_ERROR', $content['code']);
        $this->assertArrayHasKey('errors', $content);
        $this->assertEquals('cardExpMonth', $content['errors'][0]['field']);
        $this->assertEquals('This value should be between 1 and 12.', $content['errors'][0]['message']);
        $this->assertEquals('cardExpYear', $content['errors'][1]['field']);
        $this->assertEquals('Card expiration year must be between {{ '.date('Y').' }} and {{ 2050 }}.', $content['errors'][1]['message']);
        $this->assertEquals('cardCvv', $content['errors'][2]['field']);
        $this->assertEquals('CVV must be 3 or 4 digits', $content['errors'][2]['message']);
    }

    public function testMissingRequiredFields(): void
    {
        self::bootKernel();
        $client = static::getContainer()->get('test.client');
        $payload = [
            'cardHolder' => 'Jane Jones',
            'cardNumber' => '4242424242424242',
            'cardCvv' => '123',
        ];
        $client->jsonRequest('POST', '/app/example/shift4', $payload);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('PAYMENT_REQUEST_VALIDATION_ERROR', $content['code']);
        $this->assertSame('Missing fields: amount, currency, cardExpMonth, cardExpYear', $content['message']);
    }

    public function testInvalidCurrency(): void
    {
        self::bootKernel();
        $client = static::getContainer()->get('test.client');

        $payload = [
            'amount' => 2500,
            'currency' => 'InvalidCurrency',
            'cardHolder' => 'Jane Jones',
            'cardNumber' => '4242424242424242',
            'cardExpMonth' => 12,
            'cardExpYear' => 2029,
            'cardCvv' => '123',
        ];

        $client->jsonRequest('POST', '/app/example/shift4', $payload);
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('VALIDATION_ERROR', $content['code']);
        $this->assertArrayHasKey('errors', $content);
        $this->assertEquals('currency', $content['errors'][0]['field']);
        $this->assertEquals('This value is not a valid currency.', $content['errors'][0]['message']);
    }

    public function testMethodNotAllowed(): void
    {
        self::bootKernel();
        $client = static::getContainer()->get('test.client');

        $client->request('GET', '/app/example/shift4');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    public function testProcessPaymentInvalidGateway(): void
    {
        self::bootKernel();
        $client = static::getContainer()->get('test.client');

        $client->request('POST', '/app/example/invalid', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }
}
