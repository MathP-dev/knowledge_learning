<?php

namespace App\State\Api;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\Api\CourseSummaryDto;
use App\DTO\Api\LessonOutputDto;
use App\Entity\Lesson;
use App\Repository\LessonRepository;

final readonly class LessonOutputProvider implements ProviderInterface
{
    public function __construct(
        private LessonRepository $lessonRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): LessonOutputDto|array|null
    {
        $filters = $context['filters'] ?? [];

        if ($operation instanceof CollectionOperationInterface) {
            $page = max(1, (int)($filters['page'] ?? 1));
            $itemsPerPage = min(50, max(1, (int)($filters['itemsPerPage'] ?? 10)));

            $sort = $filters['sort'] ?? 'position';
            $sort = in_array($sort, ['id', 'title', 'price', 'position'], true) ? $sort : 'position';

            $direction = strtoupper((string)($filters['direction'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

            $courseId = isset($filters['course']) ? (int)$filters['course'] : null;
            $minPrice = isset($filters['minPrice']) ? (float)$filters['minPrice'] : null;
            $maxPrice = isset($filters['maxPrice']) ? (float)$filters['maxPrice'] : null;

            $lessons = $this->lessonRepository->searchApiLessons(
                page: $page,
                itemsPerPage: $itemsPerPage,
                sort: $sort,
                direction: $direction,
                courseId: $courseId,
                minPrice: $minPrice,
                maxPrice: $maxPrice
            );

            return array_map(fn (Lesson $lesson) => $this->map($lesson), $lessons);
        }

        $id = isset($uriVariables['id']) ? (int)$uriVariables['id'] : null;
        if (!$id) {
            return null;
        }

        $lesson = $this->lessonRepository->find($id);
        if (!$lesson instanceof Lesson) {
            return null;
        }

        return $this->map($lesson);
    }

    private function map(Lesson $lesson): LessonOutputDto
    {
        $course = $lesson->getCourse();

        return new LessonOutputDto(
            id: (int)$lesson->getId(),
            title: (string)$lesson->getTitle(),
            price: (string)$lesson->getPrice(),
            course: new CourseSummaryDto(
                id: (int)$course?->getId(),
                title: (string)$course?->getTitle(),
                description: (string)$course?->getDescription(),
                price: (string)$course?->getPrice(),
            )
        );
    }
}

