<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\Shift4;

use App\Application\DTO\GatewayResponse;
use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\Service\PaymentGatewayInterface;
use Psr\Log\LoggerInterface;
use Shift4\Exception\Shift4Exception;
use Shift4\Shift4Gateway;

class Shift4PaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly Shift4Gateway $gateway,
        private readonly LoggerInterface $logger,
        private readonly Shift4PaymentRequestMapper $shift4PaymentRequestMapper,
    ) {
    }

    public function processPayment(PaymentTransaction $paymentTransaction): GatewayResponse
    {
        try {
            $requestData = $this->shift4PaymentRequestMapper->toGatewayFormat($paymentTransaction);
            $this->logger->info('Sending request to Shift4', [
                'transaction_id' => $paymentTransaction->getId()->toString(),
                'amount' => $requestData->getAmount(),
                'currency' => $requestData->getCurrency(),
            ]);

            $charge = $this->gateway->createCharge($requestData);

            return GatewayResponse::success(
                externalTransactionId: $charge->getId(),
            );
        } catch (Shift4Exception $e) {
            // handle error response - see https://dev.shift4.com/docs/api#error-object
            $this->logger->error('Shift4 gateway error', [
                'transaction_id' => $paymentTransaction->getId()->toString(),
                'errorType' => $e->getType(),
                'errorCode' => $e->getCode(),
                'errorMessage' => $e->getMessage(),
            ]);

            return GatewayResponse::failure(
                errorCode: $e->getCode(),
                errorMessage: $e->getMessage(),
                errorType: $e->getType(),
            );
        }
    }
}
