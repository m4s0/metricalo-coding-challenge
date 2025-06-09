<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\Repository\PaymentTransactionRepositoryInterface;
use App\Domain\ValueObject\PaymentTransactionId;
use App\Infrastructure\Entity\PaymentTransactionEntity;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePaymentTransactionRepository implements PaymentTransactionRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(PaymentTransaction $paymentTransaction): void
    {
        $paymentTransactionEntity = $this->entityManager->find(
            PaymentTransactionEntity::class,
            $paymentTransaction->getId()->toString()
        );

        if ($paymentTransactionEntity instanceof PaymentTransactionEntity) {
            $paymentTransactionEntity->updateFromDomain(
                amountInMinorUnits: $paymentTransaction->getPaymentTransactionAmount()->getAmount(),
                currency: $paymentTransaction->getPaymentTransactionAmount()->getCurrencyCode(),
                last4Digits: $paymentTransaction->getPaymentCard()->getLast4Digits(),
                createdAt: $paymentTransaction->getCreatedAt()->getValue(),
                gateway: $paymentTransaction->getPaymentGateway(),
                status: $paymentTransaction->getPaymentTransactionStatus(),
                externalTransactionId: $paymentTransaction->getExternalTransactionId(),
                metadata: $paymentTransaction->getMetadata(),
            );
            $this->entityManager->flush();

            return;
        }

        $paymentTransactionEntity = new PaymentTransactionEntity(
            id: $paymentTransaction->getId()->toString(),
            amountInMinorUnits: $paymentTransaction->getPaymentTransactionAmount()->getAmount(),
            currency: $paymentTransaction->getPaymentTransactionAmount()->getCurrencyCode(),
            last4Digits: $paymentTransaction->getPaymentCard()->getLast4Digits(),
            createdAt: $paymentTransaction->getCreatedAt()->getValue(),
            gateway: $paymentTransaction->getPaymentGateway(),
            status: $paymentTransaction->getPaymentTransactionStatus(),
            externalTransactionId: $paymentTransaction->getExternalTransactionId(),
            metadata: $paymentTransaction->getMetadata(),
        );
        $this->entityManager->persist($paymentTransactionEntity);
        $this->entityManager->flush();
    }

    public function findById(PaymentTransactionId $id): ?PaymentTransaction
    {
        $paymentTransactionEntity = $this->entityManager->find(PaymentTransactionEntity::class, $id->toString());
        if (!$paymentTransactionEntity instanceof PaymentTransactionEntity) {
            return null;
        }

        return PaymentTransaction::fromEntity($paymentTransactionEntity);
    }
}
