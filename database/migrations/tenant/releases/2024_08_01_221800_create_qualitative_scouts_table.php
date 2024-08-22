<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('qualitative_scouts')) {
            Schema::create('qualitative_scouts', function (Blueprint $table) {

                $table->id();
                $table->foreignId('user_id')
                    ->constrained('users');
                $table->foreignId('player_id')
                    ->constrained('users');
                $table->foreignId('training_id')
                    ->constrained('trainings');
                $table->foreignId('position_id')
                    ->constrained('positions');
                $table->foreignId('action_type_id')
                    ->constrained('action_types');

                // NOTE - Campos utilizados para marcações de avaliação
                $table->smallInteger('set_number')
                    ->unsigned();
                $table->smallInteger('number_point')
                    ->unsigned();
                $table->smallInteger('evaluation')
                    ->unsigned();
                $table->smallInteger('time')
                    ->unsigned();

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qualitative_scouts');
    }
};
