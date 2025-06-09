<?php

declare(strict_types=1);

namespace App\Tests\Application\Validator\Constraints;

use App\Application\Validator\Constraints\CardExpirationYearValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @group Unit
 */
class CardExpirationYearValidatorTest extends TestCase
{
    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
    }

    public function testValidExpirationYear(): void
    {
        $currentYear = (int) date('Y');
        $validYear = $currentYear + 1;

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        CardExpirationYearValidator::validate($validYear, $this->context);
    }

    public function testPastYearIsInvalid(): void
    {
        $currentYear = (int) date('Y');
        $pastYear = $currentYear - 1;

        $violationBuilder = $this->createMock(\Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface::class);

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->with('Card expiration year must be between {{ min }} and {{ max }}.')
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->once())
            ->method('setParameters')
            ->with([
                'min' => $currentYear,
                'max' => 2050,
            ])
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        CardExpirationYearValidator::validate($pastYear, $this->context);
    }

    public function testFutureYearBeyond2050IsInvalid(): void
    {
        $invalidYear = 2051;

        $violationBuilder = $this->createMock(\Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface::class);

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->with('Card expiration year must be between {{ min }} and {{ max }}.')
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->once())
            ->method('setParameters')
            ->with([
                'min' => (int) date('Y'),
                'max' => 2050,
            ])
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        CardExpirationYearValidator::validate($invalidYear, $this->context);
    }
}
