# Laravel Likes

> Système de réactions polymorphiques extensible pour applications Laravel

Un package Laravel complet pour gérer des réactions polymorphiques (likes, loves, haha, wow, sad, angry) avec un système de toggle intelligent, des enums extensibles, des DTOs et des Value Objects.

---

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Le cast Eloquent](#le-cast-eloquent)
- [Extensibilité](#extensibilité)
- [Utilisation](#utilisation)
  - [Toggle une réaction](#toggle-une-réaction)
  - [Ajouter un like](#ajouter-un-like)
  - [Supprimer un like](#supprimer-un-like)
  - [Vérifier une réaction](#vérifier-une-réaction)
  - [Compter les réactions](#compter-les-réactions)
  - [Récupérer les réactions](#récupérer-les-réactions)
  - [Filtrer par date](#filtrer-par-date)
- [Types de réactions](#types-de-réactions)
- [Référence de l'API](#référence-de-lapi)
- [Value Objects](#value-objects)
- [Structure de la base de données](#structure-de-la-base-de-données)
- [Tests](#tests)
- [Contribuer](#contribuer)
- [Licence](#licence)

---

## ✨ Fonctionnalités

- ✅ **Double polymorphisme** - Réagissez à n'importe quel modèle avec n'importe quel utilisateur
- ✅ **6 types de réactions par défaut** - LIKE, LOVE, HAHA, WOW, SAD, ANGRY avec emojis
- ✅ **Extensible** - Ajoutez vos propres types de réactions via l'interface `LikeTypeInterface`
- ✅ **Toggle intelligent** - Changez de réaction en un seul appel
- ✅ **Filtrage temporel** - Récupérez les réactions après une date donnée
- ✅ **Pattern Repository** - Séparation propre de la logique d'accès aux données
- ✅ **Support des DTOs** - Objets de transfert de données typés
- ✅ **Value Objects** - DateTime, Métadonnées
- ✅ **Support des métadonnées** - Stockez des données supplémentaires au format JSON
- ✅ **Suppression douce** - Suppression sécurisée avec possibilité de restauration
- ✅ **Filtrage avancé** - Filtrez par type, par auteur, par objet
- ✅ **Tests complets** - Couverture complète des tests d'intégration

---

## 🚀 Prérequis

- PHP 8.2 ou supérieur
- Laravel 12.0, 13.0, 14.0 ou 15.0

---

## 📦 Installation

Installez le package via Composer :

```bash
composer require andydefer/laravel-likes
```

### Publier les migrations

```bash
php artisan vendor:publish --tag=likes-migrations
```

### Exécuter les migrations

```bash
php artisan migrate
```

### Publier la configuration (optionnel)

```bash
php artisan vendor:publish --tag=likes-config
```

---

## ⚙️ Configuration

### Fichier de configuration

```php
// config/likes.php

return [
    /*
    |--------------------------------------------------------------------------
    | Like Type Enum
    |--------------------------------------------------------------------------
    |
    | The FQCN of the enum that defines the available like types.
    | Must implement LikeTypeInterface.
    |
    | Default: AndyDefer\LaravelLikes\Enums\LikeType::class
    |
    */
    'like_type_enum' => env('LIKES_TYPE_ENUM', AndyDefer\LaravelLikes\Enums\LikeType::class),
];
```

### Configuration des enums dans Laravel Repository

Le package utilise le `EnumCast` du package **Laravel Repository** pour la conversion automatique des enums. Vous devez configurer le cast dans `config/repository.php` :

```php
// config/repository.php

return [
    /*
    |--------------------------------------------------------------------------
    | Enum Casts
    |--------------------------------------------------------------------------
    |
    | Define enum casts for specific tables and columns.
    | Each entry maps a table name and column to an enum class.
    |
    | The enum class must implement EnumerableInterface.
    |
    */
    'enum_casts' => [
        'likes' => [
            'type' => AndyDefer\LaravelLikes\Enums\LikeType::class,
        ],
    ],
];
```

### Variables d'environnement

```env
# .env
LIKES_TYPE_ENUM=App\\Enums\\CustomLikeType
```

---

## 🔧 Le cast Eloquent

Le package utilise le `EnumCast` du package **Laravel Repository** pour convertir automatiquement la colonne `type` entre sa représentation en base de données et votre enum.

### Comment ça fonctionne

1. **Lecture (get)** : Le cast récupère la valeur string de la base de données et la convertit en instance de l'enum configuré via `tryFrom()`.
2. **Écriture (set)** : Le cast accepte soit une instance de `LikeTypeInterface`, soit une string/int, et le convertit en valeur de base de données.

### Configuration requise

Pour que le cast fonctionne, vous devez :

1. **Configurer le cast dans `config/repository.php`** :

```php
'enum_casts' => [
    'likes' => [
        'type' => AndyDefer\LaravelLikes\Enums\LikeType::class,
    ],
],
```

2. **Utiliser `EnumCast` dans le modèle `Like`** :

```php
use AndyDefer\Repository\Casts\EnumCast;

class Like extends Model
{
    protected $casts = [
        'type' => EnumCast::class,
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
```

### Interface `EnumerableInterface`

Tous les enums utilisés avec le cast doivent implémenter `EnumerableInterface` du package Laravel Repository :

```php
use AndyDefer\Repository\Contracts\EnumerableInterface;

enum LikeType: string implements EnumerableInterface
{
    // ...
}
```

---

## 🔧 Extensibilité

### Ajouter des types de réactions personnalisés

Le package est conçu pour être extensible. Vous pouvez ajouter vos propres types de réactions en créant un enum qui implémente `LikeTypeInterface`.

#### 1. Créer votre enum

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use AndyDefer\LaravelLikes\Contracts\LikeTypeInterface;

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

#### 2. Configurer l'enum personnalisé

```php
// config/likes.php
return [
    'like_type_enum' => App\Enums\CustomLikeType::class,
];
```

#### 3. Mettre à jour la configuration du repository

```php
// config/repository.php
'enum_casts' => [
    'likes' => [
        'type' => App\Enums\CustomLikeType::class,
    ],
],
```

#### 4. Utiliser vos nouvelles réactions

```php
use App\Enums\CustomLikeType;

$liked = $likeService->toggle($user, $post, CustomLikeType::FIRE);
// 🔥 - En feu !

$liked = $likeService->toggle($user, $post, CustomLikeType::STAR);
// ⭐ - Super !
```

---

## 📖 Utilisation

### Toggle une réaction

La méthode `toggle()` est la plus polyvalente. Elle permet de :
- Ajouter une réaction si elle n'existe pas
- Changer de type de réaction si elle existe déjà
- Supprimer la réaction si le même type est utilisé

```php
use AndyDefer\LaravelLikes\Services\LikeService;
use AndyDefer\LaravelLikes\Enums\LikeType;

class PostController extends Controller
{
    public function react(LikeService $likeService, Post $post)
    {
        $user = auth()->user();

        // Toggle un like (👍)
        $liked = $likeService->toggle($user, $post, LikeType::LIKE);

        // Toggle un love (❤️)
        $loved = $likeService->toggle($user, $post, LikeType::LOVE);

        // Toggle un haha (😂)
        $haha = $likeService->toggle($user, $post, LikeType::HAHA);

        return response()->json([
            'reacted' => $liked,
            'type' => $liked ? LikeType::LIKE->getValue() : null,
            'emoji' => $liked ? LikeType::LIKE->getEmoji() : null,
        ]);
    }
}
```

### Ajouter un like

```php
// Ajoute un like (👍) - Lève une exception si déjà liké
$likeService->like($user, $post);
```

### Supprimer un like

```php
// Supprime un like - Lève une exception si non liké
$likeService->unlike($user, $post);
```

### Vérifier une réaction

```php
// Vérifier si l'utilisateur a réagi
$hasLiked = $likeService->hasLiked($user, $post);
```

### Compter les réactions

```php
// Compter toutes les réactions d'un objet
$total = $likeService->countLikes($post);

// Compter par type
$likes = $likeService->countLikesByType($post, LikeType::LIKE);
$loves = $likeService->countLikesByType($post, LikeType::LOVE);
$hahas = $likeService->countLikesByType($post, LikeType::HAHA);

// Avec enum personnalisé
$fires = $likeService->countLikesByType($post, CustomLikeType::FIRE);
```

### Récupérer les réactions

```php
// Récupérer tous les likeurs d'un objet
$likers = $likeService->getLikers($post);

// Récupérer les likeurs par type
$likersByType = $likeService->getLikersByType($post, LikeType::LOVE);

// Récupérer toutes les réactions d'un utilisateur
$userLikes = $likeService->getLikerLikes($user);

// Récupérer les réactions d'un utilisateur par type
$userLoves = $likeService->getLikerLikesByType($user, LikeType::LOVE);
```

### Filtrer par date

```php
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

$date = DateTimeVO::from('2024-01-01 00:00:00');

// Récupérer toutes les réactions mises à jour après une date
$recentLikes = $likeService->getLikesUpdatedAfter($date);

// Récupérer les réactions d'un utilisateur après une date
$userRecentLikes = $likeService->getLikerLikesUpdatedAfter($user, $date);

// Récupérer les réactions d'un objet après une date
$postRecentLikes = $likeService->getLikesForLikeableUpdatedAfter($post, $date);
```

---

## 🏷️ Types de réactions par défaut

| Type | Valeur | Emoji | Label |
|------|--------|-------|-------|
| `LikeType::LIKE` | `'like'` | 👍 | J'aime |
| `LikeType::LOVE` | `'love'` | ❤️ | J'adore |
| `LikeType::HAHA` | `'haha'` | 😂 | Haha |
| `LikeType::WOW` | `'wow'` | 😮 | Wow |
| `LikeType::SAD` | `'sad'` | 😢 | Triste |
| `LikeType::ANGRY` | `'angry'` | 😡 | En colère |

### Utilisation des émojis

```php
use AndyDefer\LaravelLikes\Enums\LikeType;

$type = LikeType::LOVE;
echo $type->getEmoji();  // ❤️
echo $type->getLabel();  // J'adore
echo $type->getValue();  // 'love'
```

---

## 📚 Référence de l'API

### LikeService

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `toggle(Model $liker, Model $likeable, LikeTypeInterface $type)` | Toggle une réaction (ajoute/change/supprime) | `bool` |
| `like(Model $liker, Model $likeable)` | Ajoute un like (👍) | `void` |
| `unlike(Model $liker, Model $likeable)` | Supprime un like | `void` |
| `hasLiked(Model $liker, Model $likeable)` | Vérifie si l'utilisateur a réagi | `bool` |
| `countLikes(Model $likeable)` | Compte toutes les réactions | `int` |
| `countLikesByType(Model $likeable, LikeTypeInterface $type)` | Compte les réactions par type | `int` |
| `getLikers(Model $likeable)` | Récupère tous les likeurs | `Collection` |
| `getLikersByType(Model $likeable, LikeTypeInterface $type)` | Récupère les likeurs par type | `Collection` |
| `getLikerLikes(Model $liker)` | Récupère les réactions d'un utilisateur | `Collection` |
| `getLikerLikesByType(Model $liker, LikeTypeInterface $type)` | Récupère les réactions d'un utilisateur par type | `Collection` |
| `getLikesUpdatedAfter(DateTimeVO $date)` | Récupère les réactions après une date | `Collection` |
| `getLikerLikesUpdatedAfter(Model $liker, DateTimeVO $date)` | Récupère les réactions d'un utilisateur après une date | `Collection` |
| `getLikesForLikeableUpdatedAfter(Model $likeable, DateTimeVO $date)` | Récupère les réactions d'un objet après une date | `Collection` |

---

## 🎯 Value Objects

Le package supporte les Value Objects suivants :

| Value Object | Description | Exemple |
|--------------|-------------|---------|
| `DateTimeVO` | Date/heure | `DateTimeVO::from('2024-01-01 12:00:00')` |
| `StrictDataObject` | Métadonnées typées | `StrictDataObject::from(['key' => 'value'])` |

### Accesseurs dans le modèle Like

```php
$like = Like::find(1);

// ✅ Accès via les accesseurs Eloquent (propriétés directement)
$createdAt = $like->created_at;       // Carbon
$updatedAt = $like->updated_at;       // Carbon
$deletedAt = $like->deleted_at;       // Carbon
$metadata = $like->metadata;          // StrictDataObject|null
$type = $like->type;                  // LikeTypeInterface

// ✅ Relations
$liker = $like->liker;          // Auteur (User, Admin, etc.)
$likeable = $like->likeable;    // Objet liké (Post, Article, etc.)
```

---

## 📝 Structure de la base de données

```sql
CREATE TABLE likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    liker_type VARCHAR(255) NOT NULL,    -- Type de l'auteur
    liker_id BIGINT UNSIGNED NOT NULL,   -- ID de l'auteur
    likeable_type VARCHAR(255) NOT NULL, -- Type de l'objet liké
    likeable_id BIGINT UNSIGNED NOT NULL,-- ID de l'objet liké
    type VARCHAR(20) DEFAULT 'like',     -- like, love, haha, wow, sad, angry
    metadata JSON NULL,                  -- Métadonnées
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    UNIQUE INDEX idx_unique_like (liker_type, liker_id, likeable_type, likeable_id),
    INDEX idx_liker (liker_type, liker_id),
    INDEX idx_likeable (likeable_type, likeable_id),
    INDEX idx_type (type),
    INDEX idx_updated_at (updated_at)
);
```

---

## 🔍 Exemple complet avec configuration

```php
// 1. Configuration du repository
// config/repository.php
'enum_casts' => [
    'likes' => [
        'type' => AndyDefer\LaravelLikes\Enums\LikeType::class,
    ],
],

// 2. Configuration du like
// config/likes.php
return [
    'like_type_enum' => AndyDefer\LaravelLikes\Enums\LikeType::class,
];

// 3. Utilisation
use AndyDefer\LaravelLikes\Services\LikeService;
use AndyDefer\LaravelLikes\Enums\LikeType;

class PostController extends Controller
{
    public function __construct(
        private readonly LikeService $likeService
    ) {}

    public function react(Request $request, Post $post)
    {
        $user = $request->user();
        $type = LikeType::tryFrom($request->input('type', 'like'));

        if (!$type) {
            return response()->json(['error' => 'Type de réaction invalide'], 400);
        }

        $reacted = $this->likeService->toggle($user, $post, $type);

        return response()->json([
            'reacted' => $reacted,
            'type' => $reacted ? $type->getValue() : null,
            'emoji' => $reacted ? $type->getEmoji() : null,
            'label' => $reacted ? $type->getLabel() : null,
            'stats' => [
                'total' => $this->likeService->countLikes($post),
                'types' => $this->getReactionStats($post),
            ],
        ]);
    }

    private function getReactionStats(Post $post): array
    {
        $stats = [];
        $enumClass = config('likes.like_type_enum', LikeType::class);
        
        foreach ($enumClass::cases() as $type) {
            $stats[$type->getValue()] = [
                'count' => $this->likeService->countLikesByType($post, $type),
                'emoji' => $type->getEmoji(),
                'label' => $type->getLabel(),
            ];
        }
        
        return $stats;
    }

    public function stats(Post $post)
    {
        return response()->json($this->getReactionStats($post));
    }

    public function myReactions(Request $request)
    {
        $user = $request->user();
        $type = LikeType::tryFrom($request->input('type'));

        if ($type) {
            $reactions = $this->likeService->getLikerLikesByType($user, $type);
        } else {
            $reactions = $this->likeService->getLikerLikes($user);
        }

        return response()->json($reactions);
    }
}
```

---

## 🧪 Tests

### Exécuter les tests

```bash
composer test
```

### Exécuter uniquement les tests d'intégration

```bash
composer test-integration
```

### Configuration des tests

Le package utilise `orchestra/testbench` pour les tests d'intégration avec une base de données SQLite en mémoire.

---

## 🔧 Développement

### Style de code

```bash
./vendor/bin/pint
```

### Analyse statique

```bash
./vendor/bin/phpstan analyse
./vendor/bin/psalm
```

---

## 📦 Dépendances

- [`andydefer/php-vo`](https://github.com/andydefer/php-vo) - Value Objects
- [`andydefer/laravel-repository`](https://github.com/andydefer/laravel-repository) - Implémentation du pattern Repository (contient `EnumCast`)
- [`andydefer/domain-structures`](https://github.com/andydefer/domain-structures) - Structures de domaine (AbstractRecord, AbstractData)

---

## 👨‍💻 Auteur

**Andy Kani**
- GitHub: [@andydefer](https://github.com/andydefer)
- Email: andykanidimbu@gmail.com

---

## 📄 Licence

Ce package est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus d'informations.

---

## ⭐ Support

Si vous trouvez ce package utile, n'hésitez pas à lui donner une ⭐ sur GitHub !

---

**Construit avec ❤️ pour la communauté Laravel**
