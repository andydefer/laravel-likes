# LikeService - Référence Technique

## Description

Service principal de gestion des réactions (likes). Orchestre toutes les opérations : ajout, suppression, toggle, comptage, filtrage et récupération des réactions.

## Hiérarchie

```
LikeService
```

## Rôle principal

`LikeService` est le point d'entrée unique pour toutes les opérations de réaction. Il utilise le `LikeRepository` pour l'accès aux données et expose une API complète pour :
- Toggle intelligent (ajoute, change ou supprime en un seul appel)
- Like/Unlike simple
- Vérification d'existence
- Comptage et filtrage
- Récupération des likeurs

## API / Méthodes publiques

### `toggle(Model $liker, Model $likeable, LikeType $type = LikeType::LIKE): bool`

Toggle une réaction (ajoute, change ou supprime).

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$liker` | `Model` | L'utilisateur qui réagit |
| `$likeable` | `Model` | L'objet qui reçoit la réaction |
| `$type` | `LikeType` | Type de réaction (défaut: LIKE) |

**Retourne :** `bool` - True si une réaction existe, false si supprimée

**Exceptions :** Aucune

**Exemple :**
```php
$liked = $likeService->toggle($user, $post, LikeType::LIKE);
// → true (ajouté)
// → true (changé)
// → false (supprimé)
```

---

### `like(Model $liker, Model $likeable): void`

Ajoute un like (👍).

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$liker` | `Model` | L'utilisateur qui like |
| `$likeable` | `Model` | L'objet liké |

**Retourne :** `void`

**Exceptions :** `RuntimeException` si déjà liké

**Exemple :**
```php
try {
    $likeService->like($user, $post);
} catch (RuntimeException $e) {
    // Déjà liké
}
```

---

### `unlike(Model $liker, Model $likeable): void`

Supprime un like.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$liker` | `Model` | L'utilisateur qui unlike |
| `$likeable` | `Model` | L'objet unliké |

**Retourne :** `void`

**Exceptions :** `RuntimeException` si non liké

**Exemple :**
```php
if ($likeService->hasLiked($user, $post)) {
    $likeService->unlike($user, $post);
}
```

---

### `hasLiked(Model $liker, Model $likeable): bool`

Vérifie si l'utilisateur a réagi.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$liker` | `Model` | L'utilisateur |
| `$likeable` | `Model` | L'objet |

**Retourne :** `bool`

**Exemple :**
```php
if ($likeService->hasLiked($user, $post)) {
    echo 'Vous avez aimé ce post';
}
```

---

### `countLikes(Model $likeable): int`

Compte toutes les réactions d'un objet.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$likeable` | `Model` | L'objet |

**Retourne :** `int`

**Exemple :**
```php
$total = $likeService->countLikes($post);
echo "Total réactions : {$total}";
```

---

### `countLikesByType(Model $likeable, LikeType $type): int`

Compte les réactions par type.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$likeable` | `Model` | L'objet |
| `$type` | `LikeType` | Type de réaction |

**Retourne :** `int`

**Exemple :**
```php
$loves = $likeService->countLikesByType($post, LikeType::LOVE);
$likes = $likeService->countLikesByType($post, LikeType::LIKE);
```

---

### `getLikers(Model $likeable): Collection`

Récupère tous les likeurs d'un objet (via la relation polymorphique).

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$likeable` | `Model` | L'objet |

**Retourne :** `Collection` - Collection des modèles likeurs

**Exemple :**
```php
$likers = $likeService->getLikers($post);
foreach ($likers as $liker) {
    echo $liker->name;
}
```

---

### `getLikersByType(Model $likeable, LikeType $type): Collection`

Récupère les likeurs par type.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$likeable` | `Model` | L'objet |
| `$type` | `LikeType` | Type de réaction |

**Retourne :** `Collection`

**Exemple :**
```php
$loveLikers = $likeService->getLikersByType($post, LikeType::LOVE);
```

---

### `getLikerLikes(Model $liker): Collection`

Récupère toutes les réactions d'un utilisateur.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$liker` | `Model` | L'utilisateur |

**Retourne :** `Collection`

**Exemple :**
```php
$myLikes = $likeService->getLikerLikes($user);
```

---

### `getLikerLikesByType(Model $liker, LikeType $type): Collection`

Récupère les réactions d'un utilisateur par type.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$liker` | `Model` | L'utilisateur |
| `$type` | `LikeType` | Type de réaction |

