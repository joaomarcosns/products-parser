<?php

declare(strict_types=1);

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

    public function value(): string
    {
        return match ($this) {
            self::DRAFT => 'draft',
            self::TRASH => 'trash',
            self::PUBLISHED => 'published',
        };
    }
}
