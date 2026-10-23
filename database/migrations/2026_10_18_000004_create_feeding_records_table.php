<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->restrictOnDelete();
            $table->dateTime('fed_at');
            $table->string('feed_type', 150);
            $table->decimal('quantity', 10, 2);
            $table->enum('unit', ['kg', 'g', 'litre', 'bag', 'other'])->default('kg');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('fed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_records');
    }
};
