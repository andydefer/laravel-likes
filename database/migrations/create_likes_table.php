<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->morphs('liker');
            $table->morphs('likeable');
            $table->string('type')->default('like');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['liker_type', 'liker_id', 'likeable_type', 'likeable_id'], 'likes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
