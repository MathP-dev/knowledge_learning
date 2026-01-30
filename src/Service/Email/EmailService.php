<?php

namespace App\Service\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

readonly class EmailService
{
    public function __construct(
        private MailerInterface       $mailer,
        private ParameterBagInterface $params
    ) {
    }

    public function sendVerificationEmail(User $user): void
    {
        $baseUrl = $this->params->get('site.base_url');
        $verificationUrl = $baseUrl . '/verification/' . $user->getVerificationToken();

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@knowledge-learning.com', 'Knowledge Learning'))
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject('Confirmez votre compte Knowledge Learning')
            ->htmlTemplate('emails/verification.html.twig')
            ->context([
                'user' => $user,
                'verificationUrl' => $verificationUrl,
            ]);

        $this->mailer->send($email);
    }
}
