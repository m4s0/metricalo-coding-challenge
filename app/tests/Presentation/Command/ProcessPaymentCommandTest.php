<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Command;

use App\Application\DTO\PaymentResponse;
use App\Application\UseCase\ProcessPaymentUseCase;
use App\Domain\ValueObject\PaymentGateway;
use App\Domain\ValueObject\PaymentTransactionStatus;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Uuid;

/**
 * @group Integration
 */
class ProcessPaymentCommandTest extends KernelTestCase
{
    public function testExecuteSuccessfulPayment(): void
    {
        $kernel = self::bootKernel();

        $externalTransactionId = Uuid::v4()->toRfc4122();
        $now = new \DateTime();

        $processPaymentUseCaseMocked = $this->createMock(ProcessPaymentUseCase::class);
        $processPaymentUseCaseMocked->expects(self::once())
            ->method('execute')
            ->willReturn(PaymentResponse::create(
                '27e7c525-0179-4dd0-a18f-977647146331',
                2500,
                'USD',
                '424242',
                $now->format('Y-m-d H:i:s'),
                PaymentGateway::SHIFT4->value,
                PaymentTransactionStatus::SUCCESSFUL->value,
                $externalTransactionId,
            ));

        static::getContainer()->set(ProcessPaymentUseCase::class, $processPaymentUseCaseMocked);

        $application = new Application($kernel);

        $command = $application->find('app:example');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'amount' => '100.00',
            'currency' => 'USD',
            'gateway' => PaymentGateway::SHIFT4->value,
            'cardHolder' => 'Jane Jones',
            'cardNumber' => '4242424242424242',
            'cardExpYear' => '2029',
            'cardExpMonth' => '12',
            'cardCvv' => '123',
        ]);

        $this->assertStringContainsString(
            'Payment processed successfully!',
            $commandTester->getDisplay()
        );
        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithInvalidAmount(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);

        $command = $application->find('app:example');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'amount' => '-50.00',
            'currency' => 'USD',
            'gateway' => PaymentGateway::SHIFT4->value,
            'cardHolder' => 'Jane Jones',
            'cardNumber' => '4242424242424242',
            'cardExpYear' => '2029',
            'cardExpMonth' => '12',
            'cardCvv' => '123',
        ]);

        $this->assertStringContainsString(
            '"amount": This value should be positive.',
            $commandTester->getDisplay()
        );
        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithUnsupportedCurrency(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);

        $command = $application->find('app:example');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'amount' => '100.00',
            'currency' => 'XXX',
            'gateway' => PaymentGateway::SHIFT4->value,
            'cardHolder' => 'Jane Jones',
            'cardNumber' => '4242424242424242',
            'cardExpYear' => '2029',
            'cardExpMonth' => '12',
            'cardCvv' => '123',
        ]);

        $this->assertStringContainsString(
            '"currency": This value is not a valid currency.',
            $commandTester->getDisplay()
        );
        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }
}
