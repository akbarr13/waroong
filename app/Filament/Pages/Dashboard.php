<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_transaction')
                ->label('Transaksi Baru')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(route('filament.admin.resources.transactions.create'))
                ->extraAttributes(['class' => 'fi-create-transaction-btn']),
        ];
    }
}
