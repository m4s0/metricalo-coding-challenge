<?php

declare(strict_types=1);

namespace App\Tests\Presentation;

use App\Presentation\GetViolationsDetails;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @group Unit
 */
class GetViolationsDetailsTest extends TestCase
{
    public function testForJsonResponseWithSingleViolation(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                'Invalid value',
                null,
                [],
                null,
                'email',
                null
            ),
        ]);

        $result = GetViolationsDetails::forJsonResponse($violations);

        $this->assertCount(1, $result);
        $this->assertSame([
            [
                'field' => 'email',
                'message' => 'Invalid value',
            ],
        ], $result);
    }

    public function testForJsonResponseWithMultipleViolations(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('Invalid email', null, [], null, 'email', null),
            new ConstraintViolation('Too short', null, [], null, 'password', null),
        ]);

        $result = GetViolationsDetails::forJsonResponse($violations);

        $this->assertCount(2, $result);
        $this->assertSame([
            [
                'field' => 'email',
                'message' => 'Invalid email',
            ],
            [
                'field' => 'password',
                'message' => 'Too short',
            ],
        ], $result);
    }

    public function testForCommandOutputWithSingleViolation(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                'Invalid value',
                null,
                [],
                null,
                'email',
                null
            ),
        ]);

        $result = GetViolationsDetails::forCommandOutput($violations);

        $this->assertCount(1, $result);
        $this->assertSame(['"email": Invalid value'], $result);
    }

    public function testForCommandOutputWithMultipleViolations(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('Invalid email', null, [], null, 'email', null),
            new ConstraintViolation('Too short', null, [], null, 'password', null),
        ]);

        $result = GetViolationsDetails::forCommandOutput($violations);

        $this->assertCount(2, $result);
        $this->assertSame([
            '"email": Invalid email',
            '"password": Too short',
        ], $result);
    }
}
