<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Enums;

enum LikeType: string
{
    case LIKE = 'like';
    case LOVE = 'love';
    case HAHA = 'haha';
    case WOW = 'wow';
    case SAD = 'sad';
    case ANGRY = 'angry';

    public function getEmoji(): string
    {
        return match ($this) {
            self::LIKE => '👍',
            self::LOVE => '❤️',
            self::HAHA => '😂',
            self::WOW => '😮',
            self::SAD => '😢',
            self::ANGRY => '😡',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::LIKE => 'J\'aime',
            self::LOVE => 'J\'adore',
            self::HAHA => 'Haha',
            self::WOW => 'Wow',
            self::SAD => 'Triste',
            self::ANGRY => 'En colère',
        };
    }
}
