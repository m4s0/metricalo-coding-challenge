<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\CardBin;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class CardBinTest extends TestCase
{
    public function testValidCardBin(): void
    {
        $cardBin = CardBin::create('424242');

        $this->assertEquals('424242', $cardBin->getValue());
    }

    public function testInvalidCardBinLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BIN must be at least 6 digits');

        CardBin::create('42424');
    }

    public function testInvalidCardBinNonNumeric(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card BIN must be numeric');

        CardBin::create('string');
    }

    public function testFromCardNumber(): void
    {
        $cardBin = CardBin::fromCardNumber('4242424242424242');

        $this->assertEquals('424242', $cardBin->getValue());
    }

    public function testFromCardNumberWithSpaces(): void
    {
        $cardBin = CardBin::fromCardNumber('4242 4242 4242 4242');

        $this->assertEquals('424242', $cardBin->getValue());
    }

    public function testFromShortCardNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BIN must be at least 6 digits');

        CardBin::fromCardNumber('42424');
    }
}
