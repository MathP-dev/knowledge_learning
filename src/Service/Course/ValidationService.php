<?php

namespace App\Service\Course;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\User;
use App\Repository\LessonValidationRepository;
use App\Service\User\CertificationService;
use Psr\Log\LoggerInterface;

readonly class ValidationService
{
    public function __construct(
        private LessonValidationRepository $validationRepository,
        private CertificationService       $certificationService,
        private LoggerInterface            $logger
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

        $this->checkCourseCompletion($user, $lesson->getCourse());
    }

    private function checkCourseCompletion(User $user, Course $course): void
    {
        $allLessons = $course->getLessons();
        $totalLessons = $allLessons->count();
        $validatedLessons = 0;

        foreach ($allLessons as $lesson) {
            if ($this->validationRepository->findByUserAndLesson($user, $lesson)) {
                $validatedLessons++;
            }
        }

        if ($validatedLessons === $totalLessons && $totalLessons > 0) {
            $this->logger->info('Certification awarded', [
                'user_id' => $user->getId(),
                'course_id' => $course->getId(),
                'course_title' => $course->getTitle()
            ]);

            $this->certificationService->awardCertification($user, $course);
        }
    }

    public function isLessonValidated(User $user, Lesson $lesson): bool
    {
        return $this->validationRepository->findByUserAndLesson($user, $lesson) !== null;
    }
}
