<?php

namespace App\Controller\Course;

use App\Service\Course\LessonService;
use App\Service\Course\ValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/lesson/{slug}/valider', name: 'app_lesson_validate')]
#[IsGranted('ROLE_USER')]
class ValidateLessonController extends AbstractController
{
    public function __construct(
        private LessonService $lessonService,
        private ValidationService $validationService
    ) {
    }

    public function __invoke(string $slug): Response
    {
        $lesson = $this->lessonService->getLessonBySlug($slug);

        if (!$lesson) {
            throw $this->createNotFoundException('Cette leçon n\'existe pas.');
        }

        $user = $this->getUser();

        if (!$this->lessonService->canUserAccessLesson($user, $lesson)) {
            $this->addFlash('error', 'Vous devez d\'abord acheter cette leçon pour la valider.');
            return $this->redirectToRoute('app_lesson_show', ['slug' => $slug]);
        }

        $this->validationService->validateLesson($user, $lesson);
        $this->addFlash('success', 'Leçon validée avec succès !');

        return $this->redirectToRoute('app_lesson_show', ['slug' => $slug]);
    }
}
