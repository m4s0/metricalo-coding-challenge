<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Service\PaymentBrandServiceInterface;
use App\Domain\ValueObject\CardBin;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BinListPaymentBrandService implements PaymentBrandServiceInterface
{
    private const DEFAULT_BRAND = 'VISA';
    private const API_VERSION = '3';
    private const API_URL = 'https://lookup.binlist.net';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function determineFromBin(CardBin $cardBin): string
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf('%s/%s', self::API_URL, $cardBin->getValue()),
                [
                    'headers' => [
                        'Accept-Version' => self::API_VERSION,
                    ],
                ]
            );

            $data = $response->toArray();

            return $data['scheme'] ?? self::DEFAULT_BRAND;
        } catch (ExceptionInterface) {
            return self::DEFAULT_BRAND;
        }
    }
}
