<?php

namespace App\DTO\Api;

final class CourseSummaryDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $price,
    ) {}
}
