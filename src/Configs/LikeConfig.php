<?php

// src/Configs/LikeConfig.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Configs;

use AndyDefer\LaravelLikes\Contracts\Configs\LikeConfigInterface;
use AndyDefer\LaravelLikes\Contracts\LikeTypeInterface;
use AndyDefer\LaravelLikes\Enums\LikeType;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

final class LikeConfig implements LikeConfigInterface
{
    private const CONFIG_KEY = 'likes';

    private const DEFAULT_LIKE_TYPE_ENUM = LikeType::class;

    public function __construct(
        private readonly ConfigRepository $config,
    ) {}

    public function getLikeTypeEnumClass(): string
    {
        $enumClass = $this->config->get(
            self::CONFIG_KEY.'.like_type_enum',
            self::DEFAULT_LIKE_TYPE_ENUM
        );

        if (! class_exists($enumClass)) {
            return self::DEFAULT_LIKE_TYPE_ENUM;
        }

        return $enumClass;
    }

    public function getAvailableLikeTypes(): array
    {
        $enumClass = $this->getLikeTypeEnumClass();

        if (! enum_exists($enumClass)) {
            return LikeType::cases();
        }

        return $enumClass::cases();
    }

    public function isValidType(string $type): bool
    {
        $enumClass = $this->getLikeTypeEnumClass();

        if (! enum_exists($enumClass)) {
            return false;
        }

        foreach ($enumClass::cases() as $case) {
            if ($case->value === $type) {
                return true;
            }
        }

        return false;
    }

    public function getLikeType(string $value): ?LikeTypeInterface
    {
        $enumClass = $this->getLikeTypeEnumClass();

        if (! enum_exists($enumClass)) {
            return null;
        }

        try {
            return $enumClass::tryFrom($value);
        } catch (\Exception) {
            return null;
        }
    }
}
