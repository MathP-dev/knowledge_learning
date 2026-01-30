<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
class LoginListener
{
    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user->isVerified()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'warning',
                'Votre compte n\'est pas encore activé. Veuillez vérifier votre email.'
            );
        }
    }
}
