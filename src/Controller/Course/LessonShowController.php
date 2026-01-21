<?php

namespace App\Controller\Course;

use App\Service\Course\LessonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lecon/{slug}', name: 'app_lesson_show')]
class LessonShowController extends AbstractController
{
    public function __construct(
        private LessonService $lessonService
    ) {
    }

    public function __invoke(string $slug): Response
    {
        $lesson = $this->lessonService->getLessonBySlug($slug);

        if (!$lesson) {
            throw $this->createNotFoundException('Cette leçon n\'existe pas.');
        }

        $user = $this->getUser();
        $hasAccess = $user && $this->lessonService->canUserAccessLesson($user, $lesson);

        return $this->render('course/lesson_show.html.twig', [
            'lesson' => $lesson,
            'hasAccess' => $hasAccess,
        ]);
    }
}