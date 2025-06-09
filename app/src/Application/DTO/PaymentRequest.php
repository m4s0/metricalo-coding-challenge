<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Application\Exception\PaymentRequestValidationException;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class PaymentRequest
{
    private function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $amount,

        #[Assert\NotBlank]
        #[Assert\Currency]
        public string $currency,

        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 100, minMessage: 'Card holder name must be at least 1 character', maxMessage: 'Card holder name cannot exceed 100 characters')]
        public string $cardHolder,

        #[Assert\NotBlank]
        #[Assert\Regex('/^\d{13,19}$/', message: 'Card number must be 13-19 digits')]
        public string $cardNumber,

        #[Assert\NotBlank]
        #[Assert\Range(min: 1, max: 12)]
        public int $cardExpMonth,

        #[Assert\NotBlank]
        #[Assert\Callback(['App\Application\Validator\Constraints\CardExpirationYearValidator', 'validate'])]
        public int $cardExpYear,

        #[Assert\NotBlank]
        #[Assert\Regex('/^\d{3,4}$/', message: 'CVV must be 3 or 4 digits')]
        public string $cardCvv,

        #[Assert\NotBlank]
        #[Assert\Choice(['shift4', 'aci'])]
        public string $gateway,
    ) {
    }

    public static function create(
        string $cardHolder,
        int $amount,
        string $currency,
        string $cardNumber,
        int $cardExpMonth,
        int $cardExpYear,
        string $cardCvv,
        string $gateway,
    ): self {
        return new self(
            $amount,
            $currency,
            $cardHolder,
            $cardNumber,
            $cardExpMonth,
            $cardExpYear,
            $cardCvv,
            $gateway
        );
    }

    public static function fromArray(array $data): self
    {
        self::validate($data);

        return new self(
            (int) $data['amount'],
            (string) $data['currency'],
            (string) $data['cardHolder'],
            (string) $data['cardNumber'],
            (int) $data['cardExpMonth'],
            (int) $data['cardExpYear'],
            (string) $data['cardCvv'],
            (string) $data['gateway']
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'card_number' => $this->cardNumber,
            'card_exp_month' => $this->cardExpMonth,
            'card_exp_year' => $this->cardExpYear,
            'card_cvv' => $this->cardCvv,
            'gateway' => $this->gateway,
        ];
    }

    public static function validate(array $data): void
    {
        $missing = array_filter([
            'gateway',
            'amount',
            'currency',
            'cardHolder',
            'cardNumber',
            'cardExpMonth',
            'cardExpYear',
            'cardCvv',
        ], fn ($field) => empty($data[$field]));

        if (!empty($missing)) {
            throw new PaymentRequestValidationException($missing);
        }
    }
}
