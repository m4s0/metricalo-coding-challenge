<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway\Shift4;

use App\Domain\Aggregate\PaymentTransaction;
use Shift4\Request\ChargeRequest;

class Shift4PaymentRequestMapper
{
    public function toGatewayFormat(PaymentTransaction $transaction): ChargeRequest
    {
        $chargeRequest = new ChargeRequest();
        $chargeRequest->amount($transaction->getPaymentTransactionAmount()->getAmount());
        $chargeRequest->currency($transaction->getPaymentTransactionAmount()->getCurrencyCode());
        $chargeRequest->description('Payment transaction '.$transaction->getId()->toString());

        $card = $transaction->getPaymentCard();
        $chargeRequest->card([
            'cardholderName' => $card->getCardHolder()->getValue(),
            'number' => $card->getCardNumber()->getValue(),
            'expMonth' => $card->getCardExpMonth()->getFormatted(),
            'expYear' => $card->getCardExpYear()->getValue(),
            'cvc' => $card->getCardCvv()->getValue(),
        ]);

        return $chargeRequest;
    }
}
