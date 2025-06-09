<?php

declare(strict_types=1);

namespace App\Infrastructure\Entity;

use App\Domain\ValueObject\PaymentGateway;
use App\Domain\ValueObject\PaymentTransactionStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Infrastructure\Repository\DoctrinePaymentTransactionRepository')]
#[ORM\Table(name: 'payment_transactions')]
final class PaymentTransactionEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::STRING, length: 36)]
        private string $id,

        #[ORM\Column(type: Types::INTEGER)]
        private int $amountInMinorUnits,

        #[ORM\Column(type: Types::STRING, length: 3)]
        private string $currency,

        #[ORM\Column(type: Types::STRING, length: 4)]
        private string $last4Digits,

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,

        #[ORM\Column(type: Types::STRING, enumType: PaymentGateway::class)]
        private PaymentGateway $gateway,

        #[ORM\Column(type: Types::STRING, enumType: PaymentTransactionStatus::class)]
        private PaymentTransactionStatus $status,

        #[ORM\Column(type: Types::STRING, nullable: true)]
        private ?string $externalTransactionId = null,

        #[ORM\Column(type: Types::JSON, nullable: true)]
        private ?array $metadata = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmountInMinorUnits(): int
    {
        return $this->amountInMinorUnits;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getLast4Digits(): string
    {
        return $this->last4Digits;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getGateway(): PaymentGateway
    {
        return $this->gateway;
    }

    public function getStatus(): PaymentTransactionStatus
    {
        return $this->status;
    }

    public function getExternalTransactionId(): ?string
    {
        return $this->externalTransactionId;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function updateFromDomain(
        int $amountInMinorUnits,
        string $currency,
        string $last4Digits,
        \DateTimeImmutable $createdAt,
        PaymentGateway $gateway,
        PaymentTransactionStatus $status = PaymentTransactionStatus::PENDING,
        ?string $externalTransactionId = null,
        ?array $metadata = null,
    ): void {
        $this->amountInMinorUnits = $amountInMinorUnits;
        $this->currency = $currency;
        $this->last4Digits = $last4Digits;
        $this->createdAt = $createdAt;
        $this->gateway = $gateway;
        $this->externalTransactionId = $externalTransactionId;
        $this->status = $status;
        $this->metadata = $metadata;
    }
}
