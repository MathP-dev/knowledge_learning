<?php

namespace App\Service\Course;

use App\Entity\Course;
use App\Entity\Theme;
use App\Repository\CourseRepository;
use App\Repository\ThemeRepository;

class CourseService
{
    public function __construct(
        private CourseRepository $courseRepository,
        private ThemeRepository $themeRepository
    ) {
    }

    public function getAllThemes(): array
    {
        return $this->themeRepository->findAllThemes();
    }

    public function getThemeBySlug(string $slug): ?Theme
    {
        return $this->themeRepository->findBySlug($slug);
    }

    public function getCourseBySlug(string $slug): ?Course
    {
        return $this->courseRepository->findBySlug($slug);
    }

    public function getCoursesByTheme(Theme $theme): array
    {
        return $this->courseRepository->findByTheme($theme);
    }

    public function getAllCourses(): array
    {
        return $this->courseRepository->findAllCourses();
    }
}