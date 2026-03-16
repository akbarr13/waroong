<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Form;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }
}
