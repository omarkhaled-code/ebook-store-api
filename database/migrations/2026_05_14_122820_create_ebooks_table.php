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
        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();                                    // auto increment primary key
            $table->string('title');                         // ebook title
            $table->string('slug')->unique();                // URL-friendly name e.g. "mastering-laravel"
            $table->text('description');                     // long description
            $table->string('author');                        // author name
            $table->string('cover_image_path')->nullable();  // path to cover image
            $table->string('pdf_path');                      // path to private PDF file
            $table->decimal('price', 10, 2);                 // e.g. 149.99
            $table->integer('price_in_cents');               // e.g. 14999 (for Paymob)
            $table->boolean('is_published')->default(false); // draft by default
            $table->timestamps();                            // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebooks');
    }
};
