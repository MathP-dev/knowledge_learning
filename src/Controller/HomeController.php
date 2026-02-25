<?php

namespace App\Controller;

use App\Service\Course\CourseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_home')]
class HomeController extends AbstractController
{
    public function __invoke(CourseService $courseService): Response
    {
        $themes = $courseService->getAllThemes();

        return $this->render('home/index.html.twig', [
            'themes' => $themes,
        ]);
    }
}
