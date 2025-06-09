<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\CardNumber;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class CardNumberTest extends TestCase
{
    /**
     * @dataProvider validCardNumbersProvider
     */
    public function testCreatesCardNumberFromValidString(string $input, string $expected): void
    {
        $cardNumber = CardNumber::fromString($input);

        self::assertSame($expected, $cardNumber->getValue());
    }

    /**
     * @dataProvider invalidCardNumbersProvider
     */
    public function testThrowsExceptionForInvalidCardNumbers(string $invalidNumber): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid card number format');

        CardNumber::fromString($invalidNumber);
    }

    public function testReturnsLastFourDigits(): void
    {
        $cardNumber = CardNumber::fromString('4532015112830366');

        self::assertSame('0366', $cardNumber->getLast4Digits());
    }

    public function testReturnsBin(): void
    {
        $cardNumber = CardNumber::fromString('4532015112830366');

        self::assertSame('453201', $cardNumber->getBin());
    }

    public function validCardNumbersProvider(): array
    {
        return [
            'standard card' => ['4532015112830366', '4532015112830366'],
            'card with spaces' => ['4532 0151 1283 0366', '4532015112830366'],
            'minimum length' => ['4532015112830', '4532015112830'],
            'maximum length' => ['6205500000000000004', '6205500000000000004'],
        ];
    }

    public function invalidCardNumbersProvider(): array
    {
        return [
            'too short' => ['453201511'],
            'too long' => ['45320151128303661234567'],
            'non numeric' => ['453201A112830366'],
            'invalid checksum' => ['4532015112830367'],
            'empty string' => [''],
        ];
    }
}
