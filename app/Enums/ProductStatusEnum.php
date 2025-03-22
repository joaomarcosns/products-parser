<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case DRAFT = 'draft';
    case TRASH = 'trash';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::TRASH => 'Trash',
            self::PUBLISHED => 'Published',
        };
    }
}
