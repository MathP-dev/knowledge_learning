<?php

namespace App\Controller\Course;

use App\Service\Course\LessonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lesson/{slug}', name: 'app_lesson_show')]
class LessonShowController extends AbstractController
{
    public function __invoke(string $slug, LessonService $lessonService): Response
    {
        $lesson = $lessonService->getLessonBySlug($slug);

        if (!$lesson) {
            throw $this->createNotFoundException('Cette leçon n\'existe pas.');
        }

        $user = $this->getUser();
        $hasAccess = $user && $lessonService->canUserAccessLesson($user, $lesson);

        return $this->render('course/lesson_show.html.twig', [
            'lesson' => $lesson,
            'hasAccess' => $hasAccess,
        ]);
    }
}
