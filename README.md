Tu as raison, je suis vraiment désolé ! C'est une erreur stupide. Voici la README corrigée sans mentionner un nombre fixe de types :

---

# Laravel Likes

> Système de réactions polymorphiques extensible pour applications Laravel

Un package Laravel complet pour gérer des réactions polymorphiques avec un système de toggle intelligent, des enums extensibles, des DTOs et des Value Objects.

---

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Extensibilité](#extensibilité)
- [Utilisation](#utilisation)
  - [Toggle une réaction](#toggle-une-réaction)
  - [Ajouter un like](#ajouter-un-like)
  - [Supprimer un like](#supprimer-un-like)
  - [Vérifier une réaction](#vérifier-une-réaction)
  - [Compter les réactions](#compter-les-réactions)
  - [Récupérer les réactions](#récupérer-les-réactions)
  - [Filtrer par date](#filtrer-par-date)
- [Types de réactions par défaut](#types-de-réactions-par-défaut)
- [Référence de l'API](#référence-de-lapi)
- [Value Objects](#value-objects)
- [Structure de la base de données](#structure-de-la-base-de-données)
- [Tests](#tests)
- [Contribuer](#contribuer)
- [Licence](#licence)

---

## ✨ Fonctionnalités

- ✅ **Double polymorphisme** - Réagissez à n'importe quel modèle avec n'importe quel utilisateur
- ✅ **Types de réactions entièrement configurables** - Créez vos propres énumérations
- ✅ **Toggle intelligent** - Changez de réaction en un seul appel
- ✅ **Filtrage temporel** - Récupérez les réactions après une date donnée
- ✅ **Pattern Repository** - Séparation propre de la logique d'accès aux données
- ✅ **Support des DTOs** - Objets de transfert de données typés
- ✅ **Value Objects** - DateTime, Métadonnées
- ✅ **Enum Casts** - Conversion automatique entre base de données et énumérations PHP
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

---

## ⚙️ Configuration

### Service Provider

Le package est automatiquement découvert par Laravel. Aucune configuration supplémentaire n'est requise.

### Configuration des Enum Casts

Le package utilise le système d'`EnumCast` du package `andydefer/laravel-repository` pour convertir automatiquement les valeurs en énumérations PHP.

Créez ou modifiez le fichier `config/repository.php` :

```php
<?php

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
            'type' => App\Enums\CustomLikeType::class, // Votre enum personnalisé
        ],
    ],
];
```

> **⚠️ Important** : 
> - Sans cette configuration, les énumérations ne seront pas automatiquement converties
> - L'énumération **DOIT** implémenter l'interface `AndyDefer\Repository\Contracts\EnumerableInterface`
> - La méthode `getValue()` est obligatoire pour l'interface

---

## 🔧 Extensibilité

### Créer vos types de réactions personnalisés

Le package est conçu pour être extensible. Vous devez créer votre propre enum qui implémente `EnumerableInterface`.

> **⚠️ OBLIGATOIRE :** Vos énumérations DOIVENT implémenter l'interface `EnumerableInterface`

#### 1. Créer votre enum

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use AndyDefer\Repository\Contracts\EnumerableInterface;

enum CustomLikeType: string implements EnumerableInterface  // ⚠️ Interface obligatoire
{
    case LIKE = 'like';
    case LOVE = 'love';
    case HAHA = 'haha';
    case WOW = 'wow';
    case SAD = 'sad';
    case ANGRY = 'angry';
    // Ajoutez autant de cas que vous voulez !

    /**
     * Obligatoire - Retourne la valeur brute de l'énumération
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Optionnel - Méthode utilitaire pour l'affichage
     */
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

    /**
     * Optionnel - Méthode utilitaire pour l'affichage
     */
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
```

#### 2. Configurer l'enum dans le repository

```php
// config/repository.php
'enum_casts' => [
    'likes' => [
        'type' => App\Enums\CustomLikeType::class,
    ],
],
```

#### 3. Utiliser vos réactions

```php
use App\Enums\CustomLikeType;

// Toggle avec votre enum
$liked = $likeService->toggle($user, $post, CustomLikeType::LIKE);

// Compter par type
$count = $likeService->countLikesByType($post, CustomLikeType::LOVE);

// Récupérer les likeurs par type
$likers = $likeService->getLikersByType($post, CustomLikeType::HAHA);
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
use App\Enums\CustomLikeType;

class PostController extends Controller
{
    public function react(LikeService $likeService, Post $post)
    {
        $user = auth()->user();

        // Toggle une réaction
        $reacted = $likeService->toggle($user, $post, CustomLikeType::LIKE);

        return response()->json([
            'reacted' => $reacted,
            'type' => $reacted ? CustomLikeType::LIKE->getValue() : null,
            'emoji' => $reacted ? CustomLikeType::LIKE->getEmoji() : null,
        ]);
    }
}
```

### Ajouter un like

```php
// Ajoute un like - Lève une exception si déjà liké
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
$likes = $likeService->countLikesByType($post, CustomLikeType::LIKE);
$loves = $likeService->countLikesByType($post, CustomLikeType::LOVE);
```

### Récupérer les réactions

```php
// Récupérer tous les likeurs d'un objet
$likers = $likeService->getLikers($post);

// Récupérer les likeurs par type
$likersByType = $likeService->getLikersByType($post, CustomLikeType::LOVE);

// Récupérer toutes les réactions d'un utilisateur
$userLikes = $likeService->getLikerLikes($user);

// Récupérer les réactions d'un utilisateur par type
$userLoves = $likeService->getLikerLikesByType($user, CustomLikeType::LOVE);
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

Le package fournit un enum par défaut, mais vous êtes libre de le remplacer par le vôtre.

| Type | Valeur | Emoji | Label |
|------|--------|-------|-------|
| `LikeType::LIKE` | `'like'` | 👍 | J'aime |
| `LikeType::LOVE` | `'love'` | ❤️ | J'adore |
| `LikeType::HAHA` | `'haha'` | 😂 | Haha |
| `LikeType::WOW` | `'wow'` | 😮 | Wow |
| `LikeType::SAD` | `'sad'` | 😢 | Triste |
| `LikeType::ANGRY` | `'angry'` | 😡 | En colère |

> **💡 Conseil** : Vous pouvez ajouter, supprimer ou modifier ces types en créant votre propre enum comme expliqué dans la section [Extensibilité](#extensibilité).

---

## 📚 Référence de l'API

### LikeService

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `toggle(Model $liker, Model $likeable, EnumerableInterface $type)` | Toggle une réaction (ajoute/change/supprime) | `bool` |
| `like(Model $liker, Model $likeable)` | Ajoute un like | `void` |
| `unlike(Model $liker, Model $likeable)` | Supprime un like | `void` |
| `hasLiked(Model $liker, Model $likeable)` | Vérifie si l'utilisateur a réagi | `bool` |
| `countLikes(Model $likeable)` | Compte toutes les réactions | `int` |
| `countLikesByType(Model $likeable, EnumerableInterface $type)` | Compte les réactions par type | `int` |
| `getLikers(Model $likeable)` | Récupère tous les likeurs | `Collection` |
| `getLikersByType(Model $likeable, EnumerableInterface $type)` | Récupère les likeurs par type | `Collection` |
| `getLikerLikes(Model $liker)` | Récupère les réactions d'un utilisateur | `Collection` |
| `getLikerLikesByType(Model $liker, EnumerableInterface $type)` | Récupère les réactions d'un utilisateur par type | `Collection` |
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
$type = $like->type;                  // EnumerableInterface (votre enum)

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
    type VARCHAR(20) DEFAULT 'like',     -- Valeur de votre enum
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

## 🔍 Exemple complet

```php
use AndyDefer\LaravelLikes\Services\LikeService;
use App\Enums\CustomLikeType;

class PostController extends Controller
{
    public function __construct(
        private readonly LikeService $likeService
    ) {}

    public function react(Request $request, Post $post)
    {
        $user = $request->user();
        $type = CustomLikeType::tryFrom($request->input('type', 'like'));

        if (!$type) {
            return response()->json(['error' => 'Type de réaction invalide'], 400);
        }

        $reacted = $this->likeService->toggle($user, $post, $type);

        return response()->json([
            'reacted' => $reacted,
            'type' => $reacted ? $type->getValue() : null,
            'emoji' => $reacted ? $type->getEmoji() : null,
            'label' => $reacted ? $type->getLabel() : null,
            'stats' => $this->getReactionStats($post),
        ]);
    }

    private function getReactionStats(Post $post): array
    {
        $stats = [];
        
        foreach (CustomLikeType::cases() as $type) {
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
        $type = CustomLikeType::tryFrom($request->input('type'));

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