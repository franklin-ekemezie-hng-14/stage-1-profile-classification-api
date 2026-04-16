<?php

use App\Enums\AgeGroup;
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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name');
            $table->string('gender');
            $table->float('gender_probability');
            $table->integer('sample_size');
            $table->integer('age');
            $table->enum('age_group', AgeGroup::values());
            $table->string('country_id');
            $table->string('country_probability');
            $table->timestamps();

            $table->index(['name', 'uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
