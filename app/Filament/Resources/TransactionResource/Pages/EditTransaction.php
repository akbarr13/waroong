<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak_struk')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn() => route('struk', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('lunasi')
                ->label('Tandai Lunas')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->status === 'unpaid')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'paid']);
                    Notification::make()
                        ->title('Kasbon dilunasi')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
