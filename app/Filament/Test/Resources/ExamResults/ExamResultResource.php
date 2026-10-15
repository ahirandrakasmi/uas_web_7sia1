<?php

namespace App\Filament\Test\Resources\ExamResults;

use App\Filament\Test\Resources\ExamResults\Pages\ListExamResults;
use App\Filament\Test\Resources\ExamResults\Pages\ViewExamResult;
use App\Filament\Resources\ExamResults\Schemas\ExamResultInfolist;
use App\Models\ExamResult;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamResultResource extends Resource
{
    protected static ?string $model = ExamResult::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Riwayat Ujian';

    protected static ?string $modelLabel = 'Riwayat Ujian';

    protected static ?string $pluralModelLabel = 'Riwayat Ujian';

    protected static ?string $recordTitleAttribute = 'exam_title';

    public static function getEloquentQuery(): Builder
    {
        $studentId = auth()->user()?->student?->id;

        return parent::getEloquentQuery()
            ->where('student_id', $studentId ?? 0);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('exam_title')
                    ->label('Nama Ujian')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Tanggal Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('score')
                    ->label('Nilai')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('total_questions')
                    ->label('Total Soal'),

                TextColumn::make('correct_count')
                    ->label('Benar')
                    ->color('success'),

                TextColumn::make('incorrect_count')
                    ->label('Salah')
                    ->color('danger'),
            ])
            ->defaultSort('completed_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Detail'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamResultInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamResults::route('/'),
            'view' => ViewExamResult::route('/{record}'),
        ];
    }
}