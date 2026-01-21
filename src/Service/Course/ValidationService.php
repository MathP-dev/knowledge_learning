<?php

namespace App\Service\Course;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\LessonValidationRepository;
use App\Service\User\CertificationService;

class ValidationService
{
    public function __construct(
        private LessonValidationRepository $validationRepository,
        private CertificationService $certificationService
    ) {
    }

    public function validateLesson(User $user, Lesson $lesson): void
    {
        $existingValidation = $this->validationRepository->findByUserAndLesson($user, $lesson);

        if ($existingValidation) {
            return;
        }

        $validation = new LessonValidation();
        $validation->setUser($user);
        $validation->setLesson($lesson);

        $this->validationRepository->save($validation, true);

        // Vérifier si toutes les leçons du cursus sont validées
        $this->checkCourseCompletion($user, $lesson->getCourse());
    }

    private function checkCourseCompletion(User $user, Course $course): void
    {
        $allLessons = $course->getLessons();
        $validatedLessons = 0;

        foreach ($allLessons as $lesson) {
            if ($this->validationRepository->findByUserAndLesson($user, $lesson)) {
                $validatedLessons++;
            }
        }

        // Si toutes les leçons sont validées
        if ($validatedLessons === count($allLessons)) {
            $this->checkThemeCompletion($user, $course->getTheme());
        }
    }

    private function checkThemeCompletion(User $user, Theme $theme): void
    {
        $allCourses = $theme->getCourses();
        $completedCourses = 0;

        foreach ($allCourses as $course) {
            $allLessons = $course->getLessons();
            $validatedLessons = 0;

            foreach ($allLessons as $lesson) {
                if ($this->validationRepository->findByUserAndLesson($user, $lesson)) {
                    $validatedLessons++;
                }
            }

            if ($validatedLessons === count($allLessons)) {
                $completedCourses++;
            }
        }

        // Si tous les cursus du thème sont complétés, décerner la certification
        if ($completedCourses === count($allCourses)) {
            $this->certificationService->awardCertification($user, $theme);
        }
    }

    public function isLessonValidated(User $user, Lesson $lesson): bool
    {
        return $this->validationRepository->findByUserAndLesson($user, $lesson) !== null;
    }
}