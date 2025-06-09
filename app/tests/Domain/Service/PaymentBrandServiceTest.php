<?php

declare(strict_types=1);

namespace App\Tests\Domain\Service;

use App\Domain\Service\PaymentBrandService;
use App\Domain\ValueObject\CardBin;
use PHPUnit\Framework\TestCase;

/**
 * @group Unit
 */
class PaymentBrandServiceTest extends TestCase
{
    private PaymentBrandService $paymentBrandService;

    protected function setUp(): void
    {
        $this->paymentBrandService = new PaymentBrandService();
    }

    /**
     * @dataProvider visaCardBinProvider
     */
    public function testDetectVisaCard(string $bin): void
    {
        $cardBin = CardBin::create($bin);

        $brand = $this->paymentBrandService->determineFromBin($cardBin);

        $this->assertSame('VISA', $brand);
    }

    /**
     * @dataProvider mastercardBinProvider
     */
    public function testDetectMastercard(string $bin): void
    {
        $cardBin = CardBin::create($bin);

        $brand = $this->paymentBrandService->determineFromBin($cardBin);

        $this->assertSame('MASTERCARD', $brand);
    }

    /**
     * @dataProvider amexCardBinProvider
     */
    public function testDetectAmex(string $bin): void
    {
        $cardBin = CardBin::create($bin);

        $brand = $this->paymentBrandService->determineFromBin($cardBin);

        $this->assertSame('AMEX', $brand);
    }

    public function testDetectDiscover(): void
    {
        $bin = '601234';
        $cardBin = CardBin::create($bin);

        $brand = $this->paymentBrandService->determineFromBin($cardBin);

        $this->assertSame('DISCOVER', $brand);
    }

    public function testUnknownCardBrand(): void
    {
        $bin = '901234';
        $cardBin = CardBin::create($bin);

        $brand = $this->paymentBrandService->determineFromBin($cardBin);

        $this->assertSame('Unknown', $brand);
    }

    public function visaCardBinProvider(): array
    {
        return [
            'standard visa' => ['400000'],
            'visa electron' => ['428485'],
            'visa debit' => ['432123'],
        ];
    }

    public function mastercardBinProvider(): array
    {
        return [
            'standard range 51-55' => ['510000'],
            'standard range 51-55 end' => ['550000'],
            'new range 22-27' => ['220000'],
            'new range 22-27 end' => ['270000'],
        ];
    }

    public function amexCardBinProvider(): array
    {
        return [
            'amex 34' => ['340000'],
            'amex 37' => ['370000'],
        ];
    }
}
