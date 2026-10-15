<?php

namespace App\Filament\Resources\ExamResults\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamResultInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Ujian')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('student_name')
                            ->label('Nama Siswa'),

                        TextEntry::make('exam_title')
                            ->label('Nama Ujian'),

                        TextEntry::make('started_at')
                            ->label('Waktu Mulai')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Tidak tersedia'),

                        TextEntry::make('completed_at')
                            ->label('Waktu Selesai')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Belum selesai'),

                        TextEntry::make('score')
                            ->label('Nilai Akhir')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('total_questions')
                            ->label('Total Soal'),

                        TextEntry::make('correct_count')
                            ->label('Jawaban Benar')
                            ->color('success'),

                        TextEntry::make('incorrect_count')
                            ->label('Jawaban Salah')
                            ->color('danger'),
                    ]),

                Section::make('Rincian Jawaban Siswa')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label('Daftar Soal')
                            ->contained(true)
                            ->schema([
                                TextEntry::make('subject_name')
                                    ->label('Mata Pelajaran'),

                                TextEntry::make('question_payload')
                                    ->label('Pertanyaan')
                                    ->html()
                                    ->columnSpanFull(),

                                TextEntry::make('selected_answer_text')
                                    ->label('Jawaban Siswa')
                                    ->placeholder('Tidak dijawab')
                                    ->columnSpanFull(),

                                TextEntry::make('correct_answer_text')
                                    ->label('Kunci Jawaban')
                                    ->placeholder('Tidak tersedia')
                                    ->columnSpanFull(),

                                TextEntry::make('is_correct')
                                    ->label('Status Jawaban')
                                    ->formatStateUsing(
                                        fn ($state, $record): string => blank($record->selected_answer_text)
                                            ? 'Tidak dijawab'
                                            : ($state ? 'Benar' : 'Salah')
                                    )
                                    ->badge()
                                    ->color(
                                        fn ($state, $record): string => blank($record->selected_answer_text)
                                            ? 'gray'
                                            : ($state ? 'success' : 'danger')
                                    ),

                                TextEntry::make('question_score')
                                    ->label('Nilai Soal')
                                    ->numeric(decimalPlaces: 2),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