**Retourne :** `Collection`

**Exemple :**
```php
$myLoves = $likeService->getLikerLikesByType($user, LikeType::LOVE);
```

---

### `getLikesUpdatedAfter(DateTimeVO $date): Collection`

Récupère toutes les réactions mises à jour après une date.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$date` | `DateTimeVO` | Date minimale |

**Retourne :** `Collection`

**Exemple :**
```php
$date = DateTimeVO::from('2024-01-01 00:00:00');
$recent = $likeService->getLikesUpdatedAfter($date);
```

---

### `getLikerLikesUpdatedAfter(Model $liker, DateTimeVO $date): Collection`

Récupère les réactions d'un utilisateur après une date.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$liker` | `Model` | L'utilisateur |
| `$date` | `DateTimeVO` | Date minimale |

**Retourne :** `Collection`

**Exemple :**
```php
$date = DateTimeVO::from(now()->subDays(7)->toIso8601String());
$recentUserLikes = $likeService->getLikerLikesUpdatedAfter($user, $date);
```

---

### `getLikesForLikeableUpdatedAfter(Model $likeable, DateTimeVO $date): Collection`

Récupère les réactions d'un objet après une date.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$likeable` | `Model` | L'objet |
| `$date` | `DateTimeVO` | Date minimale |

**Retourne :** `Collection`

**Exemple :**
```php
$date = DateTimeVO::from(now()->subDays(7)->toIso8601String());
$recentPostLikes = $likeService->getLikesForLikeableUpdatedAfter($post, $date);
```

## Flux d'exécution

### Toggle

```
toggle($liker, $likeable, $type)
    ↓
findExisting()
    ↓
Existe ?
    ├── OUI → Même type ?
    │   ├── OUI → delete() → retourne false
    │   └── NON → update() → retourne true
    └── NON → create() → retourne true
```

### Like/Unlike

```
like($liker, $likeable)
    ↓
findExisting()
    ↓
Existe ?
    ├── OUI → RuntimeException
    └── NON → create()
```

```
unlike($liker, $likeable)
    ↓
findExisting()
    ↓
Existe ?
    ├── OUI → delete()
    └── NON → RuntimeException
```

## Cas d'utilisation

### Cas 1 : Système de réactions pour un réseau social

**Problème :** Permettre aux utilisateurs de réagir aux publications avec différents émojis.

**Solution :** Utiliser `toggle()` pour une gestion simplifiée.

```php
class PostReactionController extends Controller
{
    public function __construct(
        private readonly LikeService $likeService
    ) {}

    public function react(Request $request, Post $post)
    {
        $user = $request->user();
        $type = LikeType::tryFrom($request->input('type', 'like'));

        $reacted = $this->likeService->toggle($user, $post, $type);

        return response()->json([
            'reacted' => $reacted,
            'type' => $reacted ? $type->value : null,
            'emoji' => $reacted ? $type->getEmoji() : null,
            'total' => $this->likeService->countLikes($post),
        ]);
    }
}
```

### Cas 2 : Analyse des réactions avec filtrage temporel

**Problème :** Analyser les réactions des derniers 30 jours.

**Solution :** Utiliser les méthodes `get*UpdatedAfter()`.

```php
class ReactionAnalyticsService
{
    public function getRecentReactions(int $days = 30): array
    {
        $date = DateTimeVO::from(
            now()->subDays($days)->toIso8601String()
        );

        $reactions = $this->likeService->getLikesUpdatedAfter($date);

        return [
            'total' => $reactions->count(),
            'by_type' => $reactions->groupBy('type')->map->count(),
            'by_user' => $reactions->groupBy('liker_id')->map->count(),
        ];
    }

    public function getUserReactions(User $user, int $days = 30): Collection
    {
        $date = DateTimeVO::from(
            now()->subDays($days)->toIso8601String()
        );

        return $this->likeService->getLikerLikesUpdatedAfter($user, $date);
    }

    public function getPostReactions(Post $post, int $days = 30): Collection
    {
        $date = DateTimeVO::from(
            now()->subDays($days)->toIso8601String()
        );

        return $this->likeService->getLikesForLikeableUpdatedAfter($post, $date);
    }
}
```

### Cas 3 : Gestion des likes avec conditions métier

**Problème :** Un utilisateur ne peut liker qu'une fois par objet.

