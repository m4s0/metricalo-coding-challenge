<?php

declare(strict_types=1);

namespace App\Domain\Aggregate;

use App\Domain\Entity\PaymentCard;
use App\Domain\ValueObject\PaymentGateway;
use App\Domain\ValueObject\PaymentTransactionAmount;
use App\Domain\ValueObject\PaymentTransactionDate;
use App\Domain\ValueObject\PaymentTransactionId;
use App\Domain\ValueObject\PaymentTransactionStatus;
use App\Infrastructure\Entity\PaymentTransactionEntity;

class PaymentTransaction
{
    private PaymentTransactionId $id;
    private PaymentTransactionAmount $paymentTransactionAmount;
    private PaymentCard $paymentCard;
    private PaymentTransactionDate $createdAt;
    private PaymentGateway $paymentGateway;
    private PaymentTransactionStatus $paymentTransactionStatus;
    private ?string $externalTransactionId = null;
    private ?array $metadata = null;

    private function __construct(
        PaymentTransactionId $id,
        PaymentTransactionAmount $paymentTransactionAmount,
        PaymentGateway $paymentGateway,
        ?PaymentCard $paymentCard,
        ?PaymentTransactionDate $createdAt,
    ) {
        $this->id = $id;
        $this->paymentTransactionAmount = $paymentTransactionAmount;
        $this->paymentCard = $paymentCard;
        $this->paymentGateway = $paymentGateway;
        $this->createdAt = $createdAt;
        $this->paymentTransactionStatus = PaymentTransactionStatus::PENDING;
    }

    public static function create(
        PaymentTransactionAmount $transactionAmount,
        PaymentCard $paymentCard,
        PaymentGateway $paymentGateway,
    ): self {
        return new self(
            PaymentTransactionId::generate(),
            $transactionAmount,
            $paymentGateway,
            $paymentCard,
            PaymentTransactionDate::now()
        );
    }

    public static function fromEntity(PaymentTransactionEntity $paymentTransactionEntity): PaymentTransaction
    {
        return new self(
            PaymentTransactionId::fromString($paymentTransactionEntity->getId()),
            PaymentTransactionAmount::fromMinorUnits(
                $paymentTransactionEntity->getAmountInMinorUnits(),
                $paymentTransactionEntity->getCurrency()
            ),
            PaymentGateway::from($paymentTransactionEntity->getGateway()->value),
            null,
            PaymentTransactionDate::fromDateTime($paymentTransactionEntity->getCreatedAt())
        );
    }

    public function getId(): PaymentTransactionId
    {
        return $this->id;
    }

    public function getPaymentTransactionAmount(): PaymentTransactionAmount
    {
        return $this->paymentTransactionAmount;
    }

    public function getPaymentCard(): PaymentCard
    {
        return $this->paymentCard;
    }

    public function getCreatedAt(): PaymentTransactionDate
    {
        return $this->createdAt;
    }

    public function getPaymentGateway(): PaymentGateway
    {
        return $this->paymentGateway;
    }

    public function getExternalTransactionId(): ?string
    {
        return $this->externalTransactionId;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getPaymentTransactionStatus(): PaymentTransactionStatus
    {
        return $this->paymentTransactionStatus;
    }

    public function markAsSuccessful(string $externalTransactionId): void
    {
        $this->externalTransactionId = $externalTransactionId;
        $this->paymentTransactionStatus = PaymentTransactionStatus::SUCCESSFUL;
    }

    public function markAsFailed(array $metadata): void
    {
        $this->paymentTransactionStatus = PaymentTransactionStatus::FAILED;
        $this->metadata = $metadata;
    }
}
