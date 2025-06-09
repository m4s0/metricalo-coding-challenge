<?php

declare(strict_types=1);

namespace App\Presentation;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class GetViolationsDetails
{
    public static function forJsonResponse(ConstraintViolationListInterface $violations): array
    {
        $output = [];
        foreach ($violations as $violation) {
            $output[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $output;
    }

    public static function forCommandOutput(ConstraintViolationListInterface $violations): array
    {
        $output = [];
        foreach ($violations as $violation) {
            $output[] = '"'.$violation->getPropertyPath().'": '.$violation->getMessage();
        }

        return $output;
    }
}
