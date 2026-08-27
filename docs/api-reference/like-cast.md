# LikeCast - Référence Technique

## Description

Convertit les valeurs de type `string` ou `int` de la base de données en instances d'enum implémentant `LikeTypeInterface`, et inversement.

## Hiérarchie

```
CastsAttributes<LikeTypeInterface, string>
    └── LikeCast
```

## Rôle principal

Le cast `LikeCast` est utilisé dans le modèle `Like` pour transformer automatiquement la colonne `type` entre sa représentation en base de données (string) et son objet métier (enum implémentant `LikeTypeInterface`). Il permet une flexibilité totale en laissant l'utilisateur définir son propre enum de réactions via la configuration.

## API / Méthodes publiques

### `get($model, string $key, $value, array $attributes): ?LikeTypeInterface`

Transforme la valeur de la base de données en instance de `LikeTypeInterface`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$model` | `Model` | L'instance du modèle Eloquent |
| `$key` | `string` | Le nom de l'attribut (ex: `type`) |
| `$value` | `string` | La valeur brute de la base de données |
| `$attributes` | `array<string, mixed>` | Tous les attributs du modèle |

**Retourne :** `LikeTypeInterface|null` - L'instance de l'enum correspondante, ou `null` si la valeur est `null`

**Exceptions :** Aucune (les erreurs sont capturées et retournent `null`)

**Exemple :**
```php
$like = Like::find(1);
$type = $like->type; // LikeType::LOVE
echo $type->getEmoji(); // ❤️
```

---

### `set($model, string $key, $value, array $attributes): string|int|null`

Transforme la valeur de l'enum en valeur de base de données.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$model` | `Model` | L'instance du modèle Eloquent |
| `$key` | `string` | Le nom de l'attribut (ex: `type`) |
| `$value` | `mixed` | La valeur à stocker (enum ou string/int) |
| `$attributes` | `array<string, mixed>` | Tous les attributs du modèle |

**Retourne :** `string|int|null` - La valeur à stocker en base de données

**Exceptions :** `InvalidArgumentException` si la valeur n'est pas valide

**Exemple :**
```php
$like = Like::find(1);
$like->type = LikeType::LOVE; // Converti en 'love' pour la base de données
$like->save();
```

## Cas d'utilisation

### Cas 1 : Utilisation avec l'enum par défaut `LikeType`

**Problème :** Utiliser les 6 réactions par défaut (like, love, haha, wow, sad, angry).

**Solution :** Aucune configuration nécessaire, le cast utilise `LikeType` par défaut.

```php
$like = Like::create([
    'liker_type' => User::class,
    'liker_id' => $user->id,
    'likeable_type' => Post::class,
    'likeable_id' => $post->id,
    'type' => LikeType::LOVE,
]);

// Récupération
$like = Like::find(1);
$type = $like->type; // LikeType::LOVE
echo $type->getEmoji(); // ❤️
echo $type->getLabel(); // J'adore
```

### Cas 2 : Extension avec un enum personnalisé

**Problème :** Ajouter des réactions personnalisées (fire, rocket, heart, clap, star).

**Solution :** Créer un enum implémentant `LikeTypeInterface` et le configurer.

```php
// App\Enums\CustomLikeType.php
enum CustomLikeType: string implements LikeTypeInterface
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
```

```php
// config/likes.php
return [
    'like_type_enum' => App\Enums\CustomLikeType::class,
];
```

```php
// Utilisation
$like = Like::create([
    'liker_type' => User::class,
    'liker_id' => $user->id,
    'likeable_type' => Post::class,
    'likeable_id' => $post->id,
    'type' => CustomLikeType::FIRE,
]);

$type = $like->type; // CustomLikeType::FIRE
echo $type->getEmoji(); // 🔥
echo $type->getLabel(); // En feu !
```

### Cas 3 : Utilisation avec des valeurs mixtes (string/int)

**Problème :** L'utilisateur peut passer une string ou un int directement.

**Solution :** Le cast tente de convertir automatiquement via `tryFrom()`.

