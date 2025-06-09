<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\DTO\PaymentRequest;
use App\Application\UseCase\ProcessPaymentUseCase;
use App\Domain\Exception\PaymentProcessingException;
use App\Presentation\GetViolationsDetails;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[Route('/app/example')]
final class ProcessPaymentController extends AbstractController
{
    public function __construct(
        private readonly ProcessPaymentUseCase $processPaymentUseCase,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/{gateway}', name: 'process_payment', methods: ['POST'], requirements: ['gateway' => 'shift4|aci'])]
    public function processPayment(string $gateway, Request $request): JsonResponse
    {
        try {
            $requestData = json_decode($request->getContent(), true) ?? [];
            $requestData['gateway'] = $gateway;
            $paymentRequest = PaymentRequest::fromArray($requestData);

            $this->logger->info('Payment API request received', [
                'gateway' => $paymentRequest->gateway,
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]);

            $paymentResponse = $this->processPaymentUseCase->execute($paymentRequest);

            return $this->json([
                'transaction_id' => $paymentResponse->paymentTransactionId,
                'amount' => $paymentResponse->amount,
                'currency' => $paymentResponse->currency,
                'card_bin' => $paymentResponse->cardBin,
                'created_at' => $paymentResponse->dateOfCreation,
                'gateway' => $paymentResponse->gateway,
                'status' => $paymentResponse->status,
                'external_transaction_id' => $paymentResponse->externalTransactionId,
            ], Response::HTTP_CREATED);
        } catch (ValidationFailedException $e) {
            $this->logger->warning('Validation failed for payment request', [
                'gateway' => $gateway,
                'errors' => $e->getMessage(),
                'ip' => $request->getClientIp(),
            ]);

            return $this->json([
                'errors' => GetViolationsDetails::forJsonResponse($e->getViolations()),
                'code' => 'VALIDATION_ERROR',
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Invalid argument in payment request', [
                'gateway' => $gateway,
                'errors' => $e->getMessage(),
                'ip' => $request->getClientIp(),
            ]);

            return $this->json([
                'message' => $e->getMessage(),
                'code' => 'PAYMENT_REQUEST_VALIDATION_ERROR',
            ], Response::HTTP_BAD_REQUEST);
        } catch (PaymentProcessingException $e) {
            $this->logger->error('Payment processing failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'ip' => $request->getClientIp(),
            ]);

            return $this->json([
                'message' => $e->getMessage(),
                'code' => 'PAYMENT_PROCESSING_ERROR',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            $this->logger->critical('Unexpected error in payment API', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->getClientIp(),
            ]);

            return $this->json([
                'message' => 'An unexpected error occurred',
                'code' => 'INTERNAL_SERVER_ERROR',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/health', name: 'payment_health', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        return $this->json([
            'status' => 'healthy',
            'timestamp' => date('c'),
        ]);
    }
}
