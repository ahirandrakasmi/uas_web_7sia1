<?php

namespace App\Filament\Resources\ExamResults\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('exam_title')
                    ->label('Nama Ujian')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Tanggal Selesai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum selesai')
                    ->sortable(),

                TextColumn::make('score')
                    ->label('Nilai')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_questions')
                    ->label('Total Soal')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('correct_count')
                    ->label('Benar')
                    ->numeric()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('incorrect_count')
                    ->label('Salah')
                    ->numeric()
                    ->color('danger')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail'),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
