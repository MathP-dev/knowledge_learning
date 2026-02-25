<?php

namespace App\Controller\Course;

use App\Service\Course\CourseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/theme/{slug}', name: 'app_theme_show')]
class ThemeShowController extends AbstractController
{
    public function __invoke(string $slug, CourseService $courseService): Response
    {
        $theme = $courseService->getThemeBySlug($slug);

        if (!$theme) {
            throw $this->createNotFoundException('Ce thème n\'existe pas.');
        }

        $courses = $courseService->getCoursesByTheme($theme);

        return $this->render('course/theme_show.html.twig', [
            'theme' => $theme,
            'courses' => $courses,
        ]);
    }
}
