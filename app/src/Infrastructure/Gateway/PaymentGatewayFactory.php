<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway;

use App\Domain\Exception\PaymentProcessingException;
use App\Domain\Service\PaymentGatewayInterface;
use App\Domain\ValueObject\PaymentGateway;

final class PaymentGatewayFactory
{
    public function __construct(
        private readonly array $gateways,
    ) {
    }

    public function create(PaymentGateway $gatewayType): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$gatewayType->value])) {
            throw new PaymentProcessingException(sprintf('Gateway "%s" is not registered', $gatewayType->value));
        }

        return $this->gateways[$gatewayType->value];
    }

    /** @return PaymentGateway[] */
    public function getAvailableGateways(): array
    {
        return array_keys($this->gateways);
    }
}
