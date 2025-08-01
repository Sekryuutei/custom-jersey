<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('file_name'); // URL ke gambar desain di Cloudinary
            $table->string('size')->default('L');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });
    }
};
