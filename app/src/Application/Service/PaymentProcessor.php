<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\Exception\PaymentProcessingException;
use App\Domain\Repository\PaymentTransactionRepositoryInterface;
use App\Infrastructure\Gateway\PaymentGatewayFactory;
use Psr\Log\LoggerInterface;

final class PaymentProcessor
{
    public function __construct(
        private readonly PaymentGatewayFactory $gatewayFactory,
        private readonly PaymentTransactionRepositoryInterface $transactionRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws PaymentProcessingException
     */
    public function process(PaymentTransaction $paymentTransaction): PaymentTransaction
    {
        try {
            $this->logger->info('Starting payment processing', [
                'transaction_id' => $paymentTransaction->getId()->toString(),
                'gateway' => $paymentTransaction->getPaymentGateway()->value,
                'amount' => $paymentTransaction->getPaymentTransactionAmount()->getAmount(),
                'currency' => $paymentTransaction->getPaymentTransactionAmount()->getCurrencyCode(),
            ]);

            $gateway = $this->gatewayFactory->create($paymentTransaction->getPaymentGateway());

            $gatewayResponse = $gateway->processPayment($paymentTransaction);

            if ($gatewayResponse->isSuccessful) {
                $paymentTransaction->markAsSuccessful($gatewayResponse->externalTransactionId);
                $this->logger->info('Payment processed successfully', [
                    'transaction_id' => $paymentTransaction->getId()->toString(),
                    'external_transaction_id' => $gatewayResponse->externalTransactionId,
                ]);
            }
            if (!$gatewayResponse->isSuccessful) {
                $paymentTransaction->markAsFailed(['error_message' => $gatewayResponse->errorMessage,
                    'error_code' => $gatewayResponse->errorCode,
                ]);
                $this->logger->error('Payment processing failed', [
                    'transaction_id' => $paymentTransaction->getId()->toString(),
                    'error_message' => $gatewayResponse->errorMessage,
                    'error_code' => $gatewayResponse->errorCode,
                ]);
            }

            $this->transactionRepository->save($paymentTransaction);

            return $paymentTransaction;
        } catch (\Exception $e) {
            $this->logger->error('Payment processing exception', [
                'transaction_id' => $paymentTransaction->getId()->toString(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $paymentTransaction->markAsFailed([
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);
            $this->transactionRepository->save($paymentTransaction);

            throw new PaymentProcessingException('Payment processing failed: '.$e->getMessage(), previous: $e);
        }
    }
}
