<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->restrictOnDelete();
            $table->date('observation_date');
            $table->text('observed_condition');
            $table->enum('health_status', ['healthy', 'monitoring', 'sick', 'recovered']);
            $table->text('treatment')->nullable();
            $table->text('medication')->nullable();
            $table->string('veterinary_professional', 150)->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('observation_date');
            $table->index('health_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
