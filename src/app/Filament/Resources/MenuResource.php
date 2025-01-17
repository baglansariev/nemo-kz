<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use App\Models\Language;
use App\Models\MenuType;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.menus');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.menus');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.menus');
    }

    public static function form(Form $form): Form
    {
        $languages = Language::where('active', true)->get();
        $menuTypes = MenuType::all();
        $menus = Menu::all();
        $menuOptions = [];
        $menuTypeOptions = [];
        $tabs = [];

        foreach ($languages as $language) {
            $tabs[] = Tabs\Tab::make($language->name)
                ->schema([
                    TextInput::make('translations.' . $language->code . '.name')
                        ->label(__('admin.crud.create.name'))
                        ->required()
                        ->maxLength(255),
                    Hidden::make('translations.' . $language->code . '.language_id')
                        ->default($language->id),
                ]);
        }

        foreach ($menus as $menu) {
            $menuOptions[$menu->id] = $menu->translation()?->name;
        }

        foreach ($menuTypes as $menuType) {
            $menuTypeOptions[$menuType->id] = $menuType->name;
        }

        return $form
            ->schema([
                Select::make('menu_type_id')
                    ->label(__('admin.crud.create.menu_type'))
                    ->options($menuTypeOptions)
                    ->required()
                    ->columnSpan(1),
                Select::make('parent_id')
                    ->label(__('admin.crud.create.parent'))
                    ->options($menuOptions)
                    ->columnSpan(1),
                Forms\Components\TextInput::make('link')
                    ->label(__('admin.crud.create.link'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                TextInput::make('sort')
                    ->label(__('admin.crud.create.sort'))
                    ->default(1)
                    ->required()
                    ->integer()
                    ->columnSpan(1),
                Tabs::make('translations')
                    ->label('Translations')
                    ->tabs($tabs)
                    ->columnSpan(2),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('admin.crud.index.id')),
                Tables\Columns\TextColumn::make('name')
                    ->state(fn (Menu $menu) => $menu->translation()?->name)
                    ->label(__('admin.crud.index.name')),
                Tables\Columns\TextColumn::make('menu_type')
                    ->state(fn (Menu $menu) => $menu->type?->name)
                    ->label(__('admin.crud.create.menu_type')),
                Tables\Columns\TextColumn::make('parent')
                    ->state(fn (Menu $post) => $post->parent?->translation()?->name)
                    ->label(__('admin.crud.index.parent'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('link')
                    ->label(__('admin.crud.index.link'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort')
                    ->label(__('admin.crud.index.sort'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
