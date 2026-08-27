<?php

// tests/Fixtures/Enums/TestLikeType.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Tests\Fixtures\Enums;

use AndyDefer\LaravelLikes\Contracts\LikeTypeInterface;

enum TestLikeType: string implements LikeTypeInterface
{
    case FIRE = 'fire';
    case ROCKET = 'rocket';
    case HEART = 'heart';
    case CLAP = 'clap';
    case STAR = 'star';

    public function getEmoji(): string
    {
        return match ($this) {
            self::FIRE => '🔥',
            self::ROCKET => '🚀',
            self::HEART => '💖',
            self::CLAP => '👏',
            self::STAR => '⭐',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::FIRE => 'En feu !',
            self::ROCKET => 'Génial !',
            self::HEART => 'Adorable !',
            self::CLAP => 'Bravo !',
            self::STAR => 'Super !',
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
