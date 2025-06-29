<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\ValueObject\CardBin;

interface PaymentBrandServiceInterface
{
    public function determineFromBin(CardBin $cardBin): string;
}
