<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return redirect('/admin/login')
                ->with('oauth_error', 'Sesi login expired. Silakan coba lagi.');
        } catch (\Exception $e) {
            return redirect('/admin/login')
                ->with('oauth_error', 'Login Google gagal. Pastikan koneksi internet stabil dan coba lagi.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update google_id if logging in by email for the first time via Google
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'name'     => $googleUser->getName(),
                'email'    => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => null,
            ]);
        }

        Auth::login($user, remember: true);

        return redirect('/admin');
    }
}
