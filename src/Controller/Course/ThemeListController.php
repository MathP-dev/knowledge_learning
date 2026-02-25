<?php

namespace App\Controller\Course;

use App\Service\Course\CourseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/themes', name: 'app_theme_list')]
class ThemeListController extends AbstractController
{
    public function __invoke(CourseService $courseService): Response
    {
        $themes = $courseService->getAllThemes();

        return $this->render('course/theme_list.html.twig', [
            'themes' => $themes,
        ]);
    }
}
