<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = "heroicon-o-users";
    protected static ?string $navigationLabel = "Pelanggan";
    protected static ?string $navigationGroup = "Master Data";
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("name")
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make("phone")->tel()->maxLength(255),
            Forms\Components\Textarea::make("address")->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->label("Nama")
                    ->searchable()
                    ->description(fn(Customer $record): ?string => $record->phone ?? null),
                Tables\Columns\TextColumn::make("phone")
                    ->label("No. HP")
                    ->searchable()
                    ->url(fn(Customer $record) => $record->phone ? 'https://wa.me/' . preg_replace('/\D/', '', $record->phone) : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
                Tables\Columns\TextColumn::make("total_debt")
                    ->label("Total Utang")
                    ->getStateUsing(fn(Customer $record) => $record->totalDebt())
                    ->money('IDR')
                    ->color(fn($state) => $state > 0 ? 'danger' : 'gray')
                    ->weight(fn($state) => $state > 0 ? 'bold' : 'normal')
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
                Tables\Columns\TextColumn::make("has_debt")
                    ->label("Status")
                    ->getStateUsing(fn(Customer $record) => $record->totalDebt() === 0 ? 'Lunas' : 'Ada Utang')
                    ->badge()
                    ->icon(fn($state) => $state === 'Lunas' ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-circle')
                    ->color(fn($state) => $state === 'Lunas' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_debt')
                    ->label('Punya Kasbon')
                    ->query(fn(Builder $query) => $query->whereHas('transactions', fn($q) => $q->where('status', 'unpaid'))),
            ])
            ->actions([Tables\Actions\EditAction::make()])
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
            "index" => Pages\ListCustomers::route("/"),
            "create" => Pages\CreateCustomer::route("/create"),
            "edit" => Pages\EditCustomer::route("/{record}/edit"),
        ];
    }
}
