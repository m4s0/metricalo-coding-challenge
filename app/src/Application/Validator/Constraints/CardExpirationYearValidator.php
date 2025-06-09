<?php

declare(strict_types=1);

namespace App\Application\Validator\Constraints;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CardExpirationYearValidator
{
    public static function validate($value, ExecutionContextInterface $context): void
    {
        $currentYear = (int) date('Y');
        if ($value < $currentYear || $value > 2050) {
            $context->buildViolation('Card expiration year must be between {{ min }} and {{ max }}.')->setParameters([
                'min' => $currentYear,
                'max' => 2050,
            ])->addViolation();
        }
    }
}
