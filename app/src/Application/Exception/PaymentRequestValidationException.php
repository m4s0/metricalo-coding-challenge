<?php

declare(strict_types=1);

namespace App\Application\Exception;

class PaymentRequestValidationException extends \InvalidArgumentException
{
    private array $missingFields;

    public function __construct(array $missingFields)
    {
        $this->missingFields = $missingFields;
        parent::__construct('Missing fields: '.implode(', ', $missingFields));
    }

    public function getMissingFields(): array
    {
        return $this->missingFields;
    }
}
