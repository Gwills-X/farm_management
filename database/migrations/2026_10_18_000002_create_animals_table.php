<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_type_id')->constrained('animal_types')->restrictOnDelete();
            $table->string('animal_code', 20)->unique();
            $table->string('name', 100)->nullable();
            $table->enum('sex', ['male', 'female', 'unknown'])->default('unknown');
            $table->string('breed', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('acquisition_type', ['purchased', 'born_on_farm', 'other']);
            $table->date('acquisition_date');
            $table->string('source', 150)->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->enum('status', ['active', 'sold', 'deceased', 'archived'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['animal_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
