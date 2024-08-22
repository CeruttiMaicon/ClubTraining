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
        if (!Schema::hasTable('scout_actions')) {
            Schema::create('scout_actions', function (Blueprint $table) {

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

                // NOTE - Campos utilizados em todas as ações
                $table->smallInteger('total')
                    ->unsigned()
                    ->comment('Número total de ações registradas');
                $table->smallInteger('total_points')
                    ->unsigned()
                    ->comment('Usado apenas para ataques e bloqueios');
                $table->smallInteger('total_errors')
                    ->unsigned()
                    ->comment('Número total de erros para todas as ações');
                $table->smallInteger('total_result_points')
                    ->unsigned()
                    ->comment('Usado apenas para Serve');

                // NOTE - Campos específicos para Defense
                $table->smallInteger('total_forearm_pass')
                    ->unsigned()
                    ->comment('Usado apenas para Defense');
                $table->smallInteger('total_overhead_pass')
                    ->unsigned()
                    ->comment('Usado apenas para Defense');

                // NOTE - Campos específicos para Reception
                $table->smallInteger('total_passes_a')
                    ->unsigned()
                    ->comment('Usado apenas para Reception');
                $table->smallInteger('total_passes_b')
                    ->unsigned()
                    ->comment('Usado apenas para Reception');
                $table->smallInteger('total_passes_c')
                    ->unsigned()
                    ->comment('Usado apenas para Reception');

                // NOTE - Campos específicos para SetAssist
                $table->smallInteger('total_excellent')
                    ->unsigned()
                    ->comment('Usado apenas para SetAssist');
                $table->smallInteger('total_good')
                    ->unsigned()
                    ->comment('Usado apenas para SetAssist');
                $table->smallInteger('total_poor')
                    ->unsigned()
                    ->comment('Usado apenas para SetAssist');

                // NOTE - Campos específicos para Attack
                $table->smallInteger('total_against')
                    ->unsigned()
                    ->comment('Usado apenas para Attack');

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
        Schema::dropIfExists('scout_actions');
    }
};
