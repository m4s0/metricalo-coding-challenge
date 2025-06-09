<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\CardExpYear;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class CardExpYearTest extends TestCase
{
    public function testFromIntCreatesValidInstance(): void
    {
        $currentYear = (int) date('Y');
        $year = CardExpYear::fromInt($currentYear);

        $this->assertSame($currentYear, $year->getValue());
    }

    public function testFromIntThrowsExceptionForPastYear(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid expiration year');

        CardExpYear::fromInt((int) date('Y') - 1);
    }

    public function testFromTwoDigitYearCreatesValidInstance(): void
    {
        $currentYear = (int) date('Y');
        $twoDigitYear = substr((string) $currentYear, -2);
        $year = CardExpYear::fromTwoDigitYear($twoDigitYear);

        $this->assertSame($currentYear, $year->getValue());
    }

    public function testFromTwoDigitYearThrowsExceptionForInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Year must be exactly 2 digits');

        CardExpYear::fromTwoDigitYear('1');
    }

    public function testGetTwoDigitReturnsCorrectFormat(): void
    {
        $currentYear = (int) date('Y');
        $year = CardExpYear::fromInt($currentYear);

        $this->assertSame(substr((string) $currentYear, -2), $year->getTwoDigit());
    }

    public function testFromTwoDigitYearHandlesCenturyRollover(): void
    {
        $currentYear = (int) date('Y');
        $futureYear = substr((string) ($currentYear + 1), -2);
        $year = CardExpYear::fromTwoDigitYear($futureYear);

        $this->assertSame($currentYear + 1, $year->getValue());
    }
}
