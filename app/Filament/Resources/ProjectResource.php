<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $pluralModelLabel = 'Projects';

    protected static ?string $modelLabel = 'Project';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Project Details')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),
                    Select::make('status')
                        ->required()
                        ->options([
                            'planning' => 'Planning',
                            'ongoing' => 'Ongoing',
                            'completed' => 'Completed',
                        ])
                        ->default('planning'),
                    TextInput::make('location')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('client')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('area')
                        ->maxLength(255),
                    RichEditor::make('description')
                        ->required()
                        ->columnSpanFull(),
                    FileUpload::make('featured_image')
                        ->image()
                        ->disk('local')
                        ->directory('temp/uploads')
                        ->maxSize(2048)
                        ->columnSpanFull(),
                    TextInput::make('featured_image_alt')
                        ->label('Display image alt text')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('SEO')
                ->schema([
                    TextInput::make('meta_description')
                        ->maxLength(160)
                        ->helperText('Keep this concise for search engine snippets.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('client')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}