**Solution :** Utiliser `hasLiked()` pour vérifier avant d'appeler `like()`.

```php
class PostService
{
    public function toggleLike(User $user, Post $post): array
    {
        // ✅ Vérification explicite
        if ($this->likeService->hasLiked($user, $post)) {
            $this->likeService->unlike($user, $post);
            return ['liked' => false, 'total' => $this->likeService->countLikes($post)];
        }

        $this->likeService->like($user, $post);
        return ['liked' => true, 'total' => $this->likeService->countLikes($post)];
    }

    public function getUserLikes(User $user): array
    {
        // ✅ Récupérer toutes les réactions de l'utilisateur
        $likes = $this->likeService->getLikerLikes($user);

        return $likes->map(function ($like) {
            return [
                'object' => $like->likeable,
                'type' => $like->type,
                'emoji' => $like->type->getEmoji(),
                'created_at' => $like->created_at,
            ];
        })->toArray();
    }
}
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Like sur un objet déjà liké | `RuntimeException` | `User {id} has already liked {type} {id}` |
| Unlike sur un objet non liké | `RuntimeException` | `User {id} has not liked {type} {id}` |
| Type de réaction invalide | `InvalidArgumentException` | (géré par l'utilisateur via `tryFrom`) |

## Intégration

### Avec LikeRepository

`LikeService` utilise `LikeRepository` pour toutes les opérations de persistance.

```php
$this->likeRepository->create($record);
$this->likeRepository->update($existing->id, $updateRecord);
$this->likeRepository->delete($existing->id);
$this->likeRepository->count($filter);
```

### Avec LikeFilterRecord

Les filtres sont construits via `LikeFilterRecord` pour une recherche typée.

```php
$filter = LikeFilterRecord::from([
    'liker_type' => $liker->getMorphClass(),
    'liker_id' => $liker->getKey(),
    'likeable_type' => $likeable->getMorphClass(),
    'likeable_id' => $likeable->getKey(),
]);
```

### Avec LikeCast

Le cast convertit automatiquement la colonne `type` en enum.

```php
$existing = $this->findExisting($liker, $likeable);
if ($existing->type === $type) { // ✅ Comparaison d'enums
    // ...
}
```

## Performance

- **Toggle** : 1 ou 2 requêtes (find + create/update/delete)
- **Count** : 1 requête COUNT optimisée
- **getLikers** : 1 requête avec relations polymorphes
- **Filtrage temporel** : Index sur `updated_at` pour les requêtes
- **Limitation** : `FindByRecord` avec `limit: 1` pour findExisting

## Compatibilité

| Version PHP | Support |
|-------------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.0 | ❌ Non supporté (pas d'enums) |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\LaravelLikes\Services\LikeService;
use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use App\Models\User;
use App\Models\Post;

$likeService = app(LikeService::class);
$user = User::find(1);
$post = Post::find(1);

// 1. Toggle (ajoute, change, supprime)
$liked = $likeService->toggle($user, $post, LikeType::LOVE);
// → true (ajouté)

// 2. Vérifier
if ($likeService->hasLiked($user, $post)) {
    echo 'Vous avez réagi !';
}

// 3. Compter
$total = $likeService->countLikes($post);
$loves = $likeService->countLikesByType($post, LikeType::LOVE);

// 4. Récupérer les likeurs
$likers = $likeService->getLikers($post);
$loveLikers = $likeService->getLikersByType($post, LikeType::LOVE);

// 5. Récupérer les réactions de l'utilisateur
$myLikes = $likeService->getLikerLikes($user);
$myLoves = $likeService->getLikerLikesByType($user, LikeType::LOVE);

// 6. Filtrage temporel
$date = DateTimeVO::from(now()->subDays(30)->toIso8601String());
$recentLikes = $likeService->getLikesUpdatedAfter($date);
$userRecent = $likeService->getLikerLikesUpdatedAfter($user, $date);
$postRecent = $likeService->getLikesForLikeableUpdatedAfter($post, $date);

// 7. Unlike
$likeService->unlike($user, $post);
```

## Voir aussi

- `LikeRepository` - Repository d'accès aux données
- `LikeType` - Enum des types de réactions
- `LikeFilterRecord` - DTO de filtrage
- `LikeCast` - Cast Eloquent pour la colonne type
- `DateTimeVO` - Value Object pour les dates