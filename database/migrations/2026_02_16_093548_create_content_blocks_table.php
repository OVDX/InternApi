<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['text', 'image', 'text_image_right', 'text_image_left']);
            $table->text('text_content')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['news_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
