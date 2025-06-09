<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\PaymentTransactionId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @group Unit
 */
class PaymentTransactionIdTest extends TestCase
{
    public function testGenerateCreatesNewUuid(): void
    {
        $transactionId = PaymentTransactionId::generate();

        self::assertInstanceOf(Uuid::class, $transactionId->getValue());
    }

    public function testFromStringCreatesFromValidUuid(): void
    {
        $uuidString = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $transactionId = PaymentTransactionId::fromString($uuidString);

        self::assertEquals($uuidString, $transactionId->toString());
    }

    public function testToStringReturnsValidUuidString(): void
    {
        $transactionId = PaymentTransactionId::generate();
        $uuidString = $transactionId->toString();

        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuidString);
    }

    public function testFromInvalidStringThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PaymentTransactionId::fromString('invalid-uuid');
    }
}