```php
// Valeur passée directement en string
$like = Like::create([
    'liker_type' => User::class,
    'liker_id' => $user->id,
    'likeable_type' => Post::class,
    'likeable_id' => $post->id,
    'type' => 'love', // ✅ Converti automatiquement en LikeType::LOVE
]);

// Valeur passée directement en int
$like = Like::create([
    'liker_type' => User::class,
    'liker_id' => $user->id,
    'likeable_type' => Post::class,
    'likeable_id' => $post->id,
    'type' => 1, // ✅ Converti automatiquement si l'enum supporte les ints
]);
```

## Flux d'exécution

```
get($value)
    ↓
$enumClass = $this->config->getLikeTypeEnumClass()
    ↓
$enumClass::tryFrom($value)
    ↓
? LikeTypeInterface : null
```

```
set($value)
    ↓
$value instanceof LikeTypeInterface ?
    ├── OUI → $value->getValue()
    └── NON → is_string($value) ou is_int($value) ?
        ├── OUI → $enumClass::tryFrom($value)
        │   ├── NOT NULL → $enum->getValue()
        │   └── NULL → Exception
        └── NON → Exception
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Valeur non convertible | `InvalidArgumentException` | `Invalid like type value. Expected instance of LikeTypeInterface, or a valid string/int, got {type}` |
| Enum non trouvé | `InvalidArgumentException` | (même message) |

## Intégration

### Avec LikeConfig

`LikeCast` utilise `LikeConfigInterface` pour déterminer la classe enum à utiliser.

```php
$enumClass = $this->config->getLikeTypeEnumClass();
```

### Avec LikeService

Le service utilise le cast automatiquement via le modèle Eloquent.

```php
$like = $likeService->toggle($user, $post, LikeType::LIKE);
$type = $like->type; // ✅ Déjà converti en enum
```

### Avec l'interface LikeTypeInterface

Tous les enums doivent implémenter cette interface.

```php
interface LikeTypeInterface
{
    public function getEmoji(): string;
    public function getLabel(): string;
    public function getValue(): string|int;
    public static function cases(): array;
    public static function tryFrom(string|int $value): ?static;
}
```

## Performance

- **O(1)** : Conversion directe sans boucle ni allocation
- **Sans cache** : Les enums sont résolus à chaque accès (PHP 8.1+ optimisé)
- **Lazy loading** : Le cast ne s'exécute que lorsque l'attribut est accédé
- **Optimisation** : Utilise `tryFrom()` natif de PHP 8.1+ pour les enums

## Compatibilité

| Version PHP | Support |
|-------------|---------|
| PHP 8.1+ | ✅ Complet (enums natifs) |
| PHP 8.0 | ❌ Non supporté (pas d'enums) |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\LaravelLikes\Models\Like;
use AndyDefer\LaravelLikes\Enums\LikeType;
use App\Models\User;
use App\Models\Post;

// 1. Création avec enum
$like = Like::create([
    'liker_type' => User::class,
    'liker_id' => 1,
    'likeable_type' => Post::class,
    'likeable_id' => 1,
    'type' => LikeType::LOVE,
]);

// 2. Récupération - automatiquement converti
$like = Like::find(1);
$type = $like->type; // LikeType::LOVE

// 3. Utilisation des méthodes
echo $type->getEmoji(); // ❤️
echo $type->getLabel(); // J'adore

// 4. Mise à jour avec enum
$like->type = LikeType::HAHA;
$like->save();

// 5. Mise à jour avec string
$like->type = 'wow';
$like->save();

// 6. Filtrage avec enum
$loves = Like::where('type', LikeType::LOVE->getValue())->get();

// 7. Avec enum personnalisé
// config/likes.php
return [
    'like_type_enum' => App\Enums\CustomLikeType::class,
];

$like = Like::create([
    'liker_type' => User::class,
    'liker_id' => 1,
    'likeable_type' => Post::class,
    'likeable_id' => 1,
    'type' => CustomLikeType::FIRE,
]);

$type = $like->type; // CustomLikeType::FIRE
echo $type->getEmoji(); // 🔥
```

## Voir aussi

- `LikeTypeInterface` - Interface à implémenter pour les enums personnalisés
- `LikeConfig` - Configuration du type d'enum
- `LikeService` - Service principal de gestion des réactions
- `Like` - Modèle Eloquent utilisant le cast