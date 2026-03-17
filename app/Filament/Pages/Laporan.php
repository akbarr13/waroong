<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Laporan extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $navigationGroup = 'Toko';
    protected static ?int    $navigationSort  = 0;
    protected static string  $view            = 'filament.pages.laporan';

    public ?string $date_from = null;
    public ?string $date_to   = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    DatePicker::make('date_from')
                        ->label('Dari Tanggal')
                        ->displayFormat('d/m/Y')
                        ->maxDate(today()),

                    DatePicker::make('date_to')
                        ->label('Sampai Tanggal')
                        ->displayFormat('d/m/Y')
                        ->maxDate(today()),
                ])->columns(2),
            ])
            ->statePath('');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with('customer')
                    ->where('user_id', auth()->id())
                    ->when($this->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
                    ->when($this->date_to,   fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
                    ->orderByDesc('created_at')
            )
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->description(fn(Transaction $r) => $r->created_at->format('d/m/Y H:i')),

                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->default('Umum')
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) { 'cash' => 'Tunai', 'qris' => 'QRIS', 'debt' => 'Kasbon', default => $state })
                    ->color(fn($state) => match($state) { 'cash' => 'success', 'qris' => 'info', 'debt' => 'warning', default => 'gray' })
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state === 'paid' ? 'Lunas' : 'Belum Lunas')
                    ->color(fn($state) => $state === 'paid' ? 'success' : 'warning'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(fn() => route('laporan.csv', array_filter([
                    'from' => $this->date_from,
                    'to'   => $this->date_to,
                ])))
                ->openUrlInNewTab(),

            Action::make('cetak')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn() => route('laporan.print', array_filter([
                    'from' => $this->date_from,
                    'to'   => $this->date_to,
                ])))
                ->openUrlInNewTab(),
        ];
    }
}
