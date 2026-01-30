<?php

namespace App\Controller\Course;

use App\Service\Course\CourseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cursus/{slug}', name: 'app_course_show')]
class CourseShowController extends AbstractController
{
    public function __construct(
        private readonly CourseService $courseService
    ) {
    }

    public function __invoke(string $slug): Response
    {
        $course = $this->courseService->getCourseBySlug($slug);

        if (!$course) {
            throw $this->createNotFoundException('Ce cursus n\'existe pas.');
        }

        return $this->render('course/course_show.html.twig', [
            'course' => $course,
        ]);
    }
}
