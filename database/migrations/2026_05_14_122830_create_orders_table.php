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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // links to users table
            $table->foreignId('ebook_id')->constrained()->cascadeOnDelete(); // links to ebooks table
            $table->string('paymob_order_id')->nullable();                   // we get this from Paymob
            $table->string('paymob_transaction_id')->nullable();             // we get this from webhook
            $table->decimal('amount', 10, 2);                                // price snapshot at purchase time
            $table->enum('status', ['pending', 'paid', 'failed']);           // order status
            $table->timestamp('paid_at')->nullable();                        // when payment confirmed
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
