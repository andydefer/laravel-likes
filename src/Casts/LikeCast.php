<?php

// src/Casts/LikeCast.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Casts;

use AndyDefer\LaravelLikes\Contracts\Configs\LikeConfigInterface;
use AndyDefer\LaravelLikes\Contracts\LikeTypeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Eloquent cast for like types.
 *
 * Converts string/int values from the database to LikeTypeInterface enum instances
 * and vice versa.
 *
 * @implements CastsAttributes<LikeTypeInterface, string>
 */
final class LikeCast implements CastsAttributes
{
    private LikeConfigInterface $config;

    public function __construct()
    {
        $this->config = app(LikeConfigInterface::class);
    }

    /**
     * Transform the attribute from the underlying database values.
     *
     * @param  Model  $model
     * @param  string  $value
     * @param  array<string, mixed>  $attributes
     */
    public function get($model, string $key, $value, array $attributes): ?LikeTypeInterface
    {
        if ($value === null) {
            return null;
        }

        $enumClass = $this->config->getLikeTypeEnumClass();

        if (! enum_exists($enumClass)) {
            return null;
        }

        try {
            return $enumClass::tryFrom($value);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Transform the attribute to its underlying database values.
     *
     * @param  Model  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     *
     * @throws InvalidArgumentException
     */
    public function set($model, string $key, $value, array $attributes): string|int|null
    {
        if ($value === null) {
            return null;
        }

        // Si c'est déjà un LikeTypeInterface, on récupère sa valeur
        if ($value instanceof LikeTypeInterface) {
            return $value->getValue();
        }

        if (is_string($value) || is_int($value)) {
            $enumClass = $this->config->getLikeTypeEnumClass();

            if (enum_exists($enumClass)) {
                try {
                    $enum = $enumClass::tryFrom($value);
                    if ($enum !== null) {
                        return $enum->getValue();
                    }
                } catch (\Exception) {
                    // Fall through to exception
                }
            }
        }

        throw new InvalidArgumentException(
            sprintf('Invalid like type value. Expected instance of %s, or a valid string/int, got %s', LikeTypeInterface::class, get_debug_type($value))
        );
    }
}
