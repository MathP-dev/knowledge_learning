<?php

namespace App\Controller\Payment;

use App\Entity\Lesson;
use App\Service\Payment\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart/add/lesson/{id}', name: 'app_cart_add_lesson', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class AddLessonController extends AbstractController
{
    public function __invoke(Request $request, int $id, CartService $cartService, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('cart_add_lesson' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user = $this->getUser();

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez activer votre compte avant d\'ajouter des éléments au panier.');
            return $this->redirectToRoute('app_home');
        }

        $cart = $cartService->getOrCreateCart($user);
        $lesson = $em->getRepository(Lesson::class)->find($id);

        if (!$lesson) {
            throw $this->createNotFoundException('Leçon non trouvée');
        }

        try {
            $cartService->addLesson($cart, $lesson);
            $this->addFlash('success', 'Leçon ajoutée au panier !');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_cart_index');
    }
}
