<?php

declare(strict_types=1);

namespace App\Presentation\Command;

use App\Application\DTO\PaymentRequest;
use App\Application\UseCase\ProcessPaymentUseCase;
use App\Domain\Exception\PaymentProcessingException;
use App\Domain\ValueObject\PaymentTransactionStatus;
use App\Presentation\GetViolationsDetails;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsCommand(name: 'app:payment:process', description: 'Process a payment through specified gateway', aliases: ['app:example'])]
final class ProcessPaymentCommand extends Command
{
    public function __construct(
        private readonly ProcessPaymentUseCase $processPaymentUseCase,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('gateway', InputArgument::REQUIRED, 'Payment gateway (aci|shift4)')
            ->addArgument('amount', InputArgument::REQUIRED, 'Payment amount')
            ->addArgument('currency', InputArgument::REQUIRED, 'Currency code (EUR, USD, etc.)')
            ->addArgument('cardHolder', InputArgument::REQUIRED, 'Card Holder')
            ->addArgument('cardNumber', InputArgument::REQUIRED, 'Card number')
            ->addArgument('cardExpYear', InputArgument::REQUIRED, 'Card expiration year')
            ->addArgument('cardExpMonth', InputArgument::REQUIRED, 'Card expiration month')
            ->addArgument('cardCvv', InputArgument::REQUIRED, 'Card CVV');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $gateway = $input->getArgument('gateway');
        $amount = (int) $input->getArgument('amount');
        $currency = $input->getArgument('currency');
        $cardHolder = $input->getArgument('cardHolder');
        $cardNumber = $input->getArgument('cardNumber');
        $expMonth = (int) $input->getArgument('cardExpMonth');
        $expYear = (int) $input->getArgument('cardExpYear');
        $cvv = $input->getArgument('cardCvv');

        $paymentRequest = PaymentRequest::create(
            $cardHolder,
            $amount,
            $currency,
            $cardNumber,
            $expMonth,
            $expYear,
            $cvv,
            $gateway
        );

        try {
            $paymentResponse = $this->processPaymentUseCase->execute($paymentRequest);

            if (PaymentTransactionStatus::SUCCESSFUL->value === $paymentResponse->status) {
                $io->success('Payment processed successfully!');
            } else {
                $io->error('Payment process error.');
            }

            $io->table(
                ['Field', 'Value'],
                [
                    ['Transaction ID', $paymentResponse->paymentTransactionId],
                    ['Amount', $paymentResponse->amount],
                    ['Currency', $paymentResponse->currency],
                    ['Card BIN', $paymentResponse->cardBin],
                    ['Created At', $paymentResponse->dateOfCreation],
                    ['Gateway', $paymentResponse->gateway],
                    ['Status', $paymentResponse->status],
                    ['External Transaction ID', $paymentResponse->externalTransactionId ?? 'N/A'],
                ]
            );
        } catch (ValidationFailedException $e) {
            $io->error('Validation failed. Please check the input parameters.');
            $io->listing(GetViolationsDetails::forCommandOutput($e->getViolations()));

            return Command::FAILURE;
        } catch (PaymentProcessingException|\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
