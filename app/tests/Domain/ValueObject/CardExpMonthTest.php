<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\CardExpMonth;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class CardExpMonthTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider validMonthProvider
     */
    public function testCreatesFromValidInteger(int $month, string $expected): void
    {
        $cardExpMonth = CardExpMonth::fromInt($month);

        self::assertSame($month, $cardExpMonth->getValue());
        self::assertSame($expected, $cardExpMonth->getFormatted());
    }

    /**
     * @dataProvider validStringMonthProvider
     */
    public function testCreatesFromValidString(string $month, int $expectedValue, string $expectedFormatted): void
    {
        $cardExpMonth = CardExpMonth::fromString($month);

        self::assertSame($expectedValue, $cardExpMonth->getValue());
        self::assertSame($expectedFormatted, $cardExpMonth->getFormatted());
    }

    /**
     * @dataProvider invalidMonthProvider
     */
    public function testThrowsExceptionForInvalidInteger(int $month): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Month must be between 1 and 12');

        CardExpMonth::fromInt($month);
    }

    public function testThrowsExceptionForNonNumericString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Month must be numeric');

        CardExpMonth::fromString('abc');
    }

    public function validMonthProvider(): array
    {
        return [
            'January' => [1, '01'],
            'September' => [9, '09'],
            'December' => [12, '12'],
        ];
    }

    public function validStringMonthProvider(): array
    {
        return [
            'String one' => ['1', 1, '01'],
            'String nine' => ['9', 9, '09'],
            'String twelve' => ['12', 12, '12'],
        ];
    }

    public function invalidMonthProvider(): array
    {
        return [
            'Zero' => [0],
            'Negative' => [-1],
            'Thirteen' => [13],
        ];
    }
}
