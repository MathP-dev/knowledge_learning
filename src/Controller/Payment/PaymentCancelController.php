<?php

namespace App\Controller\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/paiement/annule', name: 'app_payment_cancel')]
#[IsGranted('ROLE_USER')]
class PaymentCancelController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}