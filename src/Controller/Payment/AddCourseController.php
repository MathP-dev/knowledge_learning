<?php

namespace App\Controller\Payment;

use App\Entity\Course;
use App\Service\Payment\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart/add/course/{id}', name: 'app_cart_add_course', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class AddCourseController extends AbstractController
{
    public function __invoke(Request $request, int $id, CartService $cartService, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('cart_add_course' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user = $this->getUser();

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez activer votre compte avant d\'ajouter des éléments au panier.');
            return $this->redirectToRoute('app_home');
        }

        $cart = $cartService->getOrCreateCart($user);
        $course = $em->getRepository(Course::class)->find($id);

        if (!$course) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        try {
            $cartService->addCourse($cart, $course);
            $this->addFlash('success', 'Formation ajoutée au panier !');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_cart_index');
    }
}
