<?php

namespace App\Controller\Webhook;

use App\Service\Payment\WebhookHandlerService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/webhook/stripe', name: 'webhook_stripe', methods: ['POST'])]
class StripeWebhookController extends AbstractController
{
    public function __construct(
        private readonly WebhookHandlerService $webhookHandler,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');

        if (!$signature) {
            $this->logger->error('❌ Missing Stripe signature');
            return new Response('Missing signature', Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = $this->webhookHandler->constructEvent($payload, $signature);
            $this->webhookHandler->handleEvent($event);

            return new Response('Webhook handled', Response::HTTP_OK);

        } catch (\RuntimeException $e) {
            return new Response('Invalid signature', Response::HTTP_UNAUTHORIZED);

        } catch (\Exception $e) {
            $this->logger->error('❌ Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new Response('Webhook processing failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
