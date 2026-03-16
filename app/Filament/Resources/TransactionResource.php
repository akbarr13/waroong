<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Hidden;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = "heroicon-o-shopping-cart";

    protected static ?string $navigationLabel = "Kasir (Transaksi)";

    protected static ?string $navigationGroup = "Toko";

    protected static ?int $navigationSort = -1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make("Daftar Barang Belanjaan")->schema([
                TextInput::make('barcode_scan')
                    ->label('Scan Barcode')
                    ->placeholder('Arahkan scanner ke sini atau ketik SKU lalu Enter...')
                    ->prefix('🔍')
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('buka_kamera')
                            ->icon('heroicon-o-camera')
                            ->label('Kamera')
                            ->extraAttributes(['onclick' => 'openCameraScanner(); return false;'])
                    )
                    ->dehydrated(false)
                    ->hiddenOn('edit')
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if (blank($state) || strlen($state) < 2) return;

                        $product = Product::where('sku', $state)->first();

                        if (!$product) {
                            Notification::make()
                                ->title('Produk tidak ditemukan')
                                ->body("SKU \"{$state}\" tidak ada di database.")
                                ->warning()
                                ->send();
                            $set('barcode_scan', null);
                            return;
                        }

                        $items = $get('items') ?? [];

                        // Kalau produk sudah ada di keranjang, increment qty
                        foreach ($items as $key => $item) {
                            if (($item['product_id'] ?? null) == $product->id) {
                                $newQty = ($item['quantity'] ?? 1) + 1;
                                $items[$key]['quantity'] = $newQty;
                                $items[$key]['subtotal'] = $product->selling_price * $newQty;
                                $set('items', $items);
                                $set('total_amount', collect($items)->sum('subtotal'));
                                $set('barcode_scan', null);
                                Notification::make()
                                    ->title("+1 {$product->name}")
                                    ->body("Qty sekarang: {$newQty}")
                                    ->success()->send();
                                return;
                            }
                        }

                        // Tambah item baru ke keranjang
                        $items[(string) Str::uuid()] = [
                            'product_id' => $product->id,
                            'quantity'   => 1,
                            'price'      => $product->selling_price,
                            'subtotal'   => $product->selling_price,
                        ];
                        $set('items', $items);
                        $set('total_amount', collect($items)->sum('subtotal'));
                        $set('barcode_scan', null);
                        Notification::make()
                            ->title("+ {$product->name}")
                            ->body('Ditambahkan ke keranjang')
                            ->success()->send();
                    }),

                Repeater::make("items")
                    ->relationship()
                    ->schema([
                        Select::make("product_id")
                            ->relationship("product", "name")
                            ->label("Pilih Barang")
                            ->searchable()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->reactive()
                            ->hint(fn(Get $get) => ($p = Product::find($get('product_id'))) ? 'Stok: '.$p->stock.' tersisa' : null)
                            ->hintColor(fn(Get $get) => ($p = Product::find($get('product_id'))) && $p->stock <= 5 ? 'danger' : 'success')
                            ->afterStateUpdated(function (
                                $state,
                                Set $set,
                                Get $get,
                            ) {
                                $product = Product::find($state);
                                if ($product) {
                                    $qty = $get("quantity") ?? 1;
                                    $subtotal = $product->selling_price * $qty;
                                    $set("price", $product->selling_price);
                                    $set("subtotal", $subtotal);

                                    $items = $get("../../items") ?? [];
                                    $set("../../total_amount", collect($items)->sum("subtotal"));
                                }
                            })
                            ->columnSpan(['default' => 'full', 'md' => 5]),

                        TextInput::make("quantity")
                            ->label("Jumlah")
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $price = $get('price') ?? 0;
                                $subtotal = $price * ($state ?? 0);
                                $set('subtotal', $subtotal);
                                $items = $get('../../items') ?? [];
                                $set('../../total_amount', collect($items)->sum('subtotal'));
                            })
                            ->columnSpan(['default' => 2, 'md' => 2]),

                        TextInput::make("price")
                            ->label("Harga Satuan")
                            ->numeric()
                            ->readOnly()
                            ->required()
                            ->prefix('Rp')
                            ->columnSpan(['default' => 2, 'md' => 2]),

                        TextInput::make("subtotal")
                            ->label("Subtotal")
                            ->numeric()
                            ->readOnly()
                            ->required()
                            ->prefix('Rp')
                            ->columnSpan(['default' => 'full', 'md' => 3]),
                    ])
                    ->columns(['default' => 4, 'md' => 12])
                    ->addActionLabel("Tambah Barang Baru")
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $total = collect($get("items"))->sum("subtotal");
                        $set("total_amount", $total);
                    })
                    ->disabled(fn(string $operation) => $operation === 'edit')
                    ->deletable(fn(string $operation) => $operation !== 'edit')
                    ->reorderable(fn(string $operation) => $operation !== 'edit'),
            ]),

            Section::make("Total & Pembayaran")
                ->extraAttributes([
                    'wire:loading.class' => 'opacity-50 pointer-events-none',
                    'wire:target' => 'data.payment_method',
                ])
                ->schema([
                    TextInput::make("total_amount")
                        ->label("Total Belanja")
                        ->numeric()
                        ->readOnly()
                        ->required()
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            "class" => "text-3xl font-bold text-primary-600",
                        ]),

                    ToggleButtons::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options(['cash' => 'Tunai', 'qris' => 'QRIS', 'debt' => 'Kasbon'])
                        ->colors(['cash' => 'success', 'qris' => 'info', 'debt' => 'warning'])
                        ->icons(['cash' => 'heroicon-o-banknotes', 'qris' => 'heroicon-o-qr-code', 'debt' => 'heroicon-o-clock'])
                        ->default('cash')
                        ->required()
                        ->inline()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('status', $state === 'debt' ? 'unpaid' : 'paid');
                            if ($state !== 'debt') $set('customer_id', null);
                            if ($state !== 'qris') $set('payment_proof', null);
                        }),

                    FileUpload::make('payment_proof')
                        ->label('Bukti Pembayaran QRIS')
                        ->image()
                        ->directory('payment-proofs')
                        ->maxSize(2048)
                        ->visible(fn(Get $get) => $get('payment_method') === 'qris'),

                    Select::make("customer_id")
                        ->relationship("customer", "name")
                        ->label("Pelanggan")
                        ->searchable()
                        ->visible(fn(Get $get) => $get('payment_method') === 'debt')
                        ->createOptionForm([
                            TextInput::make("name")->required(),
                            TextInput::make("phone"),
                        ]),

                    Select::make("status")
                        ->label("Status Pembayaran")
                        ->options([
                            "paid" => "Lunas",
                            "unpaid" => "Belum Lunas",
                        ])
                        ->default("paid")
                        ->required()
                        ->visibleOn('edit'),
                ])
                ->columns(1),

            Section::make("Informasi Transaksi")
                ->schema([
                    TextInput::make("invoice_number")
                        ->label("Nomor Invoice")
                        ->default("INV-" . date("Ymd") . "-" . rand(100, 999))
                        ->readOnly()
                        ->required(),

                    Hidden::make("user_id")->default(auth()->id()),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("invoice_number")
                    ->label("No. Invoice")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make("customer.name")
                    ->label("Pelanggan")
                    ->default("Umum")
                    ->searchable()
                    ->sortable()
                    ->color(fn(Transaction $record) => $record->customer_id ? 'primary' : 'gray')
                    ->weight(fn(Transaction $record) => $record->customer_id ? 'medium' : 'normal')
                    ->icon(fn(Transaction $record) => $record->customer_id ? 'heroicon-m-user' : null)
                    ->url(fn(Transaction $record) => $record->customer_id
                        ? route('filament.admin.resources.customers.edit', $record->customer_id)
                        : null
                    )
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
                Tables\Columns\TextColumn::make("total_amount")
                    ->label("Total Belanja")
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make("payment_method")
                    ->label("Metode")
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            "cash" => "success",
                            "qris" => "info",
                            "debt" => "warning",
                            default => "gray",
                        },
                    )
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            "cash" => "Tunai",
                            "qris" => "QRIS",
                            "debt" => "Kasbon",
                            default => $state,
                        },
                    )
                    ->extraHeaderAttributes(['class' => 'hidden sm:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden sm:table-cell']),
                Tables\Columns\TextColumn::make("status")
                    ->label("Status")
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            "paid" => "success",
                            "unpaid" => "warning",
                        },
                    )
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            "paid" => "Lunas",
                            "unpaid" => "Belum Lunas",
                        },
                    ),
                Tables\Columns\TextColumn::make("created_at")
                    ->label("Tanggal")
                    ->dateTime("d/m/Y H:i")
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'hidden md:table-cell'])
                    ->extraCellAttributes(['class' => 'hidden md:table-cell']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode')
                    ->options(['cash' => 'Tunai', 'qris' => 'QRIS', 'debt' => 'Kasbon']),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(['paid' => 'Lunas', 'unpaid' => 'Belum Lunas']),
                Tables\Filters\Filter::make('kasbon_aktif')
                    ->label('Kasbon Belum Lunas')
                    ->query(fn(Builder $query) => $query->where('payment_method', 'debt')->where('status', 'unpaid'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('cetak_struk')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn(Transaction $record) => route('struk', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('lunasi')
                    ->label('Lunasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Transaction $record) => $record->status === 'unpaid')
                    ->requiresConfirmation()
                    ->modalHeading('Lunasi Kasbon')
                    ->modalDescription(fn(Transaction $record) => "Tandai transaksi {$record->invoice_number} sebagai lunas?")
                    ->action(function (Transaction $record) {
                        $record->update(['status' => 'paid']);
                        Notification::make()
                            ->title('Kasbon dilunasi')
                            ->body("Invoice {$record->invoice_number} sudah ditandai lunas.")
                            ->success()
                            ->send();
                    }),
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
            "index" => Pages\ListTransactions::route("/"),
            "create" => Pages\CreateTransaction::route("/create"),
            "edit" => Pages\EditTransaction::route("/{record}/edit"),
        ];
    }
}
