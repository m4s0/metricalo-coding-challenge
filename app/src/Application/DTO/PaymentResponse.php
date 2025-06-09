<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Aggregate\PaymentTransaction;

final readonly class PaymentResponse
{
    private function __construct(
        public string $paymentTransactionId,
        public int $amount,
        public string $currency,
        public string $cardBin,
        public string $dateOfCreation,
        public string $gateway,
        public string $status,
        public ?string $externalTransactionId = null,
    ) {
    }

    public static function create(
        string $paymentTransactionId,
        int $amount,
        string $currency,
        string $cardBin,
        string $dateOfCreation,
        string $gateway,
        string $status,
        ?string $externalTransactionId = null,
    ): PaymentResponse {
        return new self(
            paymentTransactionId: $paymentTransactionId,
            amount: $amount,
            currency: $currency,
            cardBin: $cardBin,
            dateOfCreation: $dateOfCreation,
            gateway: $gateway,
            status: $status,
            externalTransactionId: $externalTransactionId
        );
    }

    public static function fromTransaction(PaymentTransaction $paymentTransaction): self
    {
        return new self(
            $paymentTransaction->getId()->toString(),
            $paymentTransaction->getPaymentTransactionAmount()->getAmount(),
            $paymentTransaction->getPaymentTransactionAmount()->getCurrencyCode(),
            $paymentTransaction->getPaymentCard()->getBin(),
            $paymentTransaction->getCreatedAt()->toString(),
            $paymentTransaction->getPaymentGateway()->value,
            $paymentTransaction->getPaymentTransactionStatus()->value,
            $paymentTransaction->getExternalTransactionId()
        );
    }
}
