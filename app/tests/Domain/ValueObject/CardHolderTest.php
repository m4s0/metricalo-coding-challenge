<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\ValueObject\CardHolder;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class CardHolderTest extends TestCase
{
    public function testCreateValidCardHolder(): void
    {
        $cardHolderName = 'John Doe';
        $cardHolder = CardHolder::fromString($cardHolderName);

        self::assertSame($cardHolderName, $cardHolder->getValue());
    }

    public function testThrowsExceptionOnEmptyCardHolder(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card holder name cannot be empty');

        CardHolder::fromString('');
    }

    public function testThrowsExceptionOnWhitespaceCardHolder(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card holder name cannot be empty');

        CardHolder::fromString('   ');
    }
}
