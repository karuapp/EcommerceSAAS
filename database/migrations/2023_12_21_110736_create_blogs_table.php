<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('cover_image_url')->nullable();
            $table->text('cover_image_path')->nullable();
            $table->string('theme_id')->nullable();
            $table->string('store_id')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('blog_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
