<?php

namespace App\Service\Course;

use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\LessonRepository;

readonly class LessonService
{
    public function __construct(
        private LessonRepository $lessonRepository
    ) {
    }

    public function getLessonBySlug(string $slug): ?Lesson
    {
        return $this->lessonRepository->findBySlug($slug);
    }

    public function canUserAccessLesson(User $user, Lesson $lesson): bool
    {
        return $user->hasAccessToLesson($lesson);
    }
}
