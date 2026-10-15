
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_result_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_result_id')
                ->constrained('exam_results')
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->nullable()
                ->constrained('questions')
                ->nullOnDelete();

            $table->foreignId('subject_id')
                ->nullable()
                ->constrained('subjects')
                ->nullOnDelete();

            $table->foreignId('answer_id')
                ->nullable()
                ->constrained('answers')
                ->nullOnDelete();

            $table->string('subject_name');
            $table->longText('question_payload');

            $table->text('selected_answer_text')->nullable();
            $table->text('correct_answer_text')->nullable();

            $table->decimal('question_score', 8, 2)->default(0);
            $table->boolean('is_correct')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_result_details');
    }
};
