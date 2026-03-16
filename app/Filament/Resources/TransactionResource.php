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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Hidden;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;

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
            Section::make("Informasi Transaksi")
                ->schema([
                    TextInput::make("invoice_number")
                        ->label("Nomor Invoice")
                        ->default("INV-" . date("Ymd") . "-" . rand(100, 999))
                        ->readOnly()
                        ->required(),

                    Hidden::make("user_id")->default(auth()->id()), // Otomatis simpan ID kasir yang login

                    Select::make("customer_id")
                        ->relationship("customer", "name")
                        ->label("Pelanggan (Pilih jika Ngutang)")
                        ->searchable()
                        ->createOptionForm([
                            // Fitur keren: bisa tambah pelanggan baru langsung dari kasir!
                            TextInput::make("name")->required(),
                            TextInput::make("phone"),
                        ]),

                    Select::make("payment_method")
                        ->label("Metode Pembayaran")
                        ->options([
                            "cash" => "Tunai (Cash)",
                            "debt" => "Kasbon (Utang)",
                        ])
                        ->default("cash")
                        ->required(),

                    Select::make("status")
                        ->label("Status Pembayaran")
                        ->options([
                            "paid" => "Lunas",
                            "unpaid" => "Belum Lunas",
                        ])
                        ->default("paid")
                        ->required(),
                ])
                ->columns(2),

            Section::make("Daftar Barang Belanjaan")->schema([
                Repeater::make("items")
                    ->relationship()
                    ->schema([
                        Select::make("product_id")
                            ->relationship("product", "name")
                            ->label("Pilih Barang")
                            ->searchable()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->reactive() // Supaya otomatis ngambil harga saat barang dipilih
                            ->afterStateUpdated(function (
                                $state,
                                Set $set,
                                Get $get,
                            ) {
                                $product = Product::find($state);
                                if ($product) {
                                    $set("price", $product->selling_price); // Set harga otomatis
                                    $qty = $get("quantity") ?? 1;
                                    $set(
                                        "subtotal",
                                        $product->selling_price * $qty,
                                    ); // Hitung subtotal
                                }
                            }),

                        TextInput::make("quantity")
                            ->label("Jumlah")
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->reactive() // Supaya otomatis hitung subtotal saat jumlah diubah
                            ->afterStateUpdated(function (
                                $state,
                                Set $set,
                                Get $get,
                            ) {
                                $price = $get("price") ?? 0;
                                $set("subtotal", $price * $state);
                            }),

                        TextInput::make("price")
                            ->label("Harga Satuan")
                            ->numeric()
                            ->readOnly() // Tidak boleh diubah manual
                            ->required(),

                        TextInput::make("subtotal")
                            ->label("Subtotal")
                            ->numeric()
                            ->readOnly()
                            ->required(),
                    ])
                    ->columns(4)
                    ->addActionLabel("Tambah Barang Baru")
                    // Fitur untuk menghitung Total Belanja otomatis!
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $total = collect($get("items"))->sum("subtotal");
                        $set("total_amount", $total);
                    }),
            ]),

            Section::make("Total Pembayaran")->schema([
                TextInput::make("total_amount")
                    ->label("Total Belanja (Rp)")
                    ->numeric()
                    ->readOnly()
                    ->required()
                    ->extraInputAttributes([
                        "class" => "text-3xl font-bold text-primary-600",
                    ]),
            ]),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make("total_amount")
                    ->label("Total Belanja")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make("payment_method")
                    ->label("Metode")
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            "cash" => "success",
                            "debt" => "danger",
                        },
                    )
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            "cash" => "Tunai",
                            "debt" => "Kasbon",
                        },
                    ),
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
                    ->sortable(),
            ])
            ->filters([
                //
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
            "index" => Pages\ListTransactions::route("/"),
            "create" => Pages\CreateTransaction::route("/create"),
            "edit" => Pages\EditTransaction::route("/{record}/edit"),
        ];
    }
}
