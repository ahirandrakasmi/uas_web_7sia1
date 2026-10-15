<?php

namespace App\Filament\Test\Resources\Exams\Pages;

use App\Filament\Test\Resources\Exams\ExamResource;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Question;
use App\Models\Student;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartingExam extends Page
{
    protected static string $resource = ExamResource::class;

    protected string $view = 'filament.test.resources.exams.pages.starting-exam';

    public Exam $exam;

    public array $collections = [];

    public array $answers = [];

    public ?string $examStartedAt = null;

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
        $this->examStartedAt = now()->toDateTimeString();

        foreach ($exam->subjects as $mapel) {
            $questions = $mapel->questions()
                ->where('is_active', true)
                ->with([
                    'answers' => fn ($query) => $query
                        ->where('is_active', true)
                        ->inRandomOrder(),
                ])
                ->inRandomOrder()
                ->limit($mapel->pivot->qty)
                ->get();

            $this->collections[] = [
                'id' => $mapel->id,
                'name' => $mapel->name,
                'soals' => $questions->toArray(),
            ];
        }
    }

    public function submit(): void
    {
        $user = Auth::user();

        $student = $user?->student;

        if (! $student) {
            Notification::make()
                ->title('Data siswa tidak ditemukan')
                ->body('Akun yang digunakan belum terhubung dengan data siswa.')
                ->danger()
                ->send();

            return;
        }

        $questionIds = collect($this->collections)
            ->flatMap(fn ($mapel) => collect($mapel['soals'])
                ->pluck('id'))
            ->values();

        if ($questionIds->isEmpty()) {
            Notification::make()
                ->title('Soal ujian belum tersedia')
                ->danger()
                ->send();

            return;
        }

        foreach ($this->answers as $questionId => $answerId) {
            if (! $questionIds->contains((int) $questionId)) {
                throw ValidationException::withMessages([
                    'answers' => 'Terdapat soal yang tidak valid.',
                ]);
            }

            if ($answerId !== null && $answerId !== '') {
                $validAnswer = Answer::query()
                    ->whereKey($answerId)
                    ->where('question_id', $questionId)
                    ->where('is_active', true)
                    ->exists();

                if (! $validAnswer) {
                    throw ValidationException::withMessages([
                        'answers' => 'Terdapat pilihan jawaban yang tidak valid.',
                    ]);
                }
            }
        }

        $result = DB::transaction(function () use (
            $student,
            $questionIds
        ) {
            $details = [];
            $correctCount = 0;
            $totalScore = 0;
            $maxScore = 0;

            foreach ($this->collections as $mapel) {
                foreach ($mapel['soals'] as $questionData) {
                    $questionId = $questionData['id'];

                    $question = Question::query()
                        ->with([
                            'answers' => fn ($query) => $query
                                ->where('is_active', true),
                        ])
                        ->findOrFail($questionId);

                    $selectedAnswerId = $this->answers[$questionId] ?? null;

                    $selectedAnswer = $selectedAnswerId
                        ? $question->answers->firstWhere(
                            'id',
                            (int) $selectedAnswerId
                        )
                        : null;

                    $correctAnswer = $question->answers
                        ->firstWhere('is_correct', true);

                    $isCorrect = $selectedAnswer !== null
                        && $selectedAnswer->is_correct;

                    $questionScore = (float) $question->score;
                    $maxScore += $questionScore;

                    if ($isCorrect) {
                        $correctCount++;
                        $totalScore += $questionScore;
                    }

                    $details[] = [
                        'question_id' => $question->id,
                        'subject_id' => $mapel['id'],
                        'answer_id' => $selectedAnswer?->id,
                        'subject_name' => $mapel['name'],
                        'question_payload' => $question->payload,
                        'selected_answer_text' => $selectedAnswer?->text,
                        'correct_answer_text' => $correctAnswer?->text,
                        'question_score' => $isCorrect
                            ? $questionScore
                            : 0,
                        'is_correct' => $isCorrect,
                    ];
                }
            }

            $totalQuestions = count($details);
            $incorrectCount = $totalQuestions - $correctCount;

            $score = $maxScore > 0
                ? round(($totalScore / $maxScore) * 100, 2)
                : 0;

            $result = ExamResult::create([
                'student_id' => $student->id,
                'exam_id' => $this->exam->id,
                'student_name' => $student->name,
                'exam_title' => $this->exam->title,
                'started_at' => $this->examStartedAt ?? now()->toDateTimeString(),
                'completed_at' => now(),
                'score' => $score,
                'total_questions' => $totalQuestions,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
            ]);

            $result->details()->createMany($details);

            return $result;
        });

        Notification::make()
            ->title('Ujian berhasil diselesaikan')
            ->body(
                "Nilai: {$result->score} | Benar: {$result->correct_count} | Salah: {$result->incorrect_count}"
            )
            ->success()
            ->persistent()
            ->send();

        $this->redirect(ExamResource::getUrl('index'));
    }
}
