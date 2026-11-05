<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_likes_table.php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Longueur limitée à 191 pour permettre l'index unique composite en utf8mb4
            $table->string('liker_type', 191);
            $table->string('liker_id', 191);
            $table->string('likeable_type', 191);
            $table->string('likeable_id', 191);

            $table->string('type')->default('favorite');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['liker_type', 'liker_id'], 'likes_liker_index');
            $table->index(['likeable_type', 'likeable_id'], 'likes_likeable_index');

            $table->unique(
                ['liker_type', 'liker_id', 'likeable_type', 'likeable_id'],
                'likes_unique',
            );

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
