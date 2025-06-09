<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\PaymentRequest;
use App\Application\DTO\PaymentResponse;
use App\Application\Service\PaymentProcessor;
use App\Domain\Aggregate\PaymentTransaction;
use App\Domain\Entity\PaymentCard;
use App\Domain\Exception\PaymentProcessingException;
use App\Domain\ValueObject\CardCvv;
use App\Domain\ValueObject\CardExpMonth;
use App\Domain\ValueObject\CardExpYear;
use App\Domain\ValueObject\CardHolder;
use App\Domain\ValueObject\CardNumber;
use App\Domain\ValueObject\PaymentGateway;
use App\Domain\ValueObject\PaymentTransactionAmount;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessPaymentUseCase
{
    public function __construct(
        private readonly PaymentProcessor $paymentProcessor,
        private readonly LoggerInterface $logger,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @throws PaymentProcessingException
     */
    public function execute(PaymentRequest $paymentRequest): PaymentResponse
    {
        try {
            $violations = $this->validator->validate($paymentRequest);
            if (count($violations) > 0) {
                throw new ValidationFailedException($paymentRequest, $violations);
            }

            $paymentCard = PaymentCard::create(
                CardHolder::fromString($paymentRequest->cardHolder),
                CardNumber::fromString($paymentRequest->cardNumber),
                CardExpMonth::fromInt($paymentRequest->cardExpMonth),
                CardExpYear::fromInt($paymentRequest->cardExpYear),
                CardCvv::fromString($paymentRequest->cardCvv)
            );
            $transactionAmount = PaymentTransactionAmount::fromMinorUnits($paymentRequest->amount, $paymentRequest->currency);
            $paymentTransaction = PaymentTransaction::create($transactionAmount, $paymentCard, PaymentGateway::from($paymentRequest->gateway));

            $processedTransaction = $this->paymentProcessor->process($paymentTransaction);

            return PaymentResponse::fromTransaction($processedTransaction);
        } catch (ValidationFailedException $e) {
            $this->logger->warning('Payment validation failed', [
                'violations' => (string) $e->getViolations(),
                'request_data' => $this->sanitizeLogData($paymentRequest->toArray()),
            ]);

            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error in payment processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $this->sanitizeLogData($paymentRequest->toArray()),
            ]);

            throw $e;
        }
    }

    private function sanitizeLogData(array $data): array
    {
        $sanitized = $data;

        if (isset($sanitized['card_number'])) {
            $cardNumber = $sanitized['card_number'];
            $sanitized['card_number'] = substr($cardNumber, 0, 6).
                str_repeat('*', strlen($cardNumber) - 10).
                substr($cardNumber, -4);
        }

        if (isset($sanitized['card_cvv'])) {
            $sanitized['card_cvv'] = str_repeat('*', strlen($sanitized['card_cvv']));
        }

        return $sanitized;
    }
}
