<?php

declare(strict_types=1);

namespace App\Application\DTO;

readonly class GatewayResponse
{
    private function __construct(
        public bool $isSuccessful,
        public ?string $externalTransactionId = null,
        public ?string $errorType = null,
        public string|int|null $errorCode = null,
        public ?string $errorMessage = null,
    ) {
    }

    public static function success(
        string $externalTransactionId,
    ): self {
        return new self(
            isSuccessful: true,
            externalTransactionId: $externalTransactionId,
        );
    }

    public static function failure(
        string|int|null $errorCode,
        string $errorMessage,
        ?string $errorType = null,
    ): self {
        return new self(
            isSuccessful: false,
            errorType: $errorType,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
        );
    }
}
