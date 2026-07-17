<?php

namespace App\Entity;

enum CartItemType: string
{
    case LESSON = 'lesson';
    case COURSE = 'course';
}
