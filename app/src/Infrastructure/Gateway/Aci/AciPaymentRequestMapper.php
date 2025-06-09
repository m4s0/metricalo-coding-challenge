<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\Aci;

use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\Service\PaymentBrandService;
use App\Domain\ValueObject\CardBin;

class AciPaymentRequestMapper
{
    public const VISA = 'VISA';
    public const MASTER = 'MASTER';
    public const AFTERPAY = 'AFTERPAY';
    public const ALIA = 'ALIA';
    public const ALIADEBIT = 'ALIADEBIT';
    public const AMEX = 'AMEX';
    public const APPLEPAY = 'APPLEPAY';
    public const APPLEPAYTKN = 'APPLEPAYTKN';
    public const ARGENCARD = 'ARGENCARD';
    public const CABALDEBIT = 'CABALDEBIT';
    public const CARNET = 'CARNET';
    public const CARTEBANCAIRE = 'CARTEBANCAIRE';
    public const CARTEBLEUE = 'CARTEBLEUE';
    public const CLICK_TO_PAY = 'CLICK_TO_PAY';
    public const DANKORT = 'DANKORT';
    public const DIRECTDEBIT_SEPA = 'DIRECTDEBIT_SEPA';
    public const DISCOVER = 'DISCOVER';
    public const ELO = 'ELO';
    public const GOOGLEPAY = 'GOOGLEPAY';
    public const HIPERCARD = 'HIPERCARD';
    public const JCB = 'JCB';
    public const MADA = 'MADA';
    public const MAESTRO = 'MAESTRO';
    public const MERCADOLIVRE = 'MERCADOLIVRE';
    public const RATEPAY_INVOICE = 'RATEPAY_INVOICE';
    public const UNIONPAY = 'UNIONPAY';
    public const VISADEBIT = 'VISADEBIT';
    public const VISAELECTRON = 'VISAELECTRON';
    public const VPAY = 'VPAY';

    public function __construct(
        private readonly PaymentBrandService $paymentBrandService,
    ) {
    }

    public function toGatewayFormat(PaymentTransaction $transaction, string $entityId, string $paymentType): array
    {
        return [
            'entityId' => $entityId,
            'amount' => $transaction->getPaymentTransactionAmount()->getAmountFormatted(),
            'currency' => $transaction->getPaymentTransactionAmount()->getCurrencyCode(),
            'paymentBrand' => $this->mapBrand($this->paymentBrandService->determineFromBin(CardBin::create($transaction->getPaymentCard()->getBin()))),
            'paymentType' => $paymentType,
            'card.holder' => $transaction->getPaymentCard()->getCardHolder()->getValue(),
            'card.number' => $transaction->getPaymentCard()->getCardNumber()->getValue(),
            'card.expiryMonth' => $transaction->getPaymentCard()->getCardExpMonth()->getFormatted(),
            'card.expiryYear' => $transaction->getPaymentCard()->getCardExpYear()->getValue(),
            'card.cvv' => $transaction->getPaymentCard()->getCardCvv()->getValue(),
        ];
    }

    public function mapBrand(string $brand): string
    {
        return match (strtolower($brand)) {
            'visa' => self::VISA,
            'mastercard' => self::MASTER,
            'afterpay' => self::AFTERPAY,
            'alia' => self::ALIA,
            'aliadebit' => self::ALIADEBIT,
            'amex' => self::AMEX,
            'applepay' => self::APPLEPAY,
            'applepaytkn' => self::APPLEPAYTKN,
            'argencard' => self::ARGENCARD,
            'cabaldebit' => self::CABALDEBIT,
            'carnet' => self::CARNET,
            'cartebancaire' => self::CARTEBANCAIRE,
            'cartebleue' => self::CARTEBLEUE,
            'clicktopay' => self::CLICK_TO_PAY,
            'dankort' => self::DANKORT,
            'directdebitsepa' => self::DIRECTDEBIT_SEPA,
            'discover' => self::DISCOVER,
            'elo' => self::ELO,
            'googlepay' => self::GOOGLEPAY,
            'hipercard' => self::HIPERCARD,
            'jcb' => self::JCB,
            'mada' => self::MADA,
            'maestro' => self::MAESTRO,
            'mercadolivre' => self::MERCADOLIVRE,
            'ratepayinvoice' => self::RATEPAY_INVOICE,
            'unionpay' => self::UNIONPAY,
            'visadebit' => self::VISADEBIT,
            'visaelectron' => self::VISAELECTRON,
            'vpay' => self::VPAY,
        };
    }
}
