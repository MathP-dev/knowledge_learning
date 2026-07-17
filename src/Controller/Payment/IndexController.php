<?php

namespace App\Controller\Payment;

use App\Entity\CartItemType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Service\Payment\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart', name: 'app_cart_index', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class IndexController extends AbstractController
{
    public function __invoke(
        CartService $cartService,
        LessonRepository $lessonRepository,
        CourseRepository $courseRepository
    ): Response {

        $user = $this->getUser();
        $cart = $cartService->getOrCreateCart($user);

        $items = [];
        foreach ($cart->getItems() as $item) {
            $itemId = $item->getId();

            if ($item->getType() === CartItemType::LESSON) {
                $lesson = $lessonRepository->find($item->getLessonId());
                $label = $lesson?->getTitle() ?? 'Leçon #' . $item->getLessonId();
                $typeLabel = 'Leçon';
                $badgeClass = 'primary';
            } else {
                $course = $courseRepository->find($item->getCourseId());
                $label = $course?->getTitle() ?? 'Formation #' . $item->getCourseId();
                $typeLabel = 'Formation';
                $badgeClass = 'success';
            }

            $items[] = [
                'id' => $itemId,
                'label' => $label,
                'typeLabel' => $typeLabel,
                'badgeClass' => $badgeClass,
                'formattedPrice' => number_format($item->getPrice() / 100, 2, ',', ' ') . ' €',
            ];
        }

        $total = $cart->getTotalAmount();

        return $this->render('payment/cart.html.twig', [
            'cart' => $cart,
            'items' => $items,
            'total' => $total,
            'totalFormatted' => number_format($total / 100, 2, ',', ' ') . ' €',
        ]);
    }

}
