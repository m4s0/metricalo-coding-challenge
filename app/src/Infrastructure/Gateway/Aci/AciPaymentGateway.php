<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\Aci;

use App\Application\DTO\GatewayResponse;
use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\Service\PaymentGatewayInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AciPaymentGateway implements PaymentGatewayInterface
{
    public const DEBIT_PAYMENT = 'DB';
    public const PRE_AUTHORIZE_PAYMENT = 'PA';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $entityId,
        private readonly string $apiUrl,
        private readonly LoggerInterface $logger,
        private readonly AciPaymentRequestMapper $aciPaymentRequestMapper,
    ) {
    }

    public function processPayment(PaymentTransaction $paymentTransaction): GatewayResponse
    {
        try {
            $requestData = $this->aciPaymentRequestMapper->toGatewayFormat(
                $paymentTransaction,
                $this->entityId,
                self::DEBIT_PAYMENT
            );
            $this->logger->info('Sending request to ACI', [
                'transaction_id' => $paymentTransaction->getId()->toString(),
                'amount' => $paymentTransaction->getPaymentTransactionAmount()->getAmount(),
                'currency' => $paymentTransaction->getPaymentTransactionAmount()->getCurrencyCode(),
            ]);

            $response = $this->httpClient->request(
                'POST',
                $this->apiUrl,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->apiKey,
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'charset' => 'UTF-8',
                    ],
                    'body' => http_build_query($requestData),
                ]
            );

            $responseData = $response->toArray();
            $this->logger->info('Received response from ACI', [
                'transaction_id' => $paymentTransaction->getId()->toString(),
                'response' => $responseData,
            ]);

            return GatewayResponse::success(
                externalTransactionId: $responseData['id'],
            );
        } catch (ExceptionInterface $e) {
            $this->logger->error('ACI gateway error', [
                'transaction_id' => $paymentTransaction->getId()->toString(),
                'error' => $e->getMessage(),
            ]);

            return GatewayResponse::failure(
                errorCode: $e->getCode(),
                errorMessage: $e->getMessage(),
            );
        }
    }
}
