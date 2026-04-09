<?php

namespace App\DTO\Api;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\Api\LessonOutputProvider;

#[ApiResource(
    shortName: 'Lesson',
    operations: [
        new Get(uriTemplate: '/lessons/{id}', provider: LessonOutputProvider::class),
        new GetCollection(uriTemplate: '/lessons', provider: LessonOutputProvider::class),
    ],
    formats: ['json' => ['application/json']],
)]
final class LessonOutputDto
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public int $id,
        public string $title,
        public string $price,
        public CourseSummaryDto $course,
    ) {}
}

