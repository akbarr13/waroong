<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label("Tambah Kategori")
                ->icon('heroicon-o-plus')
                ->modalHeading("Buat Kategori Baru")
                ->modalWidth("sm")
                ->extraAttributes(['class' => 'fi-create-transaction-btn']),
        ];
    }
}
