<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                TextInput::make('store_name')
                    ->label('Nama Toko')
                    ->placeholder('Contoh: Warung Bu Sari'),

                TextInput::make('store_address')
                    ->label('Alamat Toko')
                    ->placeholder('Contoh: Jl. Mawar No. 5, Jakarta'),

                TextInput::make('store_phone')
                    ->label('No. HP Toko')
                    ->tel()
                    ->placeholder('Contoh: 08123456789')
                    ->helperText('Nama, alamat, dan HP akan tampil di struk belanja.'),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
