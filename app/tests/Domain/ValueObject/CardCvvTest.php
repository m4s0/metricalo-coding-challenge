<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\CardCvv;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class CardCvvTest extends TestCase
{
    public function testCreateValidThreeDigitCvv(): void
    {
        $cvv = CardCvv::fromString('123');

        self::assertSame('123', $cvv->getValue());
    }

    public function testCreateValidFourDigitCvv(): void
    {
        $cvv = CardCvv::fromString('1234');

        self::assertSame('1234', $cvv->getValue());
    }

    /**
     * @dataProvider invalidCvvProvider
     */
    public function testThrowsExceptionOnInvalidCvv(string $invalidCvv): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CVV must be 3 or 4 digits');

        CardCvv::fromString($invalidCvv);
    }

    public function invalidCvvProvider(): array
    {
        return [
            'empty string' => [''],
            'non-numeric' => ['abc'],
            'too short' => ['12'],
            'too long' => ['12345'],
            'with spaces' => [' 123 '],
            'with letters' => ['12a'],
        ];
    }
}
